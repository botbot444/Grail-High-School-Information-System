# Setup and Conventions

> Last updated: 2026-08-02
> Update this file when the setup process or project conventions change.

---

## 14. Environment & Setup

`.env` requirements (see `.env.example`):

- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- `DB_CONNECTION=sqlite` (default) — or MySQL/Postgres
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

- **Custom primary keys** on every domain model (`student_id`, `teacher_id`, `class_id`, `subject_id`, `class_subject_id`, `attendance_id`, `grade_id`, `fee_id`, `parent_id`) — always pass these as the route-model-binding key.
- **Soft deletes** on `Student`, `Teacher`, `SchoolClass`, `ParentProfile`.
- **Two role columns** on `users`: `role` (legacy string) and `role_id` (FK to `roles`). `User::role_name` prefers the FK relation but falls back to the string.
- **Pivot upgraded to model**: `class_subjects` is treated as a full Eloquent model because `grades` and `attendances` both reference it.
- **Fee status is computed** — never write to `fees.status` directly; use `recordPayment()` / `reversePayment()`.
- **`users.role` migration ordering**: `users` is created in the default Laravel migration (`0001_01_01_000000`), then `roles` is created in `2026_06_01_000012`, then `role_id` is added in `2026_06_01_000013`. The `User` model tolerates this via the fallback logic in `role_name`.
- **`TeacherController::storeMarks`** now uses the full `grades` schema (`score`, `max_score`, `term`, `academic_year`, `assessment_type`). The `Grade` model provides a `marks` accessor/mutator that maps to `score` for backward compatibility with views that reference `marks`.
  -- **`AdminController::dashboard`** previously queried `Fee::where('status', 'paid')` — this was inconsistent with the Fee state machine (which uses `Pending`/`Partially Paid`/`Cleared`). The codebase now uses `Fee::cleared()` and related scopes; ensure seeders create `Cleared` records where intended.

Status: Resolved (2026-08-05) — seeders were re-run against the XAMPP MySQL dev DB and the admin dashboard KPI now correctly reports cleared fees. If you switch back to SQLite for local experiments, be aware seeders may need review to ensure status strings match the state-machine values.

---

_End of setup and conventions documentation._
