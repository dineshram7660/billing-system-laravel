# Bhavani Engineering — Laravel rebuild

Laravel rebuild of the Bhavani Engineering admin/billing system, replacing the
legacy procedural PHP + mysqli codebase. Built incrementally alongside the
legacy app rather than as a big-bang rewrite — see the migration roadmap for
the full phase plan and reasoning.

## Status

| Phase | What | Status |
|---|---|---|
| 0 | Project foundations, DB connection, Eloquent models for existing tables | ✅ Done |
| 1 | Auth against the existing `admin` table, dashboard shell + sidebar | ✅ Done |
| 2 | Master-data CRUD (Department, Designation, Employee, Product, Sub Admin) | ✅ Done |
| 3 | Normalize `bill`/`estimate` line items out of delimited text columns | ✅ Done |
| 4 | Bill, Estimate, Quotation, GST report, Salary | ⏳ Not started |
| 5 | PDF/Excel/email (dompdf, PhpSpreadsheet, Mail) | ⏳ Not started |
| 6 | Remaining modules + API layer | ⏳ Not started |

## Notable decisions

- **Auth runs against the existing `admin` table**, not a new `users` table —
  see `App\Models\User`. Existing accounts and permissions (the `access`
  column, mirrored via `User::hasLegacyPermission()`) work unchanged.
- **Passwords upgrade transparently.** The legacy app hashed with unsalted
  MD5. `App\Http\Requests\Auth\LoginRequest` verifies against the old hash
  once on login, then rehashes to bcrypt — see the comments there before
  touching that flow.
- **`App\Casts\LegacyDate`** exists because the legacy schema uses the literal
  string `'0000-00-00'` for "no date yet" instead of `NULL`. Carbon doesn't
  reject that string, it silently mangles it into a garbage date — use this
  cast (or add a migration making the column nullable, like the one for
  `bill.ref_date`/`paid_date`) for any date column you touch that might carry
  legacy zero-dates.
- **Sidebar links marked "Soon"** in `resources/views/layouts/admin.blade.php`
  are modules not yet ported — they're intentionally inert placeholders, not
  bugs.
- Running against **a copy of the production database** (`bhavani_laravel`),
  not the same connection the legacy app writes to — see `.env.example`.
- **`config/database.php`'s mysql connection disables strict mode** (`strict`
  => false + `MYSQL_ATTR_INIT_COMMAND` clearing sql_mode). Several legacy
  columns (e.g. `employee.username`/`password`) are NOT NULL with no default
  and the legacy forms never set them — they only ever worked because the
  original app's MySQL config was lenient. Revisit once Phase 3 gives these
  columns real defaults/nullability instead of relying on this.
- **Line items and measurements are normalized out of delimited text
  columns.** `bill.product`/`estimate.product` (an `"[#]"`/`"[@]"`-delimited
  blob) is backfilled into `bill_items`/`estimate_items` by
  `App\Console\Commands\ImportLegacyLineItems`;
  `measurement_bill.product`/`measurement_estimate.product` (an
  additionally `"[(@)]"`-nested blob — one `"[@]"` slot per product, further
  split into per-line values) is backfilled into
  `measurement_bill_items`/`measurement_estimate_items` +
  `..._item_lines` by `App\Console\Commands\ImportLegacyMeasurements`. Both
  commands are safe to re-run (`--fresh` truncates first, otherwise each
  source row's items are replaced) and never guess at malformed or
  orphaned rows — those get logged to `legacy_import_issues` instead of
  silently corrupting data. The original `product` columns are left
  untouched as an audit trail; new features should read `items()` /
  `measurementItems()` on `Bill`/`Estimate`, not `product` directly.
- **Authorization**: most modules follow `App\Policies\LegacyModulePolicy` —
  the legacy app checks four permission names per module (e.g. `"Department"`,
  `"Add New Department"`, `"Edit Department"`, `"Delete Department"`, see the
  `sub_access` table), and a concrete policy just declares `$module` to get
  all four abilities. Sub Admin doesn't fit that pattern (`"Sub Admin Edit"`
  puts the module name first) so it has its own `SubAdminPolicy`, registered
  manually in `AppServiceProvider` since it doesn't follow Laravel's
  `{Model}Policy` auto-discovery naming either.

## Testing

Feature tests run against `bhavani_laravel_testing`, a real copy of the
legacy schema — see `phpunit.xml` and `.env.testing.example` for why (in
short: the app's models map onto tables that only exist via the legacy DB
dump, not Laravel migrations, so blank sqlite has nothing to test against).

To refresh that database after pulling schema-affecting changes, **drop and
recreate it** rather than re-importing over the top — a plain re-import
resets tables like `admin` back to the legacy dump's shape without touching
Laravel's `migrations` tracking table, which then believes migrations
adding columns to those tables are already applied when they're not:

```bash
mysql -u root -p -e "DROP DATABASE IF EXISTS bhavani_laravel_testing; CREATE DATABASE bhavani_laravel_testing;"
mysqldump -u root -p bhavani | mysql -u root -p bhavani_laravel_testing
php artisan migrate --force --env=testing
php artisan test
```

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# point DB_* at a copy of the database, not production
php artisan migrate
npm run build   # or `npm run dev` while working on views
php artisan serve
```

## Requirements

PHP 8.2+, MySQL, Node/npm, Composer.
