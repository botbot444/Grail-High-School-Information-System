# Architecture

> Last updated: 2026-09-13
> Update this file when the project structure, tech stack, or file counts change.

---

## 1. Directory Structure

```
grail/
├── app/
│   ├── Helpers/              (CalendarHelper)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php   (role dispatch + admin dashboard)
│   │   │   ├── ProfileController.php
│   │   │   ├── TeacherController.php     (teacher portal)
│   │   │   ├── Admin/        (students, teachers, parents, classes, subjects,
│   │   │   │                  fees, payments, categories, audit logs, reports,
│   │   │   │                  academic years, terms, holidays, grade levels)
│   │   │   ├── Auth/         (Breeze auth controllers)
│   │   │   ├── Parent/       (ParentController — full parent portal)
│   │   │   └── Student/      (StudentController)
│   │   ├── Middleware/       (CheckRole.php)
│   │   └── Requests/         (Profile, Auth, StoreFee, StorePayment)
│   ├── Models/               (19 Eloquent models — see models.md)
│   ├── Notifications/        (fee reminder / overdue / payment confirmation)
│   ├── Policies/             (PaymentPolicy, ReportPolicy)
│   ├── Providers/
│   ├── Traits/               (Auditable)
│   └── View/
├── bootstrap/
├── config/
├── database/
│   ├── factories/            (10 factories)
│   ├── migrations/           (33 migration files)
│   └── seeders/              (15 domain seeders + DatabaseSeeder orchestrator)
├── Frontend/                 (Static HTML/CSS/JS prototypes — not served)
│   ├── AdminViews/
│   └── ParentViews/
├── stitch_grail_sis_teacher_portal/  (Stitch HTML + screenshots for teacher UI)
├── public/
├── resources/
│   └── views/                (Blade templates — see views.md)
│       ├── admin/
│       ├── auth/
│       ├── components/
│       ├── errors/
│       ├── layouts/          (app, guest, navigation, parent, teacher)
│       ├── parent/
│       ├── profile/
│       ├── student/
│       └── teacher/
│       ├── dashboard.blade.php
│       ├── login.blade.php
│       ├── mark_entry.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── auth.php
│   ├── console.php
│   └── web.php
├── storage/
└── tests/
    ├── Feature/
    │   └── Auth/
    └── Unit/
```

---

## 2. Tech Stack

| Layer      | Technology                                      |
| ---------- | ----------------------------------------------- |
| Language   | PHP 8.2+                                        |
| Framework  | Laravel 12.x                                    |
| Frontend   | Blade, Tailwind CSS 3, Alpine.js                |
| Build tool | Vite 6                                          |
| Database   | MySQL (SQLite supported for local development)  |
| Auth       | Laravel Breeze                                  |
| PDF        | barryvdh/laravel-dompdf 3.1                     |
| PWA        | vite-plugin-pwa (installed, not wired in Vite)  |
| Testing    | PHPUnit 11                                      |
| Dev runner | Concurrently (artisan serve + queue + vite)     |

Portal chrome (admin, parent, teacher) uses Material Symbols plus Inter / JetBrains Mono tokens defined in `tailwind.config.js`.

---

## 3. File Counts

- **19** Eloquent models
- **33** Migrations (3 Laravel defaults + domain create/alter/backfill files)
- **16** Seeders (15 domain seeders + 1 `DatabaseSeeder` orchestrator)
- **10** Factories
- **14** Admin controllers (students/settings plus dedicated resource controllers for staff, fees, calendar, reports)
- **9** Breeze auth controllers
- **1** Custom middleware (`CheckRole`)
- Admin, parent, teacher, student, auth, profile, and layout Blade views (see views.md)
- Static admin/parent HTML under `Frontend/` plus Stitch teacher screens under `stitch_grail_sis_teacher_portal/`
- **18** Feature test files (12 top-level + 6 in `Feature/Auth/`) + 1 Unit test + 1 base TestCase

---

_End of architecture documentation._
