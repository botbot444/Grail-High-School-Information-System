# Architecture

> Last updated: 2026-08-02
> Update this file when the project structure, tech stack, or file counts change.

---

## 1. Directory Structure

```
grail/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── TeacherController.php
│   │   │   ├── Admin/        (AdminController, AdminClassController, AdminParentController, AdminSubjectController, AdminTeacherController)
│   │   │   ├── Auth/         (Breeze auth controllers)
│   │   │   ├── Parent/       (ParentController)
│   │   │   └── Student/      (StudentController)
│   │   ├── Middleware/       (CheckRole.php)
│   │   └── Requests/         (Profile + Auth requests)
│   ├── Models/               (11 Eloquent models — see models.md)
│   ├── Providers/
│   └── View/
├── bootstrap/
├── config/
├── database/
│   ├── factories/            (7 factories)
│   ├── migrations/           (14 migration files)
│   └── seeders/              (11 seeders)
├── Frontend/                 (Static HTML/CSS/JS prototypes)
│   ├── AdminViews/
│   └── ParentViews/
├── public/
├── resources/
│   └── views/                (Blade templates)
├── routes/
│   ├── auth.php
│   ├── console.php
│   └── web.php
├── storage/
└── tests/
    ├── Feature/
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
| Database   | MySQL                                           |
| Auth       | Laravel Breeze                                  |
| PDF        | barryvdh/laravel-dompdf 3.1                     |
| PWA        | vite-plugin-pwa (installed, not yet configured) |
| Testing    | PHPUnit 11                                      |
| Dev runner | Concurrently (artisan serve + queue + vite)     |

---

## 3. File Counts

- **11** Eloquent models
- **14** Migrations (3 Laravel defaults + 11 domain)
- **11** Database seeders
- **7** Factories
- **5** Admin resource controllers (+ 1 top-level AdminController handling students & dashboard)
- **9** Breeze auth controllers
- **1** Custom middleware (`CheckRole`)
- **~10** Admin Blade views + auth/profile/role dashboards
- **9** Static HTML admin prototypes + 1 static parent prototype
- **6** Feature test files + 1 base TestCase

---

_End of architecture documentation._
