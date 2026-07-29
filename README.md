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
| 5 | PDF/Excel/email (dompdf, PhpSpreadsheet, Mail) | ✅ Done |
| 6 | Remaining modules + API layer | ✅ Done |
| 7 | Post-roadmap audit findings (see below) | ✅ Done |

## Phase 7: post-roadmap audit

After Phase 6, every legacy `admin/*.php` file was checked one by one
against what had actually been built, rather than trusting the sidebar
inventory the roadmap was originally scoped from. That turned up:

- **Confirmed dead, not ported**: `example2.php` (a vendored Excel-reader
  library's demo file), `excel_reader.php` (the vendored library itself),
  `test.php` (a raw DB-credentials script, not a page),
  `managecalls.php` (leftover code from an unrelated "Mitmold" project —
  references a config path and pages that don't exist in this codebase),
  `report_department.php` (leftover from an unrelated "Hotel Namaste"
  hotel-booking project — its own `<title>` says so).
- **`report.php`** (a stock-report PDF generator, logged to a `report`
  table) — intentionally **not ported**: its "department report" branch
  calls an undefined function even in the legacy app, and the `report`
  table shows only 4 rows of historical use ever. Flagged, not built.
- **Real, confirmed gaps, all now built** — Expense, Income, an Employee
  ledger, a dashboard overview widget, Bill photo upload, and the Bill/
  Estimate measurement sheet editors. See the relevant bullets below for
  each.
- **Post-audit follow-up, now built**: `rojmed.php`, a single-day ledger
  day book — see the Rojmed bullet below. (Two more follow-ups flagged
  at the end of Phase 7 — a "Copy Measurement" convenience from Estimate
  to Bill, and a standalone Employee advance/debit entry UI — are
  tracked separately.)

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
  - The GST register/TDS report is a separate module — see
    `GstReportController` below. The "seed a bill from a measurement
    sheet" fallback (and the measurement-sheet print view/PDF it depends
    on) is **not ported** — still open, no current follow-up planned.
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
  all). Estimate's "email as PDF/Excel" feature is built — see
  `App\Mail\EstimateMail` below; the one piece of `estimate_mail.php` not
  carried forward is its optional measurement-sheet PDF attachment,
  since that print view doesn't exist in this app.
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
  - The aggregate "Salary Sheet" payroll register is a separate module —
    see `SalarySheetController` below. **Not included**: a UI for
    recording ad-hoc advance/debit `employee_details` ledger entries
    outside of a slip's own deduction field — still open, no current
    follow-up planned.
- **PDF export (`barryvdh/laravel-dompdf`)**: every module with a
  browser-print view (Bill, Estimate, Quotation, GST Report, Salary Slip)
  now also has a real downloadable PDF at a parallel `*.pdf` route (e.g.
  `bills.pdf` alongside `bills.print`), both gated by the same `print`
  ability/Gate as the browser view. dompdf doesn't execute the app's
  compiled Tailwind (no Vite pipeline in a non-HTTP render), so each
  module has a dedicated `*.pdf.blade.php` view using plain CSS classes
  from the shared `resources/views/pdf/_styles.blade.php` partial,
  instead of reusing the Tailwind-based `*.print.blade.php` view directly
  — same layout/data, different markup.
- **Excel export (`maatwebsite/excel`)**: `App\Exports\EstimateItemsExport`
  rebuilds `create_estimate_excel.php`'s line-item spreadsheet against the
  normalized `estimate_items` table instead of re-parsing the delimited
  `product` blob — `estimates.excel` route, gated by the same `print`
  ability as the PDF/browser-print routes. Legacy only had this for
  Estimate (no Bill/Quotation/GST equivalent), so that's the only export
  here too. One deliberate correctness fix: the row-counter in
  `EstimateItemsExport::map()` is an instance property, not a `static`
  local like a naive port might use — a static local would leak its
  count across every export a queue worker/Octane process ever runs
  (the class stays loaded between requests), silently starting later
  exports' "Sr. No" column at the wrong number. Legacy's 35-character
  truncation of the work-name column (apparently a fixed-column-width
  workaround) isn't reproduced — real spreadsheet columns don't need it.
- **Estimate email (`App\Mail\EstimateMail`,
  `App\Http\Controllers\EstimateMailController`)** rebuilds
  `estimate_mail.php` — emails a client the estimate's PDF and Excel as
  attachments (both generated inline via the same dompdf/Excel wiring as
  the download routes, not read from disk) with the exact legacy canned
  message and subject line (`"Bhavani Engineering Estimate - {subject}"`).
  Gated by a single legacy permission (`"Send Email"`), so it's another
  closure-based `Gate::define('send-email', ...)` rather than a policy —
  same pattern as `view-gst-report`. `App\Http\Controllers\
  EmailSendController` is the read-only sent-email log
  (`email_send.php`) the legacy sidebar's "Send Email" link actually
  points to — composing only happens from a specific estimate, there's
  no standalone compose page. Mail is sent via the `MAIL_MAILER=log`
  driver by default (see `.env.example`) — verified end-to-end against
  real dev data by inspecting `storage/logs/laravel.log` for correct
  headers/subject/body and both attachments' raw bytes, not just that
  the HTTP request succeeded. **Not ported**: legacy's optional
  measurement-sheet PDF attachment — that print view doesn't exist in
  this app yet.
- **Inquiry (`App\Http\Controllers\InquiryController`)** is read-only
  list + delete only — legacy has no add/edit page for the `inquery`
  table (submissions come from a public contact form, not the admin);
  see `App\Policies\InquiryPolicy` for why it doesn't extend
  `LegacyModulePolicy` (no `create`/`update` abilities exist to grant).
  Legacy's table name typo (`inquery`, not `inquiry`) is kept as the
  literal DB table name on the `Inquiry` model — only the model/class/
  route names are spelled correctly.
- **Account (`App\Http\Controllers\AccountController`)** is a named
  ledger account (e.g. "Cash", "Bank", a vendor name) with its own
  Credit/Debit transaction history (`App\Models\AccountDetail`, via
  `App\Http\Controllers\AccountDetailController`) — structurally the
  same pattern as Salary Slip's advance-payment ledger, but standalone
  rather than tied to a payslip. The `account` table shares several
  column names with `employee` (`username`, `password`, `status`,
  `par_day`, `designation_id`), but grepping the whole legacy `admin/`
  tree confirmed `add_edit_account.php` only ever reads/writes
  `account_name` — so those columns are intentionally left off
  `Fillable` on the `Account` model rather than carried over as unused
  cruft. `App\Policies\AccountPolicy` overrides `view()` (rather than
  inheriting `LegacyModulePolicy`'s default, which just delegates to
  `viewAny()`) because legacy splits list access (`"Account"`) from
  viewing one account's ledger (`"View Account"`) as two separate
  permissions.
- **Rojmed (`App\Http\Controllers\RojmedController`, Phase 7 audit
  finding)** rebuilds `rojmed.php` — a single-day "day book" journal
  across every account: a carried-forward opening balance ("Silak" in
  legacy) plus every `account_details` entry dated exactly on the
  selected day, plus a running closing total. Gated by the same
  `"Account"` permission as the account list (`viewAny` on
  `App\Models\Account`); delete reuses the existing
  `accounts.details.destroy` route since a Rojmed entry still belongs to
  a specific account. Legacy's `add_edit_c_d_account.php?id=X` in-place
  edit link isn't reproduced — the existing per-account ledger page
  (`accounts/show.blade.php`) only supports add/delete, not edit, so
  Rojmed matches that rather than introducing a new capability; the
  "add" button instead links to the accounts list so the admin can add a
  transaction from a specific account's own ledger.
- **Attendance (`App\Http\Controllers\AttendanceController`)** rebuilds
  `attendance.php`/`edit_attendance.php`/`add_attendance.php`/
  `add_admin_attendance.php`/`add_attendance_month.php`. Legacy's
  `add_attendance.php` (today only) and `add_admin_attendance.php`
  (any date, otherwise byte-for-byte identical — both gated by the same
  `"Add Attendance"` permission) are merged into one `create()`/`store()`
  pair with a date field that defaults to today but is editable, rather
  than porting two near-duplicate pages. The month grid
  (`month()`/`storeMonth()`) reproduces the legacy layout exactly: one
  checkbox row + one overtime-input row per employee, one column per day
  of the selected month — every day in the month is written on save
  (defaulting to absent/0 for any day left blank), matching legacy's
  "always fill the whole month" semantics rather than only writing the
  cells that were touched. `App\Policies\AttendancePolicy` doesn't
  extend `LegacyModulePolicy`: this is the one module where the create
  permission is `"Add Attendance"`, not the usual `"Add New {module}"`.
  Confirmed `employee.employee` (the eligibility flag this module
  filters employees by) defaults to `1` at the DB level and is `NOT
  NULL`, so employees created via the already-shipped Employee module
  (which never sets this column) still show up correctly here — not a
  cross-module gap.
- **Salary Sheet (`App\Http\Controllers\SalarySheetController`)**
  rebuilds `salary_bill.php`/`gst_salary_sheet_pdf()` — a P/A attendance
  register with pay totals across all active employees for a date range,
  as browser HTML, PDF, and Excel (`App\Exports\SalarySheetExport`).
  **Deliberately filters employees by `status=1` only, not
  `employee=1 AND status=1` like Attendance** — read both legacy files
  side by side to confirm this is a genuine, intentional difference in
  the two modules' eligibility rules, not a copy-paste bug to "fix" into
  consistency. The pay formula (`total_days × par_day_amount +
  total_over_time × (par_day_amount / 8)`) matches Salary Slip's
  `basic_pay`, but **unlike** the payslip, legacy's salary sheet does
  **not** round each term — reproduced exactly (no rounding here either).
  Gated by a single legacy permission (`"Salary Sheet"`) via a closure
  Gate, same pattern as `view-gst-report`/`send-email`.
- **API layer (`routes/api.php`, `App\Http\Controllers\Api\*`,
  Laravel Sanctum)**: the original migration roadmap described this as
  "the 61-file custom REST dispatcher" — that estimate turned out to be
  wrong, and it mattered enough to verify before writing any code.
  Investigated the legacy `api/` directory directly rather than trusting
  the roadmap's file count: stripping out a vendored PHPMailer copy and
  an entirely unrelated leftover project file
  (`api/rest/api_bbb.php` — sales/order/notification functions from a
  different client's app, never wired into this app's routing) leaves
  **four real endpoints** in `api/rest/api.php`: `login`, `logout`,
  `get_attendance`, `save_attendance` — a narrow mobile
  attendance-marking API, not a general billing/estimate/employee CRUD
  layer. Its own server log
  (`api/rest/error_log`) shows consistent real traffic only from
  Jun 2021–Jan 2022, then nothing but one failed-connection probe in
  Jan 2024 — no in-repo consumer, no documentation, and no evidence of
  current use. `api/employee.php` (a separate, non-dispatched script
  dumping the full employee list with **zero authentication**) is a
  legacy vulnerability, not a feature — intentionally not carried
  forward.
  - Rebuilt the real four-endpoint surface — `POST /api/login`,
    `POST /api/logout`, `GET /api/attendance`, `POST /api/attendance` —
    behind **Sanctum bearer tokens**, since legacy's `login` returned no
    token/session artifact at all and every subsequent call
    (`get_attendance`/`save_attendance`/`logout`) blindly trusted a
    client-supplied `user_id` with no server-side verification. This is
    a fresh, secure design rather than a byte-compatible port — there
    was no live client to preserve exact compatibility for.
  - `Api\AuthController::login()` reuses `LoginRequest::resolveUser()`
    (the same MD5-upgrade-shim/rate-limiting logic the web login uses)
    rather than duplicating it — **but not** `LoginRequest::authenticate()`,
    which additionally calls `Auth::login()` to start a web session
    guard login. That's the right behavior for the Blade login form and
    the wrong one for a stateless token API — reusing it as-is caused a
    real bug, caught by an automated test: a Sanctum-authenticated
    request would intermittently resolve as a `TransientToken` (no
    `delete()` method) instead of a real revocable database token,
    because the session guard was quietly also being logged in
    underneath the API call. Fixed by splitting `LoginRequest` into
    `resolveUser()` (credential verification only, shared by both flows)
    and `authenticate()` (web-only, calls `Auth::login()` on top of
    `resolveUser()`).
  - `Api\AttendanceController` shares the same
    `Employee::scopeEligibleForAttendance()` query scope as the admin
    panel's `AttendanceController` (`employee=1 AND status=1`) — the
    scope was extracted from the admin controller specifically so both
    could use it without duplicating the eligibility rule.
- **Expense/Income** (`App\Http\Controllers\ExpenseController`/
  `IncomeController`) are department-scoped ledgers found during the
  Phase 7 audit — both plain `LegacyModulePolicy` CRUD, same shape as
  Department/Product. `Income` has no search box wired up in this
  rebuild because legacy's own `income.php` doesn't either (`$aColumns`
  is an empty array there — the search input is rendered but never
  actually filters anything), so an unfiltered list here matches
  observed legacy behavior rather than "fixing" a UX gap nobody asked
  for.
- **Dashboard department overview** (`App\Http\Controllers\DashboardController`)
  rebuilds `ajax_show_overview.php`'s per-department Income vs.
  Expense+Billed-work summary — but as a **working** version, not a
  port. Legacy's own `index.php` never actually renders this: the
  department-select/date-range form and `#data_show` container that
  `ajax_show_overview.php`'s JS listens for don't exist anywhere in that
  page's markup, so the feature was dead on arrival in the legacy app
  (confirmed by reading `index.php` in full, not just the AJAX handler).
  The underlying calculation is sound and useful, so this rebuilds it
  with a real, working form on the dashboard instead of silently
  carrying the dead JS listeners forward. Matches legacy's calc exactly:
  bill totals for the department/range are folded into "expense"
  alongside actual `Expense` records, not tracked separately.
- **Bill photos** (`App\Http\Controllers\BillPhotoController`) rebuilds
  `add_edit_photo.php`'s multi-photo attachment feature, still stored
  comma-separated in `bill.photo` (not worth a new table for what's
  always been a flat list). Uses Laravel's storage disk instead of the
  legacy hand-rolled random-filename generator (`upload_image()`), so
  `photo` here holds storage paths (`bill-photos/xyz.jpg`) rather than
  legacy's bare filenames. **Pre-existing legacy photo values will 404**
  (broken image icon, not a crash) — this app only ever had a copy of
  the database, never the legacy upload directory, so those files were
  never in scope to migrate; nothing to fix on this end.
- **Employee ledger** (`App\Http\Controllers\EmployeeController::show()`,
  `App\Http\Controllers\EmployeeDetailController`) rebuilds
  `view_employee.php`/`add_edit_c_d_employee.php` — a Credit/Debit
  transaction ledger per employee, structurally identical to the Account
  ledger (same `Debit − Credit = balance` convention, same nested
  store/destroy controller shape). `EmployeePolicy::view()` overrides the
  `LegacyModulePolicy` default the same way `AccountPolicy::view()`
  does, for the same reason: legacy splits list access (`"Employee"`)
  from viewing one employee's ledger (`"View Employee"`) into two
  separate permissions. This is also the ledger that
  `SalarySlipController` writes Credit rows into for a slip's advance
  deduction (see the Salary Slip notes above) — those rows now show up
  here too, alongside any ad-hoc entries added directly.
- **Measurement sheet editors** (`App\Http\Controllers\
  MeasurementBillController`/`MeasurementEstimateController`) rebuild
  `add_edit_bill_measurement.php`/`add_edit_estimate_measurement.php` +
  their print views — the most structurally complex remaining legacy
  feature (confirmed by diffing the two files: identical except
  `b_id`/`e_id`). A measurement sheet is nested two levels deep: a list
  of product "groups", each with its own list of length×breadth
  measurement "lines" — edited via a new shared Alpine component
  (`measurementForm` in `resources/js/app.js`).
  - **Builds directly against the already-normalized
    `measurement_bill_items`/`measurement_bill_item_lines` tables**
    (populated back in Phase 3 by `ImportLegacyMeasurements`) instead of
    round-tripping through the legacy `[#]`/`[@]`/`[(@)]`-delimited
    `measurement_bill.product` blob — consistent with how `bill_items`/
    `estimate_items` already work; new features never write the
    delimited format.
  - The quantity formula (`no × length × breath × unit`, blank fields
    default to 1) and group-total formula (sum of that group's line
    quantities) reproduce legacy's `count_total_bill()` JS exactly.
  - `"Edit Measurement"` is a single permission shared by **both** the
    Bill and Estimate editors in legacy (not two separate per-module
    permissions) — `Gate::define('edit-measurement', ...)` reflects that
    directly rather than splitting it artificially. Printing does have
    separate permissions per module (`"Print Bill Measurement"`/
    `"Print Estimate Measurement"`), added as a `printMeasurement()`
    ability on `BillPolicy`/`EstimatePolicy`.
  - **Not ported**: the "Copy Measurement" convenience on the Bill
    editor that appends an Estimate's measurement sheet onto the Bill's
    — a nice-to-have layered on top of the core editor, not the editor
    itself.
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
