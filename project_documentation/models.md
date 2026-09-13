# Models

> Last updated: 2026-09-13
> Update this file when models are added or modified.

---

All located in `app/Models/`. Custom primary keys are used on domain tables (e.g. `student_id`, `teacher_id`) instead of `id`. Several models also use the `Auditable` trait.

---

## 5.1 `User` (`app/Models/User.php`)

- **Fillable**: `name, email, password, role, role_id, email_verified_at`
- **Casts**: `email_verified_at → datetime`, `password → hashed`
- **Relationships**:
    - `roleModel()` — BelongsTo Role
    - `teacher()` — HasOne Teacher
    - `parent()` / `parentProfile()` — HasOne ParentProfile
    - `children()` — HasMany Student via `parent_user_id`
    - `assignments()` — HasManyThrough ClassSubject via Teacher
- **Accessors**:
    - `role_name` — reads from related Role → falls back to `role` string column
- **Helpers**: `hasRole()`, `isAdmin()`, `isTeacher()`, `isParent()`, `isStudent()`

---

## 5.2 `Role` (`app/Models/Role.php`)

- **Fillable**: `name, description`
- **Relationships**:
    - `users()` — HasMany
- **Seeded with**: `admin`, `teacher`, `parent`, `student`

---

## 5.3 `SchoolClass` (`app/Models/SchoolClass.php`)

- **PK**: `class_id`
- **Fillable**: `class_name, grade_level, teacher_id`
- **Soft deletes**: enabled
- **Relationships**:
    - `teacher()` — homeroom teacher
    - `students()` — HasMany
    - `subjects()` — BelongsToMany via `class_subjects` with pivot `teacher_id`, `class_subject_id`
    - `classSubjects()` — HasMany pivot
    - `gradeLevel()` — BelongsTo GradeLevel (`grade_level_id`, nullable)
- **Accessors**: `display_name` → "10A – Grade 10"; `grade_level_name` prefers the GradeLevel relation then the legacy `grade_level` string

---

## 5.4 `Subject` (`app/Models/Subject.php`)

- **PK**: `subject_id`
- **Fillable**: `subject_name`
- **Relationships**:
    - `classes()` — BelongsToMany
    - `classSubjects()` — HasMany
    - `teachers()` — BelongsToMany via `teacher_subjects`

---

## 5.5 `Teacher` (`app/Models/Teacher.php`)

- **PK**: `teacher_id`
- **Fillable**: `user_id, first_name, last_name, email, phone`
- **Soft deletes**: enabled
- **Relationships**:
    - `user()`
    - `homeroomClasses()`
    - `classSubjects()`
    - `subjects()` — BelongsToMany via `teacher_subjects`, independent of homeroom classes
    - `recordedAttendance()`
    - `recordedGrades()`
- **Accessor**: `full_name`

### `teacher_subjects`

- Stores independent teacher-to-subject assignments used by the admin teacher create/edit workflows.
- Homeroom assignments remain stored in `school_classes.teacher_id`.
- Class-specific teaching and mark-entry assignments remain stored in `class_subjects`.

---

## 5.6 `ClassSubject` (`app/Models/ClassSubject.php`)

- **PK**: `class_subject_id`
- **Fillable**: `class_id, subject_id, teacher_id`
- **Note**: First-class model (not just a pivot) — Grades and Attendance both FK into it
- **Relationships**:
    - `schoolClass()`
    - `subject()`
    - `teacher()`
    - `grades()`
    - `attendanceRecords()`
- **Accessor**: `display_name` → "10A – Mathematics"

---

## 5.7 `Student` (`app/Models/Student.php`)

- **PK**: `student_id`
- **Fillable**: `user_id, parent_user_id, first_name, last_name, date_of_birth, gender, student_number, class_id, guardian_name, guardian_phone, enrolment_date`
- **Casts**: `date_of_birth`, `enrolment_date` → date
- **Soft deletes**: enabled
- **Relationships**:
    - `user()`
    - `parentUser()`
    - `schoolClass()`
    - `grades()`
    - `attendance()`
    - `fees()`
- **Scopes**: `scopeInClass(int $classId)`
- **Accessor**: `full_name`

---

## 5.8 `Attendance` (`app/Models/Attendance.php`)

- **PK**: `attendance_id`
- **Fillable**: `student_id, class_subject_id, date, status, recorded_by`
- **Casts**: `date` → date
- **Relationships**:
    - `student()`
    - `classSubject()`
    - `recordedByTeacher()`
- **Scopes**: `scopeForStudent()`, `scopeForClass()`, `scopeForDateRange()`, `scopePresent()`, `scopeAbsent()`, `scopeLate()`

---

## 5.9 `Grade` (`app/Models/Grade.php`)

- **PK**: `grade_id`
- **Fillable**: `student_id, class_subject_id, assessment_type, score, max_score, term, academic_year, academic_year_id, term_id, recorded_by, marks`
- **Casts**: `score/max_score` → decimal(2), `academic_year` → integer
- **Relationships**:
    - `student()`
    - `classSubject()`
    - `recordedByTeacher()`
    - calendar FKs: `academic_year_id`, `term_id` (legacy `term` string + `academic_year` int kept)
- **Business logic**:
    - `validateScore()` — score between 0 and max
    - `percentage` accessor
    - `letter_grade` accessor (A+, A, B+, B, C+, C, D, F)
    - `remark` accessor (Excellent/Good/Satisfactory/Pass/Fail)
- **Scopes**: `scopeForTerm()`, `scopeForStudent()`, `scopeExams()`, `scopeCa()`
- **Accessors/Mutators**: `marks` (maps to/from `score` for backward compatibility)

---

## 5.10 `Fee` (`app/Models/Fee.php`)

- **PK**: `fee_id`
- **Fillable**: `student_id, description, amount_due, amount_paid, balance, due_date, status, term, academic_year, academic_year_id, term_id, last_updated`
- **Casts**: money → decimal(2), dates
- **Relationships**: `student()`, `feeItems()`, `payments()`, `academicYear()`, `term()`
- **State machine (FR-11)** — status is computed, never set manually:
    - `recordPayment(float $amount)` — adds to `amount_paid`, recalculates `balance`, sets status via `computeStatus()`
    - `reversePayment(float $amount)` — admin error correction
    - `recalculateAmountDue()` — totals from `fee_items`
    - `computeStatus()` → `Pending` / `Partially Paid` / `Cleared` (enum also allows `Overdue`)
- **Scopes**: `scopePending()`, `scopePartiallyPaid()`, `scopeCleared()`, `scopeOverdue()`, `scopeForTerm()`
- **Accessors**: `is_overdue`, `payment_progress`

---

## 5.11 `ParentProfile` (`app/Models/ParentProfile.php`)

- **Table**: `parents` (named `ParentProfile` to avoid PHP reserved word)
- **PK**: `parent_id`
- **Fillable**: `user_id, first_name, last_name, email, phone, address, occupation, national_id`
- **Soft deletes**: enabled
- **Relationships**:
    - `user()`
    - `students()` — HasMany via `students.parent_user_id` = `user_id`

---

## 5.12 `AcademicYear`

- **PK**: `year_id`
- **Fillable**: `label, start_date, end_date, is_current`
- **Relationships**: `terms()`, `holidays()`, `grades()`, `fees()`
- **Helpers**: `scopeCurrent()`, `current()`, `setAsCurrent()`

## 5.13 `Term`

- **PK**: `term_id`
- **Fillable**: `academic_year_id, name, start_date, end_date, is_current`
- **Relationships**: `academicYear()`, `grades()`, `fees()`
- **Helpers**: `scopeCurrent()`, `current()` (date window containing today), `school_days` accessor (weekdays minus holidays)

## 5.14 `Holiday`

- **PK**: `holiday_id`
- **Fillable**: `academic_year_id, date, description`
- **Relationships**: `academicYear()`

## 5.15 `GradeLevel`

- **PK**: `grade_level_id`
- **Fillable**: `name, order`
- **Relationships**: `classes()`, `students()` (HasManyThrough)

## 5.16 `Payment`

- **PK**: `payment_id`
- **Fillable**: `fee_id, amount, payment_method, reference_number, notes, payment_date, recorded_by`
- **Relationships**: `fee()`, `recordedBy()`
- **Auditable**

## 5.17 `FeeItem`

- **PK**: `fee_item_id`
- **Fillable**: `fee_id, item_name, category, amount`
- **Relationships**: `fee()`
- **Auditable**

## 5.18 `FeeCategory`

- **Fillable**: `name, slug, sort_order`
- **Soft deletes**: enabled
- **Relationships**: `feeItems()` via `category` slug
- **Scope**: `scopeInUse()`

## 5.19 `AuditLog`

- **Fillable**: `user_id, auditable_type, auditable_id, action, old_values, new_values, reason, ip_address, user_agent`
- **Casts**: `old_values` / `new_values` → array
- **Timestamps**: `created_at` only
- **Relationships**: `user()`, morph `auditable()`

---

_End of models documentation._
