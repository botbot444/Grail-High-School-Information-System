# Database Schema

> Last updated: 2026-09-16
> Update this file when migrations are added or modified.

---

## Overview

**56** migration files: Laravel `users` / `cache` / `jobs` plus domain tables and later alter/backfill migrations
(fees, calendar, teacher_subjects, periods/timetables, assignments, report cards, school settings, announcements,
student promotions, account flags, fee credits, payment submissions, registration requests, notifications).

Section **4.1–4.22** preceded the 2026-09-13+ work; **4.23–4.35** cover the newer tables.

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
| must_change_password | boolean   | default `false` — locks the account to its settings page |
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
| class_id       | bigint    | FK → `school_classes.class_id` (nullable — unplaced until assigned) |
| guardian_name  | string    | nullable                                  |
| guardian_phone | string    | nullable                                  |
| enrolment_date | date      | NOT NULL                                  |
| status         | string    | `Enrolled` (default) / `Graduated` / `Transferred` / `Withdrawn` |
| graduated_on   | date      | nullable — set on graduation              |
| credit_balance | decimal   | default 0 — unapplied overpayment carried forward |
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

---

## 4.23 `assignments`

| Column              | Type      | Notes                                          |
| ------------------- | --------- | ---------------------------------------------- |
| assignment_id       | bigint PK |                                                |
| class_subject_id    | bigint    | FK → `class_subjects.class_subject_id`         |
| term_id             | bigint    | nullable FK → `terms.term_id`                  |
| title               | string    |                                                |
| instructions        | text      | nullable                                       |
| status              | string    | `Draft` / `Published`                          |
| published_at        | timestamp | nullable                                       |
| due_at              | timestamp |                                                |
| max_score           | decimal   |                                                |
| allows_file_upload  | boolean   |                                                |
| created_by          | bigint    | FK → `teachers.teacher_id`                     |
| deleted_at          | timestamp | soft delete                                    |

## 4.24 `assignment_submissions`

| Column            | Type      | Notes                                     |
| ----------------- | --------- | ----------------------------------------- |
| submission_id     | bigint PK |                                           |
| assignment_id     | bigint    | FK → `assignments.assignment_id`          |
| student_id        | bigint    | FK → `students.student_id`                |
| notes             | text      | nullable                                  |
| file_path         | string    | nullable                                  |
| original_filename | string    | nullable                                  |
| submitted_at      | timestamp | nullable                                  |
| score             | decimal   | nullable                                  |
| feedback          | text      | nullable                                  |
| graded_by         | bigint    | nullable FK → `teachers.teacher_id`       |
| graded_at         | timestamp | nullable                                  |

Unique key: `assignment_id + student_id` (one submission per student per assignment).

## 4.25 `report_cards`

| Column                 | Type      | Notes                                    |
| ---------------------- | --------- | ---------------------------------------- |
| report_card_id         | bigint PK |                                          |
| student_id             | bigint    | FK → `students.student_id`               |
| term_id                | bigint    | FK → `terms.term_id`                     |
| class_id               | bigint    | FK → `school_classes.class_id`           |
| term_average           | decimal   | nullable                                 |
| class_rank             | integer   | nullable — competition ranking           |
| class_size             | integer   | nullable                                 |
| class_teacher_comment  | text      | nullable — the homeroom teacher's remark |
| finalized_at           | timestamp | nullable — null means still a draft      |
| finalized_by           | bigint    | nullable FK → `teachers.teacher_id`      |
| audit_reason           | string    | nullable — added by `2026_09_13_000040`  |

## 4.26 `report_card_comments`

| Column           | Type      | Notes                                        |
| ---------------- | --------- | -------------------------------------------- |
| comment_id       | bigint PK |                                              |
| student_id       | bigint    | FK → `students.student_id`                   |
| term_id          | bigint    | FK → `terms.term_id`                         |
| class_subject_id | bigint    | FK → `class_subjects.class_subject_id`       |
| comment          | text      | per-subject remark                           |
| teacher_id       | bigint    | FK → `teachers.teacher_id`                   |

## 4.27 `school_settings`

| Column | Type      | Notes                              |
| ------ | --------- | ---------------------------------- |
| id     | bigint PK |                                    |
| key    | string UQ | e.g. `payment_bank_account_number` |
| value  | text      | nullable                           |

Reads are cached as a single `school_settings.all` map.

## 4.28 `announcements`

| Column          | Type      | Notes                           |
| --------------- | --------- | ------------------------------- |
| announcement_id | bigint PK |                                 |
| title           | string    |                                 |
| body            | text      |                                 |
| audience        | string    | `all` / `class` / `grade_level` |
| published_at    | timestamp | nullable — null is a Draft      |
| expires_at      | timestamp | nullable                        |
| created_by      | bigint    | FK → `users.id`                 |
| deleted_at      | timestamp | soft delete                     |

## 4.29 `announcement_targets`

| Column          | Type      | Notes                                |
| --------------- | --------- | ------------------------------------ |
| id              | bigint PK |                                      |
| announcement_id | bigint    | FK → `announcements.announcement_id` |
| targetable_type | string    | `SchoolClass` or `GradeLevel`        |
| targetable_id   | bigint    |                                      |

## 4.30 `announcement_reads`

| Column          | Type      | Notes                                |
| --------------- | --------- | ------------------------------------ |
| id              | bigint PK |                                      |
| announcement_id | bigint    | FK → `announcements.announcement_id` |
| user_id         | bigint    | FK → `users.id`                      |
| read_at         | timestamp |                                      |

No row means unread. Unique on `announcement_id + user_id`.

## 4.31 `promotion_mappings`

| Column        | Type      | Notes                                   |
| ------------- | --------- | --------------------------------------- |
| id            | bigint PK |                                         |
| from_class_id | bigint    | FK → `school_classes.class_id`          |
| to_class_id   | bigint    | nullable FK → `school_classes.class_id` |
| graduates     | boolean   | true = this class leaves the school     |

## 4.32 `student_promotions`

| Column           | Type      | Notes                                   |
| ---------------- | --------- | --------------------------------------- |
| id               | bigint PK |                                         |
| batch_ref        | string    | groups one promotion run                |
| student_id       | bigint    | FK → `students.student_id`              |
| from_class_id    | bigint    | nullable                                |
| to_class_id      | bigint    | nullable                                |
| outcome          | string    | `promoted` / `retained` / `graduated`   |
| previous_status  | string    | student status before the run           |
| academic_year_id | bigint    | FK → `academic_years.year_id`           |
| promoted_by      | bigint    | FK → `users.id`                         |
| rolled_back_at   | timestamp | nullable — set when a batch is reversed |

## 4.33 `payment_submissions`

| Column                  | Type      | Notes                                     |
| ----------------------- | --------- | ----------------------------------------- |
| submission_id           | bigint PK |                                           |
| fee_id                  | bigint    | FK → `fees.fee_id`                        |
| amount                  | decimal   | claimed amount                            |
| payment_method          | string    | cash / bank_transfer / cheque / mobile_money / card |
| reference_number        | string    | nullable                                  |
| payment_date            | timestamp |                                           |
| proof_path              | string    | stored on the `public` disk               |
| proof_original_filename | string    | nullable                                  |
| notes                   | text      | nullable                                  |
| status                  | string    | `pending` / `approved` / `rejected`       |
| submitted_by            | bigint    | FK → `users.id`                           |
| reviewed_by             | bigint    | nullable FK → `users.id`                  |
| reviewed_at             | timestamp | nullable                                  |
| review_notes            | text      | nullable                                  |
| payment_id              | bigint    | nullable FK → `payments.payment_id` (set on approval) |

## 4.34 `registration_requests`

| Column                  | Type      | Notes                                 |
| ----------------------- | --------- | ------------------------------------- |
| registration_request_id | bigint PK |                                       |
| parent_first_name       | string    |                                       |
| parent_last_name        | string    |                                       |
| parent_email            | string    |                                       |
| parent_password         | string    | hashed at submission, never shown     |
| parent_phone            | string    | nullable                              |
| parent_address          | string    | nullable                              |
| parent_occupation       | string    | nullable                              |
| parent_national_id      | string    | nullable, unique against parents      |
| child_first_name        | string    |                                       |
| child_last_name         | string    |                                       |
| child_date_of_birth     | date      |                                       |
| child_gender            | string    |                                       |
| child_email             | string    |                                       |
| status                  | string    | `pending` / `approved` / `rejected`   |
| reviewed_by             | bigint    | nullable FK → `users.id`              |
| reviewed_at             | timestamp | nullable                              |
| review_notes            | text      | nullable                              |
| created_parent_user_id  | bigint    | nullable FK → `users.id` (on approval)|
| created_student_id      | bigint    | nullable FK → `students.student_id` (on approval) |

## 4.35 `fee_credits` (append-only overpayment ledger)

| Column            | Type      | Notes                                          |
| ----------------- | --------- | ---------------------------------------------- |
| credit_id         | bigint PK |                                                |
| student_id        | bigint    | FK → `students.student_id`                     |
| amount            | decimal   | positive for `overpayment`, negative otherwise |
| type              | string    | `overpayment` / `applied` / `refunded`         |
| source_payment_id | bigint    | nullable FK → `payments.payment_id`            |
| applied_fee_id    | bigint    | nullable FK → `fees.fee_id`                    |
| notes             | text      | nullable                                       |
| recorded_by       | bigint    | nullable FK → `users.id`                       |

Rows are never updated — each ledger event is its own row.

## 4.36 `notifications` (Laravel)

Standard Laravel database-notification table (`2026_09_15_140648`), used by the `database` channel of the
payment-submission / registration-review notifications.

---

_End of database schema documentation._
