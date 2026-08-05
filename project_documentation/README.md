# Grail - High School Information System

> **Project Documentation Hub**
> Last updated: 2026-08-02
> This is the entry point for all project documentation. Each file below covers a specific concern so you can read and update only what you need.

---

## Documentation Index

| File                                                                   | Purpose                                                                                         | Update When                              |
| ---------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------- | ---------------------------------------- |
| [architecture.md](architecture.md)                                     | Directory structure, tech stack, file counts                                                    | Project structure or dependencies change |
| [database/schema.md](database/schema.md)                               | All 11 database tables: columns, types, FKs, notes                                              | Migrations are added/modified            |
| [database/seeders-and-factories.md](database/seeders-and-factories.md) | Seeders and factories                                                                           | Seeders or factories change              |
| [models.md](models.md)                                                 | All 11 Eloquent models (PKs, fillable, casts, relationships, scopes, accessors, business logic) | Models are added/modified                |
| [controllers.md](controllers.md)                                       | All controllers (top-level, Admin, Parent, Student, Auth)                                       | Controllers are added/modified           |
| [routes.md](routes.md)                                                 | Full route table (methods, URIs, names, middleware)                                             | Routes are added/modified                |
| [middleware-and-error-handling.md](middleware-and-error-handling.md)   | CheckRole middleware + custom 419 handling                                                      | Middleware or error handling changes     |
| [views.md](views.md)                                                   | Blade templates, components, layouts, role dashboards                                           | Views are added/modified                 |
| [frontend-prototypes.md](frontend-prototypes.md)                       | Static HTML/CSS/JS prototypes (AdminViews, ParentViews)                                         | Frontend prototypes change               |
| [business-logic.md](business-logic.md)                                 | Fee state machine, grade letter calc, role auth, marks entry                                    | Business logic changes                   |
| [setup-and-conventions.md](setup-and-conventions.md)                   | Environment/setup steps, known conventions/gotchas                                              | Setup process or conventions change      |
| [tests.md](tests.md)                                                   | Test suite overview                                                                             | Tests are added/modified                 |

---

## Quick Start

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

## Tech Stack at a Glance

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

_End of documentation hub. For detailed information, see the linked files above._
