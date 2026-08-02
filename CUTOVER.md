# Production cutover checklist

Feature parity with the legacy app is done (see `README.md`'s Status
table and Phase 7 audit). This document tracks what's left before this
app can actually replace the legacy one in production — infra decisions,
data migration, and rollback — none of which is "done" yet. Hosting isn't
decided yet, so items below stay generic rather than assuming a specific
provider; revisit the hosting-dependent items once that's picked.

## Production environment config

Starting from `.env.example`, a real deploy needs at least:

- `APP_ENV=production`, `APP_DEBUG=false` — the example file is
  intentionally dev-friendly (`local`/`true`), not a production template.
- A real `APP_URL` (not `localhost`).
- A real `MAIL_MAILER` — example/testing only configure `log`/`array`,
  neither sends actual mail (needed for `EstimateMail`, etc.).
- `LOG_LEVEL` tightened from `debug` to something like `error` or
  `warning`.
- `SESSION_DRIVER=database` / `CACHE_STORE=database` (already the
  example defaults) are fine for a single app server. Only move to
  Redis/Memcached if the eventual hosting setup runs multiple app
  servers behind a load balancer.

## Legacy column defaults — done (2026-08-02)

`config/database.php`'s `mysql` connection used to disable strict mode
because several legacy columns — e.g. `employee.username`/`password` —
were `NOT NULL` with no default, and either the app's own forms treated
them as optional or never populated them at all. Resolved: three parallel
research passes cross-referenced every NOT-NULL-no-default legacy column
against the app's actual Eloquent write paths, found 26 real gaps across
9 tables (`bill`, `estimate`, `quotation`, `product`, `employee`,
`account`, `employee_details`, `expenses`, `income`), and
`2026_08_02_000000_make_legacy_not_null_columns_nullable` made all of
them real `NULL`able columns. `config/database.php` now runs with
`strict => true`, Laravel's normal default. Tables with no active insert
path from Laravel at all (`bill_estimate`, `sub_access`, `inquery`,
`report`) were left untouched — no insert path means no strict-mode risk.

Not done in this pass — doing it blind risks breaking existing inserts.

## Data cutover sequence

This is a one-way cutover, not a live sync — plan for a maintenance
window:

1. Freeze writes to the legacy app (maintenance page / read-only mode).
2. Take a final `mysqldump` of the legacy database.
3. Restore that dump into the production Laravel app's database.
4. Run `php artisan migrate` (creates the tables Laravel owns — line
   items, measurement items, `legacy_import_issues`, etc. — the 21
   legacy tables come from the dump itself, they have no migrations).
5. Run `php artisan legacy:import-line-items --fresh` and
   `php artisan legacy:import-measurements --fresh` (see
   `App\Console\Commands\ImportLegacyLineItems` /
   `ImportLegacyMeasurements`) against the freshly-restored data.
6. Check the `legacy_import_issues` table — empty, or only rows already
   known/accepted from earlier dry runs. Don't cut traffic over with new
   unexplained rows there.
7. Run the post-cutover smoke test below.
8. Point traffic (DNS / load balancer / reverse proxy) at the Laravel
   app.

## File/upload migration

Legacy bill photo uploads live in the legacy app's `images/` directory.
The Laravel app's `App\Http\Controllers\BillPhotoController` writes to
the `public` disk (`storage/app/public`, served via
`php artisan storage:link`). There's currently no step that copies
existing legacy-uploaded photos into that disk and reconciles their DB
references — without it, old bill photos will 404 after cutover. Needs a
one-off copy script as part of the cutover, written once the actual file
layout/paths in `images/` are inventoried.

## Backups

TBD pending the hosting decision — DB backups (`mysqldump` cron vs. a
managed database's built-in snapshots) and `storage/app/public` backups
both need a mechanism, but which one depends on where this ends up
running.

## Rollback plan

Keep the legacy app and its database reachable (read-only) for a defined
window after cutover. The import commands are `--fresh` (destructive to
the *normalized* Laravel-side tables, replacing them on re-run) but never
touch the legacy source tables or the legacy app itself, so rolling back
means simply pointing traffic back at the legacy app — as long as no new
data was written to the Laravel app in the meantime that would need to be
manually replayed back into the legacy DB.

## Monitoring

Not wired up yet. `LOG_CHANNEL=stack` currently only writes to local log
files — there's no error-tracking/alerting service (Sentry or similar)
configured. Should be in place before go-live so failures in production
are visible, but the specific service and setup are deferred until
needed.

## CI/test-suite gap

`.github/workflows/ci.yml` only runs style checks (Pint) and the asset
build — not the feature test suite. That's because `phpunit.xml`
deliberately has no `DB_*` overrides: the test suite runs against a real
MySQL copy of the 21 legacy tables (imported from a DB dump; see
`phpunit.xml`'s comment), which have no Laravel migrations at all, only
the 13 new-feature tables do. A GitHub Actions MySQL service container
starts empty, so it can't run these tests as they're written today.

Closing this gap is its own future initiative, not attempted here — two
real options:

- Write schema-only migrations for the 21 legacy tables plus synthetic
  fixture data, decoupling tests from real business data entirely (large
  but the "correct" long-term fix).
- Restore a sanitized/anonymized dump in CI from a securely-stored
  source — a data-handling decision that needs an explicit call on what's
  safe to put in CI, not something to default into.

Until one of those happens, `php artisan test` against `.env.testing`
stays a local/manual pre-PR check, same as it's been for every feature
built so far.

## Post-cutover smoke test

Run manually right after cutover, before considering it done:

- [ ] Log in as an existing admin account.
- [ ] View the Bill list, open a Bill, print it, download its PDF.
- [ ] Add an Account transaction (Credit or Debit) and confirm it shows
      up on that day's Rojmed entry with the right running balance.
- [ ] Add an Employee ledger (advance/debit) entry from an Employee's
      page.
- [ ] Run the Salary Sheet for the current month and confirm it renders.
- [ ] Open a Bill's measurement sheet, confirm existing data still shows,
      and confirm "Copy Measurement from Estimate" still works.
- [ ] Confirm uploaded Bill photos (migrated per the section above) load
      without 404s.
