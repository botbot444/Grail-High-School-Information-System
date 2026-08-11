# Grail — High School Information System

A Laravel 12 application for managing high school operations: classes, subjects,
teachers, students, parents, attendance, grades, and fees with role-based access
for admins, teachers, parents, and students.

> **Documentation lives in [`project_documentation/`](./project_documentation/)**.
> Start at [`project_documentation/README.md`](./project_documentation/README.md)
> for the full documentation hub (setup, architecture, schema, models, routes, tests, etc.).

## Quick Start

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite      # or use MySQL — see env config
php artisan migrate --seed
npm install
npm run dev          # in one terminal (Vite)
php artisan serve    # in another
# or: composer dev   # runs server + queue + logs + vite together
```

## Tech Stack

| Layer      | Technology                                      |
| ---------- | ----------------------------------------------- |
| Language   | PHP 8.2+                                        |
| Framework  | Laravel 12.x                                    |
| Frontend   | Blade, Tailwind CSS 3, Alpine.js                |
| Build tool | Vite 6                                          |
| Database   | MySQL (SQLite supported for local development)  |
| Auth       | Laravel Breeze                                  |
| PDF        | barryvdh/laravel-dompdf 3.1                     |
| PWA        | vite-plugin-pwa (installed, not yet configured) |
| Testing    | PHPUnit 11                                      |
| Dev runner | Concurrently (artisan serve + queue + vite)     |

## Roles

| Role    | URL prefix | Access                                                                               |
| ------- | ---------- | ------------------------------------------------------------------------------------ |
| Admin   | `/admin`   | Dashboard, settings, examinations, manage teachers/parents/classes/subjects/students |
| Teacher | `/teacher` | Mark entry (CA + exam scores) and attendance for assigned classes                    |
| Parent  | `/parent`  | Dashboard for all linked children                                                    |
| Student | `/student` | Dashboard with personal results and attendance                                       |
