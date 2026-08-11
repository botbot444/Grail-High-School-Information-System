# Database Schema

> Last updated: 2026-08-09
> Update this file when migrations are added or modified.

---

## Overview

14 migrations create 11 application tables (plus Laravel's standard `users`, `cache`, `jobs`).

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

| Column      | Type      | Notes                                 |
| ----------- | --------- | ------------------------------------- |
| class_id    | bigint PK |                                       |
| class_name  | string    | e.g. "10A"                            |
| grade_level | string    | e.g. "Grade 10"                       |
| teacher_id  | bigint    | FK → `teachers.teacher_id` (homeroom) |
| deleted_at  | timestamp | soft delete                           |
| timestamps  |           |                                       |

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

| Column           | Type      | Notes                                  |
| ---------------- | --------- | -------------------------------------- |
| grade_id         | bigint PK |                                        |
| student_id       | bigint    | FK → `students.student_id`             |
| class_subject_id | bigint    | FK → `class_subjects.class_subject_id` |
| assessment_type  | enum      | 'CA' or 'EXAM'                         |
| score            | decimal   |                                        |
| max_score        | decimal   | default 100.00                         |
| term             | string    |                                        |
| academic_year    | integer   |                                        |
| recorded_by      | bigint    | FK → `teachers.teacher_id`             |
| timestamps       |           |                                        |

---

## 4.10 `fees`

| Column        | Type      | Notes                                    |
| ------------- | --------- | ---------------------------------------- |
| fee_id        | bigint PK |                                          |
| student_id    | bigint    | FK → `students.student_id`               |
| description   | string    | nullable                                 |
| amount_due    | decimal   |                                          |
| amount_paid   | decimal   | default 0                                |
| balance       | decimal   | computed                                 |
| due_date      | date      |                                          |
| status        | enum      | 'Pending' / 'Partially Paid' / 'Cleared' |
| term          | string    |                                          |
| academic_year | integer   |                                          |
| last_updated  | datetime  | nullable                                 |
| timestamps    |           |                                          |

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

_End of database schema documentation._
