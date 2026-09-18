# Architecture

> Last updated: 2026-09-18
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
│   │   │   ├── TeacherController.php     (teacher portal)
│   │   │   ├── Concerns/     (RendersReportCards — shared preview/PDF trait)
│   │   │   ├── Admin/        (see controllers.md — 24 controllers: students,
│   │   │   │                  teachers, parents, classes, subjects, fees,
│   │   │   │                  payments + submissions, payment settings,
│   │   │   │                  categories, audit logs, reports, analytics,
│   │   │   │                  academic years, terms, holidays, grade levels,
│   │   │   │                  periods, timetable, announcements, promotions,
│   │   │   │                  report cards, registration requests, user accounts)
│   │   │   ├── Auth/         (9 controllers incl. ParentRegistrationController)
│   │   │   ├── Parent/       (ParentController, PaymentSubmissionController)
│   │   │   ├── Student/      (StudentController, AssignmentController)
│   │   │   └── Teacher/      (AssignmentController, ReportCardController)
│   │   ├── Middleware/       (CheckRole, EnsureAccountIsActive, EnsurePasswordIsChanged)
│   │   └── Requests/         (Login [in Auth/], StoreFee, StorePayment, StorePaymentSubmission,
│   │                          StoreParentRegistration)
│   ├── Models/               (34 Eloquent models — see models.md)
│   ├── Notifications/        (fee reminder / overdue / payment confirmation /
│   │                          payment-submission reviewed / registration reviewed)
│   ├── Observers/            (ReportCacheObserver)
│   ├── Policies/             (PaymentPolicy, ReportPolicy)
│   ├── Providers/
│   ├── Services/             (Analytics, Announcement, Promotion, ReportCard)
│   ├── Traits/               (Auditable, GeneratesTemporaryPassword)
│   └── View/
├── bootstrap/
├── config/
├── database/
│   ├── factories/            (12 factories)
│   ├── migrations/           (56 migration files)
│   └── seeders/              (18 domain seeders + DatabaseSeeder orchestrator)
├── public/
├── resources/
│   └── views/                (158 Blade templates — see views.md)
│       ├── admin/            (19 subdirectories — see views.md)
│       ├── auth/
│       ├── components/
│       ├── errors/
│       ├── layouts/          (app, guest, parent, student, teacher)
│       ├── parent/           (+ partials/)
│       ├── profile/          (+ partials/)
│       ├── reports/          (report-card.blade.php — shared by all portals)
│       ├── shared/           (timetable-grid.blade.php)
│       ├── student/          (+ assignments/, partials/)
│       ├── students/         (profile-content.blade.php)
│       └── teacher/          (+ assignments/, report-cards/)
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

| Layer      | Technology                                                                           |
| ---------- | ------------------------------------------------------------------------------------ |
| Language   | PHP 8.2+                                                                             |
| Framework  | Laravel 12.x                                                                         |
| Frontend   | Blade, Tailwind CSS 3, Alpine.js                                                     |
| Icons      | Font Awesome 6.5.1 (solid) + Material Symbols Outlined — vendored in `public/fonts/` |
| Build tool | Vite 6                                                                               |
| Database   | MySQL (SQLite supported for local development)                                       |
| Auth       | Laravel Breeze                                                                       |
| PDF        | barryvdh/laravel-dompdf 3.1                                                          |
| PWA        | vite-plugin-pwa (installed, not wired in Vite)                                       |
| Testing    | PHPUnit 11                                                                           |
| Dev runner | Concurrently (artisan serve + queue + vite)                                          |

Portal chrome (admin, parent, teacher) uses Material Symbols plus Inter / JetBrains Mono tokens defined in `tailwind.config.js`. The icon fonts themselves are vendored in `public/fonts/` and linked with `asset()` — see assets-and-icons.md.

---

## 2.1 Application Layers

Beyond the standard Laravel layers, the app uses a small service/observer layer for logic shared across portals.

| Class                             | Layer        | Responsibility                                                                                             |
| --------------------------------- | ------------ | ---------------------------------------------------------------------------------------------------------- |
| `AnalyticsService`                | `app/Services` | Attendance/fee-aging/school-wide report figures, with version-keyed caching and `flush()` invalidation.  |
| `AnnouncementService`             | `app/Services` | Audience resolution, reach summaries (for the admin preview), read tracking and unread counts.             |
| `PromotionService`                | `app/Services` | Year-end promotion runs, blockers, default outcomes, per-student records and rollback.                     |
| `ReportCardService`               | `app/Services` | The single build() of a report card (subject rows, averages, ranks), finalize/unfinalize, lock checks.     |
| `ReportCacheObserver`             | `app/Observers` | Flushes cached reports whenever a model that feeds them is saved/deleted/restored.                        |
| `Auditable`                       | `app/Traits`   | Writes create/update/delete events to `audit_logs`, honouring a per-model `$auditExclude` list.            |
| `GeneratesTemporaryPassword`      | `app/Traits`   | Readable one-time passwords (no `I`, `O` or `L`) for admin-created or newly admitted accounts.             |
| `CalendarHelper`                  | `app/Helpers`  | Shared calendar/term helpers used by calendar and timetable screens.                                       |

`AnalyticsService` deliberately delegates term averages to `ReportCardService`, so a student's average in a
school-wide report always matches their printed report card.

---

## 3. File Counts

- **34** Eloquent models
- **56** Migrations (3 Laravel defaults + domain create/alter/backfill files)
- **19** Seeders (18 domain seeders + 1 `DatabaseSeeder` orchestrator)
- **12** Factories
- **24** Admin controllers (students/settings plus dedicated controllers for staff, fees, payments + submissions, payment settings, announcements, promotions, report cards, registration requests, user accounts, analytics, calendar, timetable, reports)
- **9** Auth controllers (8 Breeze + `ParentRegistrationController`)
- **3** Custom middleware (`CheckRole`, `EnsureAccountIsActive`, `EnsurePasswordIsChanged`)
- **4** Services (`AnalyticsService`, `AnnouncementService`, `PromotionService`, `ReportCardService`)
- **1** Observer (`ReportCacheObserver`)
- **5** Requests, **5** Notifications, **2** Traits, **2** Policies
- **158** Blade views across admin, auth, components, errors, layouts, parent, profile, reports, shared, student, students, teacher (see views.md)
- The static admin/parent HTML and teacher Stitch prototypes that used to live under `Frontend/` and `stitch_grail_sis_teacher_portal/` have been **removed** from the repository — the Blade portals under `resources/views/` are the live UI (see frontend-prototypes.md)
- **23** Feature test files (17 top-level + 6 in `Feature/Auth/`) + **2** Unit tests + 1 base TestCase

---

_End of architecture documentation._
