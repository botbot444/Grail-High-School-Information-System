# Views

> Last updated: 2026-09-13
> Update this file when views are added or modified.

---

All templates live under `resources/views/`.

---

## 9.1 Top-level

- `welcome.blade.php` — public landing page
- `dashboard.blade.php` — fallback dashboard if a user has no recognised role
- `login.blade.php`, `mark_entry.blade.php`
- `errors/419.blade.php` — custom CSRF token mismatch page

---

## 9.2 Admin (`resources/views/admin/`)

- `dashboard.blade.php` — KPI cards + recent teachers + student roster
- `settings.blade.php` — admin settings
- `examinations.blade.php` — examinations/results overview
- `header.blade.php`, `sidebar.blade.php` — admin layout chrome
- `classes/` — class management
- `parents/` — parent management
- `students/` — student CRUD plus `financial-summary.blade.php` and `statement.blade.php`
- `subjects/` — subject management
- `teachers/` — teacher management
- `fees/` — fee index/create/edit/show plus `receipt.blade.php`
- `payments/_form.blade.php` — payment form partial
- `settings/categories.blade.php` — fee categories
- `audit-logs/index.blade.php`
- `reports/fee-collection.blade.php`
- `calendar/` — academic years, terms, holidays, grade levels, and period CRUD
- `timetable/builder.blade.php` — class/term timetable builder

---

## 9.3 Auth (`resources/views/auth/`)

- `confirm-password.blade.php`
- `forgot-password.blade.php`
- `login.blade.php`
- `register.blade.php`
- `reset-password.blade.php`
- `verify-email.blade.php`

---

## 9.4 Components (`resources/views/components/`)

Standard Breeze components: `application-logo`, `auth-session-status`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`. Extra: `status-pill`.

---

## 9.5 Layouts (`resources/views/layouts/`)

- `app.blade.php` — main authenticated layout
- `guest.blade.php` — guest layout
- `navigation.blade.php` — Breeze top nav
- `parent.blade.php` — parent portal shell (sidebar + header)
- `teacher.blade.php` — teacher portal shell (sidebar + header)

---

## 9.6 Parent portal (`resources/views/parent/`)

Uses `layouts.parent`. Pages:

- `dashboard.blade.php`, `children.blade.php`, `attendance.blade.php`, `performance.blade.php`, `reports.blade.php`, `assignments.blade.php`, `fees.blade.php`, `settings.blade.php`
- `header.blade.php`, `sidebar.blade.php`

---

## 9.7 Teacher portal (`resources/views/teacher/`)

Uses `layouts.teacher`. Pages:

- `dashboard.blade.php` — KPIs, tasks, activity, events
- `classes.blade.php` — rostered sections with stats
- `roster.blade.php` — teacher-owned class roster with term-aware averages and attendance
- `marks.blade.php` — mark + attendance entry
- `timetable.blade.php` — read-only grids for each class scheduled for the authenticated teacher
- `performance.blade.php` — class performance summary and grade finalization
- `placeholder.blade.php` — “coming soon” for dedicated announcements and settings
- `header.blade.php`, `sidebar.blade.php`
- `timetable.blade.php` — selected child's read-only class timetable

---

## 9.8 Student / profile

- `student/dashboard.blade.php`
- `student/timetable.blade.php` — authenticated student's read-only class timetable
- `profile/edit.blade.php` + `profile/partials/`

---

_End of views documentation._
