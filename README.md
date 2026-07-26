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
| 2 | Master-data CRUD (Department, Designation, Employee, Product, Sub Admin) | ⏳ Not started |
| 3 | Normalize `bill`/`estimate` line items out of delimited text columns | ⏳ Not started |
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
