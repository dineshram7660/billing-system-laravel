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
6. Run `php artisan legacy:import-bill-photos --source=...` (see the
   "File/upload migration" section below) and `php artisan storage:link`.
7. Check the `legacy_import_issues` table — empty, or only rows already
   known/accepted from earlier dry runs. Don't cut traffic over with new
   unexplained rows there.
8. Run the post-cutover smoke test below.
9. Point traffic (DNS / load balancer / reverse proxy) at the Laravel
   app.

## File/upload migration — done (2026-08-02)

Legacy bill photo uploads live in the legacy app's `admin/image/`
directory (`UPLOAD_URL` in `admin/includes/config.php` — not the
repo-root `images/` directory, which turned out to be static site
assets, not uploads). `App\Console\Commands\ImportLegacyBillPhotos`
copies each file `bill.photo` references into `storage/app/public/
bill-photos/` and rewrites `bill.photo` to the disk-relative path
`App\Http\Controllers\BillPhotoController` expects. Run as part of the
data cutover sequence above, after the legacy DB dump is restored:

```
php artisan legacy:import-bill-photos --source=/path/to/legacy/admin/image
```

(or set `LEGACY_BILL_PHOTOS_PATH` in `.env` and drop `--source`). Safe to
re-run — already-migrated bills and already-copied files are left alone.
Any referenced file missing from the source directory is logged to
`legacy_import_issues` (`source_table = 'bill_photo'`) rather than
silently dropped without a trace, and don't forget
`php artisan storage:link` so the copied files are actually served.

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

## CI/test-suite gap — partially closed (2026-08-02)

`.github/workflows/ci.yml`'s `lint-and-build` job only runs style checks
(Pint) and the asset build, not the feature test suite. That's because
`phpunit.xml` deliberately has no `DB_*` overrides: most of the suite
runs against a real MySQL copy of the legacy tables (imported from a DB
dump; see `phpunit.xml`'s comment), which have no Laravel migrations at
all — a GitHub Actions MySQL service container starts empty, so it can't
run those tests as written.

**First slice — Employee module, done**:
`2026_07_26_050000_create_legacy_tables_if_missing.php` adds guarded
(`Schema::hasTable()`-gated) schema migrations for the 8 legacy tables
the Employee module needs — `admin` (every test needs this one, for
auth), `department`, `designation`, `employee`, `employee_details`,
`attendance`, `salary_details`, `salary_slip`. Six other migrations that
unconditionally touched legacy tables outside this slice (raw `ALTER
TABLE bill`, or an inline `$table->foreign(...)->on('bill'/'estimate')`
constraint) got the same `Schema::hasTable()` guard, since `php artisan
migrate` runs every migration in the folder — not just the ones for
tables a given slice cares about — and would otherwise fail outright on
a bare database. `test-employee-module` runs a genuinely bare `mysql:8`
service container through `php artisan migrate` and the five
Employee-module feature test files, and passes.

**Second slice — Bill/Estimate module, done**:
`2026_07_26_050001_create_bill_estimate_tables_if_missing.php` covers
`bill`, `estimate`, `quotation`, `product`, `bill_estimate`,
`measurement_bill`, `measurement_estimate` (the last two are the raw
legacy blob tables `MeasurementBill`/`MeasurementEstimate` map to —
distinct from the already-migrated `measurement_bill_items`/
`measurement_estimate_items`), plus `email_send` — not originally scoped
to this module, but running the tests against a bare database (not just
checking test-file imports) surfaced that `EstimateMailControllerTest`
exercises a write into it as a side effect of sending an estimate. The
`bill`/`estimate` FK constraints in `create_bill_items_table` and
friends (guarded in the first slice) now actually attach on a fresh
database, same as production. One real fix needed along the way: the
`id` columns had to be plain signed `int` (`$table->integer('id')->autoIncrement(); $table->primary('id');`),
not Laravel's default `bigint unsigned` from `$table->id()` — MySQL
rejects a foreign key whose referencing and referenced column types
don't match exactly, and the legacy schema's ids are signed `int`
throughout. `test-bill-estimate-module` runs the same bare-database
pattern against 11 feature test files and passes.

Also corrected an earlier miscount here: this section used to say 13
legacy tables remained after the Employee slice — actually 15, because
`measurement_bill`/`measurement_estimate` were missed. The count above
already reflects the fix.

**Remaining scope for future slices** — 8 legacy tables still have no
schema migration: `account`, `account_details`, `expenses`, `income`,
`inquery`, `report`, `sub_access`, and (per the `DashboardControllerTest`
gap noted below) nothing further blocks it once `expenses`/`income` land.
Each future slice is the same shape as the two done so far: add a
guarded `Schema::create()` for the tables a module's tests need (in
their *original* legacy column shape, so existing ALTER-based migrations
like `make_legacy_not_null_columns_nullable` keep working unmodified on
top), add a scoped CI job, run the tests against a real throwaway
database first — not just a test-file-import grep — to catch hidden
dependencies like `email_send` above, and expand the
`Schema::hasTable()` guards already in place if a new slice's tables get
referenced by an existing migration. Once every table has a guarded
migration, `lint-and-build` and every scoped job can collapse back into
one job running the whole suite, and `php artisan test` against
`.env.testing` can stop being purely a local/manual pre-PR check.

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
