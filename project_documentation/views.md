# Views

> Last updated: 2026-09-18
> Update this file when views are added or modified.

---

All templates live under `resources/views/` — **158** `.blade.php` files in total.

---

## 9.1 Top-level

- `welcome.blade.php` — public landing page (redesigned with the login/register screens)
- `dashboard.blade.php` — fallback dashboard if a user has no recognised role
- `login.blade.php`, `mark_entry.blade.php`
- `errors/419.blade.php` — custom CSRF token mismatch page

---

## 9.2 Admin (`resources/views/admin/`) — 19 subdirectories

- `dashboard.blade.php` — KPI cards + recent teachers + student roster
- `settings.blade.php` — admin settings
- `examinations.blade.php` — examinations/results overview
- `header.blade.php`, `sidebar.blade.php` — admin layout chrome
- `partials/` — shared admin fragments
- `announcements/` — index / create / edit (`preview` is a JSON endpoint, not a view)
- `audit-logs/index.blade.php`
- `calendar/` — one directory per resource:
    - `academic-years/`, `terms/`, `holidays/`, `grade-levels/`, `periods/`
- `classes/` — class management
- `fees/` — fee index/create/edit/show plus `receipt.blade.php`
- `parents/` — parent management
- `payment-submissions/` — `index.blade.php`, `show.blade.php` (approve / reject)
- `payments/_form.blade.php` — payment form partial
- `promotions/` — `index.blade.php`, `show.blade.php`, `mappings.blade.php`
- `registration-requests/` — `index.blade.php`, `show.blade.php` (approve / reject)
- `report-cards/` — `index.blade.php` (browse by class/term; unfinalize form)
- `reports/` — `fee-collection.blade.php`, `attendance.blade.php`, `aging.blade.php`, `school-wide.blade.php`
- `settings/categories.blade.php` — fee categories
- `settings/payment-instructions.blade.php` — bank / mobile-money details shown to parents
- `students/` — student CRUD plus `financial-summary.blade.php` and `statement.blade.php`; in `create.blade.php` the "Student Login" section is **mandatory** (`email` is `required`) and a one-time password is always generated and shown once on save
- `subjects/` — subject management
- `teachers/` — teacher management
- `timetable/builder.blade.php` — class/term timetable builder
- `users/index.blade.php` — account management (activate, reset password, change role)

---

## 9.3 Auth (`resources/views/auth/`)

- `confirm-password.blade.php`
- `forgot-password.blade.php`
- `login.blade.php`
- `parent-register.blade.php` — public parent + child sign-up form
- `registration-submitted.blade.php` — "your request is with the office" confirmation
- `reset-password.blade.php`
- `verify-email.blade.php`

---

## 9.4 Components (`resources/views/components/`)

Standard Breeze components: `application-logo`, `auth-session-status`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`. Extra: `status-pill` (per-row status badge) and `flash` (flash-message banner).

---

## 9.5 Layouts (`resources/views/layouts/`)

- `app.blade.php` — main authenticated layout (declares the vendored Font Awesome / Material Symbols `<head>` links)
- `guest.blade.php` — guest layout (no icon fonts)
- `parent.blade.php` — parent portal shell (sidebar + header)
- `student.blade.php` — student portal shell
- `teacher.blade.php` — teacher portal shell (sidebar + header)

> Breeze's `navigation.blade.php` was removed along with the `/profile` screens; the sidebar/header partials now carry
> the portal navigation.

---

## 9.6 Parent portal (`resources/views/parent/`)

Uses `layouts.parent`. Pages:

- `dashboard.blade.php`, `children.blade.php`, `attendance.blade.php`, `performance.blade.php`, `reports.blade.php`, `timetable.blade.php`, `assignments.blade.php`, `announcements.blade.php`, `fees.blade.php`, `settings.blade.php`
- `header.blade.php`, `sidebar.blade.php`, `partials/`

---

## 9.7 Teacher portal (`resources/views/teacher/`)

Uses `layouts.teacher`. Pages:

- `dashboard.blade.php` — KPIs, tasks, activity, events
- `classes.blade.php` — rostered sections with stats
- `roster.blade.php` — teacher-owned class roster with term-aware averages and attendance
- `student-profile.blade.php` — a single student, seen from the teacher's classes
- `marks.blade.php` — mark + attendance entry
- `timetable.blade.php` — read-only grids for each class scheduled for the authenticated teacher
- `attendance.blade.php` — a dedicated attendance page (replaces the old placeholder)
- `performance.blade.php` — class performance summary and grade finalization
- `announcements.blade.php` — read-only feed with read tracking
- `settings.blade.php` — teacher settings (replaces the old placeholder)
- `placeholder.blade.php` — still available for any not-yet-built destination
- `header.blade.php`, `sidebar.blade.php`
- `assignments/` — `index.blade.php`, `create.blade.php`, `edit.blade.php`, `submissions.blade.php`, `_form.blade.php`
- `report-cards/` — `index.blade.php`, `show.blade.php` (class + finalize), `subject-comments.blade.php`

---

## 9.8 Student portal (`resources/views/student/`) and shared views

Student portal pages:

- `dashboard.blade.php`, `results.blade.php`, `attendance.blade.php`, `timetable.blade.php`, `report-cards.blade.php`, `announcements.blade.php`, `settings.blade.php`
- `assignments/` — `index.blade.php`, `show.blade.php`, `_submitted-files.blade.php`
- `partials/` — `header.blade.php`, `sidebar.blade.php`, `stat-card.blade.php`, `status-pill.blade.php`, `empty-state.blade.php`

Shared / other:

- `reports/report-card.blade.php` — the **single** report-card template rendered by all four portals (HTML preview and DomPDF)
- `shared/timetable-grid.blade.php` — reusable timetable grid used by admin/teacher/parent/student screens
- `students/profile-content.blade.php` — shared student profile block
- `profile/partials/update-password-form.blade.php` — the only surviving profile view; `/profile` is no longer routed and `profile/edit.blade.php` has been deleted (settings now live on each portal's own settings page)

---

_End of views documentation._
