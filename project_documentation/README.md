# Grail - High School Information System

> **Project Documentation Hub**
> Last updated: 2026-09-18
> This is the entry point for all project documentation. Each file below covers a specific concern so you can read and update only what you need.

---

## Documentation Index

| File                                                                   | Purpose                                                                                       | Update When                                  |
| ---------------------------------------------------------------------- | --------------------------------------------------------------------------------------------- | -------------------------------------------- |
| [architecture.md](architecture.md)                                     | Directory structure, tech stack, file counts, services/observers/traits                       | Project structure or dependencies change     |
| [database/schema.md](database/schema.md)                               | Database tables: columns, types, FKs, notes                                                   | Migrations are added/modified                |
| [database/seeders-and-factories.md](database/seeders-and-factories.md) | Seeders and factories                                                                         | Seeders or factories change                  |
| [models.md](models.md)                                                 | Eloquent models (PKs, fillable, casts, relationships, scopes, accessors, business logic)      | Models are added/modified                    |
| [controllers.md](controllers.md)                                       | All controllers (top-level, Admin, Parent, Student, Teacher, Auth)                            | Controllers are added/modified               |
| [routes.md](routes.md)                                                 | Full route table (methods, URIs, names, middleware)                                           | Routes are added/modified                    |
| [middleware-and-error-handling.md](middleware-and-error-handling.md)   | CheckRole + account-state middleware + custom 419 handling                                    | Middleware or error handling changes         |
| [views.md](views.md)                                                   | Blade templates, components, layouts, role dashboards                                         | Views are added/modified                     |
| [frontend-prototypes.md](frontend-prototypes.md)                       | Record of the removed static prototypes (AdminViews, ParentViews, teacher Stitch screens)      | Frontend prototypes change                   |
| [assets-and-icons.md](assets-and-icons.md)                             | Vendored icon fonts (`public/fonts/`), per-page `<head>` links, remaining remote assets       | Icon fonts, webfonts or global assets change |
| [business-logic.md](business-logic.md)                                 | Fee state machine + credits, grade letter calc, role auth, marks entry, report cards, portals | Business logic changes                       |
| [setup-and-conventions.md](setup-and-conventions.md)                   | Environment/setup steps, known conventions/gotchas                                            | Setup process or conventions change          |
| [user-manual.md](user-manual.md)                                       | End-user guide covering installation, sign-in, role actions, and quick-start usage            | User flow or install steps change            |
| [tests.md](tests.md)                                                   | Test suite overview                                                                           | Tests are added/modified                     |

The living implementation checklist is [`implementation_plan (2).md`](<../implementation_plan (2).md>) at the repo root (not part of this hub’s numbered files). End-user installation and day-to-day usage guidance lives in [`user-manual.md`](user-manual.md).

> The root checklist file is literally named `implementation_plan (2).md` (with a space and parentheses). If it is ever
> renamed to `implementation_plan.md`, update this link.

---

## Feature Phases

The codebase is organised around numbered delivery phases; most doc files reference them.

| Phase | Feature area                                                                          |
| ----- | ------------------------------------------------------------------------------------- |
| 1     | Fee ledger, payments, audit logging                                                   |
| 3     | School calendar (academic years, terms, holidays, grade levels, periods)              |
| 4     | Fee-collection reporting, student financials and statements                           |
| 5     | Announcements (audience targeting, reach preview, read tracking)                      |
| 6     | Year-end student promotion and rollback                                               |
| 9     | Analytics: attendance, fee aging, school-wide performance                             |
| 11    | Report cards: subject/overall comments, finalization, ranking, PDF                    |
| 12    | Account management: activation, roles, temporary passwords, parent/child registration |

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
| PWA        | vite-plugin-pwa (installed, not yet configured)                                      |
| Testing    | PHPUnit 11                                                                           |
| Dev runner | Concurrently (artisan serve + queue + vite)                                          |

---

_End of documentation hub. For detailed information, see the linked files above._
