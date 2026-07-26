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
| 4 | Bill, Estimate, Quotation, GST report, Salary | ✅ Done |
| 5 | PDF/Excel/email (dompdf, PhpSpreadsheet, Mail) | 🚧 In progress (PDF export done) |
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
- **Bill module (`App\Http\Controllers\BillController`)** rebuilds
  `add_edit_bill.php`/`bill.php`/`bill_print.php` against the normalized
  `bill_items` table instead of the delimited `product` column:
  - Line items are edited with an Alpine.js component
    (`lineItemForm` in `resources/js/app.js`) that searches
    `GET /products/search` (any authenticated user, not gated by the
    `Product` module's own permissions — matches legacy) instead of the
    old jQuery-UI-autocomplete + per-keystroke AJAX round trips.
  - **Line/bill totals are recomputed server-side** (`price × qty` per
    line, summed for `bill.total`) — the legacy form trusted whatever the
    client posted for both; this is a deliberate hardening, not a
    behavior-preserving port.
  - **Invoice number is a suggested default only** (`MAX(invoice_no) +
    1`), left fully editable and *not* enforced unique on save — matches
    legacy behavior; historical `invoice_no` values already contain
    gaps/duplicates from the old "last row's number + 1" logic, so a
    uniqueness constraint needs a data audit first, not app-level
    enforcement now.
  - Bill list defaults to **India's April–March fiscal year**
    (`BillController::currentFiscalYearStart()`), matching the legacy
    `bill.php` default filter, and searches invoice/subject/sir
    name/ref no. A different pattern than other index pages, since bill
    volume makes an unfiltered list impractical.
  - `bills.print` is a browser-print HTML view (`window.print()` on
    load), not a generated PDF — same as the legacy `bill_print.php`.
    GST math (9% CGST + 9% SGST when `gst_bill=1`, grand total rounded
    once after adding both) and the amount-in-words conversion
    (`App\Support\IndianCurrency`) reproduce the legacy print template
    exactly, including its "round after summing" order of operations.
  - Company-wide constants that were hardcoded into the legacy print
    template (GSTIN, bank details, MSME registration) now live in
    `config/company.php`.
  - The GST register/TDS PDF report (legacy `gst_bill.php`, an aggregate
    date-range export, not per-invoice) and the "seed a bill from a
    measurement sheet" fallback are **not ported yet** — tracked as
    follow-up work under Phase 4/5.
- **Estimate module (`App\Http\Controllers\EstimateController`)** mirrors
  the Bill module (same `lineItemForm` Alpine component, same
  server-side total recomputation) but is deliberately simpler because
  the legacy `estimate` table/forms are: no department, GST toggle,
  payment fields, ref no, or invoice numbering exist for estimates —
  `subject`, `bill_date`, and line items are the whole editable surface.
  Two columns on the `estimate` table (`ast_desc`, `address`) are dead —
  grepped the entire legacy `admin/` tree and neither is read or written
  anywhere — so they're intentionally left off the form. Unlike Bill,
  **estimate print always applies 9%+9% GST with no toggle**, matching
  `estimate_print.php` exactly (it has no `gst_bill`-equivalent check at
  all). Estimate's "email as PDF/Excel" feature (`estimate_mail.php`) is
  still deferred — see the Phase 5 note below on Excel/email.
- **Quotation module (`App\Http\Controllers\QuotationController`)** is the
  simplest of the three billing modules — the legacy `quotation` table has
  no line-items concept at all, just a single free-text `particulars`
  field plus `unit`/`total`, so there's no `quotation_items` table and no
  `lineItemForm` reuse here. The print template signs off as a different
  trade name (`Bhavani Fabricators`) than Bill/Estimate's `Bhavani
  Engineering` — see `config('company.quotation_entity_name')`.
- **GST Report (`App\Http\Controllers\GstReportController`)** rebuilds
  `gst_bill.php`/`gst_bill_pdf()` — an aggregate GST/TDS register for a
  date range across all bills, not a per-invoice document. Reproduces the
  legacy math exactly, including that it does **not** round per-row
  values (unlike `BillController::print`, which does) — only the totals
  row is a plain sum of unrounded figures, and TDS is a flat 1% of each
  bill's pre-tax total. Not backed by an Eloquent model/policy — it's a
  single legacy permission (`"GST Report"`), so authorization is a
  closure-based `Gate::define('view-gst-report', ...)` in
  `AppServiceProvider` instead of a `{Model}Policy` class.
- **Salary Slip (`App\Http\Controllers\SalarySlipController`)** rebuilds
  `add_edit_salary_slip.php`/`salary_slip.php`/`salary_slip_print.php`/
  `ajax_get_salary_data.php`. A slip is a monthly payslip computed from
  that month's `attendance` rows and the latest applicable `salary_details`
  rate (picked by "most recent row on/before month-end", not a real
  date-range table — matches legacy). The `GET /salary-slips/data`
  endpoint replaces the legacy pipe-delimited AJAX response with JSON,
  used by the create/edit form to prefill Days Worked/Overtime — only for
  *new* slips, matching legacy (an existing slip's entered values are
  never silently overwritten). Earnings math
  (`SalarySlipController::calculateEarnings()`) reproduces
  `salary_slip_print.php` exactly: `basic_pay = round(day_work ×
  par_day_amount) + round(over_time × par_day_amount/8)`, same shape for
  the extra-allowance/per_day_extra pair.
  - **Advance-payment ledger**: `employee_details` is a running Debit/
    Credit ledger per employee (Debit = advanced to them, Credit = repaid/
    deducted — unrelated to `Bill`'s use of the same table via
    `bill_id`/`type`; here `bill_id` holds a `salary_slip.id`, a legacy
    naming collision, not an actual bill reference). A slip's "Advance
    Payment Deduction" field is mirrored into this ledger as a Credit row.
    **Deliberate fix over legacy**: the legacy UPDATE-only sync silently
    no-ops if a slip's advance was originally zero (no row to update) and
    is later edited to a nonzero value; this uses `updateOrCreate`/delete
    so editing always keeps the ledger correct.
  - **Pay-rate history** (`App\Http\Controllers\SalaryDetailController`,
    nested under `employees/{employee}/salary-details`) covers the
    essential list/add/delete subset of `view_salary.php`/
    `add_edit_salary.php` — editing an existing rate isn't ported (legacy
    rarely uses it differently from adding a new dated row).
  - **Not included** (follow-up work): the aggregate "Salary Sheet"
    payroll register (`salary_bill.php`, the `salary_bill.php`/"Salary
    Sheet" sidebar item is still a placeholder) and a UI for recording
    ad-hoc advance/debit ledger entries outside of a slip's own deduction
    field.
- **PDF export (`barryvdh/laravel-dompdf`)**: every module with a
  browser-print view (Bill, Estimate, Quotation, GST Report, Salary Slip)
  now also has a real downloadable PDF at a parallel `*.pdf` route (e.g.
  `bills.pdf` alongside `bills.print`), both gated by the same `print`
  ability/Gate as the browser view. dompdf doesn't execute the app's
  compiled Tailwind (no Vite pipeline in a non-HTTP render), so each
  module has a dedicated `*.pdf.blade.php` view using plain CSS classes
  from the shared `resources/views/pdf/_styles.blade.php` partial,
  instead of reusing the Tailwind-based `*.print.blade.php` view directly
  — same layout/data, different markup. **Not yet wired**: Excel export
  (`create_estimate_excel.php` and friends) and the "email as PDF/Excel"
  feature (`estimate_mail.php`) — both still Phase 5 follow-up work.
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
