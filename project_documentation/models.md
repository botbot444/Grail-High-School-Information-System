# Models

> Last updated: 2026-08-02
> Update this file when models are added or modified.

---

All located in `app/Models/`. Custom primary keys are used (e.g. `student_id`, `teacher_id`) instead of `id`.

---

## 5.1 `User` (`app/Models/User.php`)

- **Fillable**: `name, email, password, role, role_id, email_verified_at`
- **Casts**: `email_verified_at → datetime`, `password → hashed`
- **Relationships**:
    - `roleModel()` — BelongsTo Role
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
- **Accessor**: `display_name` → "10A – Grade 10"

---

## 5.4 `Subject` (`app/Models/Subject.php`)

- **PK**: `subject_id`
- **Fillable**: `subject_name`
- **Relationships**:
    - `classes()` — BelongsToMany
    - `classSubjects()` — HasMany

---

## 5.5 `Teacher` (`app/Models/Teacher.php`)

- **PK**: `teacher_id`
- **Fillable**: `user_id, first_name, last_name, email, phone`
- **Soft deletes**: enabled
- **Relationships**:
    - `user()`
    - `homeroomClasses()`
    - `classSubjects()`
    - `recordedAttendance()`
    - `recordedGrades()`
- **Accessor**: `full_name`

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
- **Fillable**: `student_id, class_subject_id, assessment_type, score, max_score, term, academic_year, recorded_by, marks`
- **Casts**: `score/max_score` → decimal(2), `academic_year` → integer
- **Relationships**:
    - `student()`
    - `classSubject()`
    - `recordedByTeacher()`
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
- **Fillable**: `student_id, description, amount_due, amount_paid, balance, due_date, status, term, academic_year, last_updated`
- **Casts**: money → decimal(2), dates
- **State machine (FR-11)** — status is computed, never set manually:
    - `recordPayment(float $amount)` — adds to `amount_paid`, recalculates `balance`, sets status via `computeStatus()`
    - `reversePayment(float $amount)` — admin error correction
    - `computeStatus()` → `Pending` / `Partially Paid` / `Cleared`
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

_End of models documentation._
