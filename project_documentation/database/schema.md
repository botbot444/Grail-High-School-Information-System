# Database Schema

> Last updated: 2026-09-13
> Update this file when migrations are added or modified.

---

## Overview

33 migration files: Laravel `users` / `cache` / `jobs` plus domain tables and later alter/backfill migrations (fees, calendar, teacher_subjects, etc.).

---

## 4.1 `users`

| Column            | Type         | Notes                                        |
| ----------------- | ------------ | -------------------------------------------- |
| id                | bigint PK    |                                              |
| name              | string       |                                              |
| email             | string UQ    |                                              |
| email_verified_at | timestamp    | nullable                                     |
| password          | string       | hashed                                       |
| role              | string       | legacy: 'admin'/'teacher'/'parent'/'student' |
| is_active         | boolean      | default `true` — account enabled flag        |
| role_id           | unsigned int | FK → `roles.id` (added later)                |
| remember_token    | string       |                                              |
| timestamps        |              |                                              |

---

## 4.2 `roles`

| Column      | Type      | Notes                   |
| ----------- | --------- | ----------------------- |
| id          | bigint PK |                         |
| name        | string    | e.g. 'admin', 'teacher' |
| description | string    | nullable                |
| timestamps  |           |                         |

---

## 4.3 `school_classes`

| Column         | Type      | Notes                                         |
| -------------- | --------- | --------------------------------------------- |
| class_id       | bigint PK |                                               |
| class_name     | string    | e.g. "10A"                                    |
| grade_level    | string    | e.g. "Grade 10" (legacy text)                 |
| grade_level_id | bigint    | FK → `grade_levels.grade_level_id` (nullable) |
| teacher_id     | bigint    | FK → `teachers.teacher_id` (homeroom)         |
| deleted_at     | timestamp | soft delete                                   |
| timestamps     |           |                                               |

---

## 4.4 `subjects`

| Column       | Type      | Notes |
| ------------ | --------- | ----- |
| subject_id   | bigint PK |       |
| subject_name | string UQ |       |
| timestamps   |           |       |

---

## 4.5 `teachers`

| Column     | Type      | Notes           |
| ---------- | --------- | --------------- |
| teacher_id | bigint PK |                 |
| user_id    | bigint    | FK → `users.id` |
| first_name | string    |                 |
| last_name  | string    |                 |
| email      | string UQ |                 |
| phone      | string    | nullable        |
| deleted_at | timestamp | soft delete     |
| timestamps |           |                 |

---

## 4.6 `class_subjects` (pivot upgraded to first-class model)

| Column           | Type      | Notes                          |
| ---------------- | --------- | ------------------------------ |
| class_subject_id | bigint PK |                                |
| class_id         | bigint    | FK → `school_classes.class_id` |
| subject_id       | bigint    | FK → `subjects.subject_id`     |
| teacher_id       | bigint    | FK → `teachers.teacher_id`     |
| timestamps       |           |                                |

---

## 4.7 `students`

| Column         | Type      | Notes                                     |
| -------------- | --------- | ----------------------------------------- |
| student_id     | bigint PK |                                           |
| user_id        | bigint    | FK → `users.id` (nullable)                |
| parent_user_id | bigint    | FK → `users.id` (nullable)                |
| first_name     | string    |                                           |
| last_name      | string    |                                           |
| date_of_birth  | date      |                                           |
| gender         | enum      | 'Male'/'Female'                           |
| student_number | string UQ |                                           |
| class_id       | bigint    | FK → `school_classes.class_id` (nullable) |
| guardian_name  | string    | nullable                                  |
| guardian_phone | string    | nullable                                  |
| enrolment_date | date      | NOT NULL                                  |
| deleted_at     | timestamp | soft delete                               |
| timestamps     |           |                                           |

---

## 4.8 `attendances`

| Column           | Type      | Notes                                  |
| ---------------- | --------- | -------------------------------------- |
| attendance_id    | bigint PK |                                        |
| student_id       | bigint    | FK → `students.student_id`             |
| class_subject_id | bigint    | FK → `class_subjects.class_subject_id` |
| date             | date      |                                        |
| status           | enum      | 'Present'/'Absent'/'Late'              |
| recorded_by      | bigint    | FK → `teachers.teacher_id`             |
| timestamps       |           |                                        |

---

## 4.9 `grades`

| Column           | Type      | Notes                                    |
| ---------------- | --------- | ---------------------------------------- |
| grade_id         | bigint PK |                                          |
| student_id       | bigint    | FK → `students.student_id`               |
| class_subject_id | bigint    | FK → `class_subjects.class_subject_id`   |
| assessment_type  | enum      | 'CA' or 'EXAM'                           |
| score            | decimal   |                                          |
| max_score        | decimal   | default 100.00                           |
| term             | string    | legacy label                             |
| term_id          | bigint    | FK → `terms.term_id` (nullable)          |
| academic_year    | integer   | legacy year number                       |
| academic_year_id | bigint    | FK → `academic_years.year_id` (nullable) |
| recorded_by      | bigint    | FK → `teachers.teacher_id`               |
| timestamps       |           |                                          |

---

## 4.10 `fees`

| Column           | Type      | Notes                                                |
| ---------------- | --------- | ---------------------------------------------------- |
| fee_id           | bigint PK |                                                      |
| student_id       | bigint    | FK → `students.student_id`                           |
| description      | string    | nullable                                             |
| amount_due       | decimal   |                                                      |
| amount_paid      | decimal   | default 0                                            |
| balance          | decimal   | computed                                             |
| due_date         | date      |                                                      |
| status           | enum      | 'Pending' / 'Partially Paid' / 'Cleared' / 'Overdue' |
| term             | string    | legacy label                                         |
| term_id          | bigint    | FK → `terms.term_id` (nullable)                      |
| academic_year    | integer   | legacy year number                                   |
| academic_year_id | bigint    | FK → `academic_years.year_id` (nullable)             |
| last_updated     | datetime  | nullable                                             |
| timestamps       |           |                                                      |

---

## 4.11 `parents` (table), `ParentProfile` (model)

| Column      | Type      | Notes           |
| ----------- | --------- | --------------- |
| parent_id   | bigint PK |                 |
| user_id     | bigint    | FK → `users.id` |
| first_name  | string    |                 |
| last_name   | string    |                 |
| email       | string UQ |                 |
| phone       | string    | nullable        |
| address     | string    | nullable        |
| occupation  | string    | nullable        |
| national_id | string UQ | nullable        |
| deleted_at  | timestamp | soft delete     |
| timestamps  |           |                 |

---

## 4.12 `fee_items`

| Column      | Type      | Notes                         |
| ----------- | --------- | ----------------------------- |
| fee_item_id | bigint PK |                               |
| fee_id      | bigint    | FK → `fees.fee_id`            |
| item_name   | string    |                               |
| category    | string    | matches `fee_categories.slug` |
| amount      | decimal   |                               |
| timestamps  |           |                               |

---

## 4.13 `fee_categories`

| Column     | Type      | Notes       |
| ---------- | --------- | ----------- |
| id         | bigint PK |             |
| name       | string    |             |
| slug       | string UQ |             |
| sort_order | smallint  | default 0   |
| deleted_at | timestamp | soft delete |
| timestamps |           |             |

---

## 4.14 `payments`

| Column           | Type      | Notes                      |
| ---------------- | --------- | -------------------------- |
| payment_id       | bigint PK |                            |
| fee_id           | bigint    | FK → `fees.fee_id`         |
| amount           | decimal   |                            |
| payment_method   | string    | cash, bank_transfer, etc.  |
| reference_number | string    | nullable                   |
| notes            | text      | nullable                   |
| payment_date     | timestamp |                            |
| recorded_by      | bigint    | FK → `users.id` (nullable) |
| timestamps       |           |                            |

---

## 4.15 `audit_logs`

| Column         | Type      | Notes                       |
| -------------- | --------- | --------------------------- |
| id             | bigint PK |                             |
| user_id        | bigint    | FK → `users.id` (nullable)  |
| auditable_type | string    | morph                       |
| auditable_id   | bigint    | morph                       |
| action         | string    | created / updated / deleted |
| old_values     | json      | nullable                    |
| new_values     | json      | nullable                    |
| reason         | text      | nullable                    |
| ip_address     | string    | nullable                    |
| user_agent     | text      | nullable                    |
| created_at     | timestamp | no `updated_at` in practice |

---

## 4.16 `academic_years`

| Column     | Type      | Notes       |
| ---------- | --------- | ----------- |
| year_id    | bigint PK |             |
| label      | string UQ | e.g. "2026" |
| start_date | date      |             |
| end_date   | date      |             |
| is_current | boolean   |             |
| timestamps |           |             |

---

## 4.17 `terms`

| Column           | Type      | Notes                         |
| ---------------- | --------- | ----------------------------- |
| term_id          | bigint PK |                               |
| academic_year_id | bigint    | FK → `academic_years.year_id` |
| name             | string    | e.g. "Term 1"                 |
| start_date       | date      |                               |
| end_date         | date      |                               |
| is_current       | boolean   |                               |
| timestamps       |           |                               |

---

## 4.18 `holidays`

| Column           | Type      | Notes                         |
| ---------------- | --------- | ----------------------------- |
| holiday_id       | bigint PK |                               |
| academic_year_id | bigint    | FK → `academic_years.year_id` |
| date             | date      |                               |
| description      | string    |                               |
| timestamps       |           |                               |

---

## 4.19 `grade_levels`

| Column         | Type      | Notes           |
| -------------- | --------- | --------------- |
| grade_level_id | bigint PK |                 |
| name           | string UQ | e.g. "Grade 10" |
| order          | integer   | sort key        |
| timestamps     |           |                 |

---

## 4.20 `teacher_subjects`

Independent teacher-to-subject assignments (not the same as `class_subjects`).

| Column     | Type      | Notes                               |
| ---------- | --------- | ----------------------------------- |
| id         | bigint PK |                                     |
| teacher_id | bigint    | FK → `teachers.teacher_id`          |
| subject_id | bigint    | FK → `subjects.subject_id`          |
| timestamps |           | unique (`teacher_id`, `subject_id`) |

---

## 4.21 `periods`

| Column         | Type      | Notes                                  |
| -------------- | --------- | -------------------------------------- |
| id             | bigint PK |                                        |
| grade_level_id | bigint    | FK -> `grade_levels.grade_level_id`    |
| name           | string    |                                        |
| start_time     | time      |                                        |
| end_time       | time      |                                        |
| order          | integer   | unique within a grade level            |
| is_break       | boolean   | break cells cannot receive assignments |

## 4.22 `timetable_slots`

| Column          | Type      | Notes                                       |
| --------------- | --------- | ------------------------------------------- |
| id              | bigint PK |                                             |
| school_class_id | bigint    | FK -> `school_classes.class_id`, restricted |
| subject_id      | bigint    | nullable FK -> `subjects.subject_id`        |
| teacher_id      | bigint    | nullable FK -> `teachers.teacher_id`        |
| period_id       | bigint    | FK -> `periods.id`, restricted              |
| day_of_week     | enum      | Monday through Friday                       |
| term_id         | bigint    | FK -> `terms.term_id`, restricted           |

Unique key: `school_class_id + day_of_week + period_id + term_id`.

_End of database schema documentation._
