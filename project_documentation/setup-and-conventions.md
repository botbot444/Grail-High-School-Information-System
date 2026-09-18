# Setup and Conventions

> Last updated: 2026-09-18
> Update this file when the setup process or project conventions change.

---

## 14. Environment & Setup

`.env` requirements (see `.env.example`):

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- `DB_CONNECTION=sqlite` (as shipped in `.env.example`) — or MySQL/Postgres. The XAMPP + MySQL path used for coursework
  is written up in [`user-manual.md`](user-manual.md). Both work; just make sure the `DB_*` block matching the connection
  you chose is the one left uncommented.
- `MAIL_*` for Breeze email verification / password reset
- `CACHE_STORE`, `QUEUE_CONNECTION`, `SESSION_DRIVER`

Bootstrap scripts (from `composer.json`):

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run dev          # in one terminal
php artisan serve    # in another
# or: composer dev   # runs server + queue + logs + vite together
```

---

## 15. Known Conventions / Gotchas

- **Custom primary keys** on domain models (`student_id`, `teacher_id`, `class_id`, `subject_id`, `class_subject_id`, `attendance_id`, `grade_id`, `fee_id`, `parent_id`, `year_id`, `term_id`, `holiday_id`, `grade_level_id`, `payment_id`, `fee_item_id`) — always pass these as the route-model-binding key.
- **Soft deletes** on `Student`, `Teacher`, `SchoolClass`, `ParentProfile`.
- **Two role columns** on `users`: `role` (legacy string) and `role_id` (FK to `roles`). `User::role_name` prefers the FK relation but falls back to the string.
- **Pivot upgraded to model**: `class_subjects` is treated as a full Eloquent model because `grades` and `attendances` both reference it.
- **Fee status is computed** — never write to `fees.status` directly; use `recordPayment()` / `reversePayment()`.
- **`users.role` migration ordering**: `users` is created in the default Laravel migration (`0001_01_01_000000`), then `roles` is created in `2026_06_01_000012`, then `role_id` is added in `2026_06_01_000013`. The `User` model tolerates this via the fallback logic in `role_name`.
- **`TeacherController::storeMarks`** uses the full `grades` schema (`score`, `max_score`, `term`, `academic_year`, `assessment_type`). The `Grade` model provides a `marks` accessor/mutator that maps to `score` for backward compatibility.
- **Admin dashboard route** is `DashboardController@adminDashboard` (today’s `Payment` totals, cached academic year/term). `AdminController::dashboard()` still exists but is not the named `admin.dashboard` route.
- **Parent/teacher layouts** live in `resources/views/layouts/{parent,teacher}.blade.php` and include role-specific sidebar/header partials.
- **Vite** binds the dev server to `127.0.0.1` (`vite.config.js`) so HMR works with XAMPP/local hosts.
- **Shared logic lives in services, not controllers** — report figures, announcement audiences, promotion runs and
  report-card assembly are all in `app/Services/` so the four portals cannot disagree.
- **Reports use version-keyed caching** — never tag-based. Call `AnalyticsService::flush()` after anything that changes
  the numbers; `ReportCacheObserver` already does this for the models the reports read.
- **Account state is enforced by middleware, not just at login** — `EnsureAccountIsActive` (immediate deactivation)
  and `EnsurePasswordIsChanged` (must-change-password lock) are appended to the `web` group in `bootstrap/app.php`.
- **`users.is_active` has a PHP-side default** (`protected $attributes = ['is_active' => true]`) because
  `Model::create()` does not re-read DB defaults; without it a freshly created user would read as deactivated.
- **`User` deliberately has no `password => hashed` cast** — every write site calls `Hash::make()` itself, so the cast
  would double-hash. Do not re-add it.
- **Proof-of-payment files** use `PaymentSubmission::proof_url` (which calls `asset()`), not
  `Storage::disk('public')->url()`. `asset()` falls back to the current request's host/port, so links keep working when
  `APP_URL` does not match the host actually being browsed.
- **`students.class_id` is nullable by design** — a newly admitted student stays unplaced until an admin assigns a class.
- **`/profile` is not routed and its controller is gone** — `ProfileController` and `ProfileUpdateRequest` were deleted;
  only `resources/views/profile/partials/update-password-form.blade.php` survives as an orphan view. Settings now live on
  each portal's `settings` action. Do not document `profile.*` route names.
- **Announcement authoring is admin-only** — there is no teacher authoring route (teachers get a read-only feed).
- **Creating a student always creates a login** — `AdminController@store` `require`s a unique `email` and provisions that
  student's `User` inside the same transaction, flashing a one-time password (`GeneratesTemporaryPassword`,
  `must_change_password = true`). The old email-optional student path was removed in `7dbf85f`; approved registrations
  reach the same end state.
- **Seeder dates are `Y-m-d`** — MySQL rejects `d-m-Y` strings, so keep new seeders on `Y-m-d` (see
  database/seeders-and-factories.md).
- **Static prototypes were deleted** — `Frontend/` and `stitch_grail_sis_teacher_portal/` no longer exist in the repo; do
  not add links or asset paths pointing at them (see frontend-prototypes.md).

Status: Resolved (2026-08-05) — seeders were re-run against the XAMPP MySQL dev DB and fee statuses use the state-machine values. If you switch back to SQLite for local experiments, confirm enum/status strings still match (`Pending` / `Partially Paid` / `Cleared` / `Overdue`).

---

_End of setup and conventions documentation._
