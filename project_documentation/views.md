# Views

> Last updated: 2026-08-02
> Update this file when views are added or modified.

---

All templates live under `resources/views/`.

---

## 9.1 Top-level

- `welcome.blade.php` — public landing page
- `dashboard.blade.php` — generic dashboard dispatcher
- `login.blade.php`, `mark_entry.blade.php`
- `errors/419.blade.php` — custom CSRF token mismatch page

---

## 9.2 Admin (`resources/views/admin/`)

- `dashboard.blade.php` — KPI cards + recent teachers + student roster
- `settings.blade.php` — admin settings screen based on the frontend prototype
- `examinations.blade.php` — examinations/results overview based on the frontend prototype
- `header.blade.php`, `sidebar.blade.php` — admin layout chrome
- `classes/` — class management views
- `parents/` — parent management views
- `students/` — student CRUD (index, create, edit, show) with the create form now styled from the frontend prototype
- `subjects/` — subject management views
- `teachers/` — teacher management views

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

Standard Breeze components: `application-logo`, `auth-session-status`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`.

---

## 9.5 Layouts (`resources/views/layouts/`)

- `app.blade.php` — main authenticated layout
- `guest.blade.php` — guest layout
- `navigation.blade.php` — top nav

---

## 9.6 Role dashboards

- `parent/dashboard.blade.php`
- `student/dashboard.blade.php`
- `teacher/marks.blade.php`

---

## 9.7 Profile

- `profile/edit.blade.php` + `profile/partials/`

---

_End of views documentation._
