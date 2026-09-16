# Models

> Last updated: 2026-09-16
> Update this file when models are added or modified.

---

All located in `app/Models/`. **34** models in total. Custom primary keys are used on domain tables (e.g. `student_id`, `teacher_id`) instead of `id`. Several models also use the `Auditable` trait.

**Legacy sections (5.1–5.19)** cover User, Role, SchoolClass, Subject, Teacher, ClassSubject, Student, Grade, Fee, ParentProfile, AcademicYear, Term, Holiday, GradeLevel, Payment, FeeItem, FeeCategory, AuditLog.

**Newer sections (5.20–5.34)** cover the Phase 5/6/9/11/12 additions. Two attributes were also added to existing models: `users.must_change_password` + `users.is_active`, and `students.status` + `students.graduated_on` + `students.credit_balance`.

---

## 5.1 `User` (`app/Models/User.php`)

- **Fillable**: `name, email, password, role, role_id, email_verified_at, must_change_password, is_active`
- **Default attributes**: `is_active => true` (set in-memory so a freshly `create()`d user is active before any re-fetch)
- **Casts**: `email_verified_at → datetime`, `must_change_password → boolean`, `is_active → boolean`
    - **No `password → hashed` cast** — every write site calls `Hash::make()` itself; adding the cast double-hashes.
- **Hidden**: `password`, `remember_token`; **`$auditExclude`**: `email_verified_at`, `updated_at`, `created_at`
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
- **Fillable**: `user_id, parent_user_id, first_name, last_name, date_of_birth, gender, student_number, class_id, guardian_name, guardian_phone, enrolment_date, status, graduated_on, credit_balance`
- **Casts**: `date_of_birth` / `enrolment_date` / `graduated_on` → `date:Y-m-d`, `credit_balance` → decimal:2
- **Constants**: `STATUS_ENROLLED` (`Enrolled`), `STATUS_GRADUATED`, `STATUS_TRANSFERRED`, `STATUS_WITHDRAWN` (+ `STATUSES` label map)
- **Soft deletes** + **Auditable**
- **Relationships**:
    - `user()`, `parentUser()`, `guardian()` (alias of parentUser, for notifications)
    - `schoolClass()`, `grades()`, `attendance()`, `fees()`
    - `assignmentSubmissions()`, `reportCards()`, `promotions()`, `feeCredits()`
- **Scopes**: `scopeInClass(int $classId)`, `scopeEnrolled()`, `scopeGraduated()`
- **Fee-credit ledger**: `grantCredit($amount, $attrs)` (overpayment → `credit_balance` + ledger row, then auto-applies), `applyAvailableCredit($recordedBy)` (pays oldest-due fees first, each as a real `Payment` with method `credit`), `refundCredit($amount, $notes, $recordedBy)` (manual, logged exception for leaving students)
- **Student numbers**: `nextStudentNumber()` → `"2026/0017"`; `createWithGeneratedNumber($attributes)` retries on a rare collision
- **Accessor**: `full_name`; **Helpers**: `isEnrolled()`, `averageExamPercentage()`, `averageExamLetterGrade()`, `attendanceRate()`
- **Note**: `class_id` is nullable — a newly admitted student is unplaced until an admin assigns a class.

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
- **Casts**: `amount_due` / `amount_paid` / `balance` → decimal:2, `due_date` → `date:Y-m-d`, `last_updated` → datetime, `academic_year` → integer
- **Relationships**: `student()`, `feeItems()`, `payments()`, `academicYear()`, `term()`
- **State machine (FR-11)** — status is computed, never set manually:
    - `recordPayment(float $amount)` — adds to `amount_paid`, recalculates `balance`, sets status via `computeStatus()`
    - `reversePayment(float $amount)` — admin error correction
    - `recalculateAmountDue()` — totals from `fee_items`; runs on `saving` when items are loaded
    - `computeStatus()` → `Cleared` (balance ≤ 0) / `Partially Paid` (paid > 0) / `Overdue` (past due, unpaid) / `Pending`
    - `updateStatus()` — recompute **and save**, so `Auditable` captures the change
    - `applyPayment($amount, $method, $reference, $notes, $date, $recordedBy)` — creates the `Payment` row, applies what fits this fee, and carries the overage to `Student::grantCredit()`. Shared by the bursar's direct entry and admin-approved parent proof-of-payment.
- **Scopes**: `scopePending()`, `scopePartiallyPaid()`, `scopeCleared()`, `scopeOverdue()`, `scopeForTerm()`
- **Payment reference**: a deterministic, checksummed per-fee code (e.g. `GRL-0042-0117-K`) using a typo-resistant alphabet, so a parent can quote one reference across instalments.
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

## 5.20 `Period` (Phase 3)

- **Fillable**: `grade_level_id, name, start_time, end_time, order, is_break`
- **Casts**: `start_time` / `end_time` → `datetime:H:i`, `order` → integer, `is_break` → boolean
- **Relationships**: `gradeLevel()`, `timetableSlots()`
- **Validation (saving hook)**: `end_time` must be after `start_time`; a period may not overlap another period in the same grade level (throws `ValidationException`).

## 5.21 `TimetableSlot` (Phase 3)

- **Fillable**: `school_class_id, subject_id, teacher_id, period_id, day_of_week, term_id`
- **Auditable**
- **Relationships**: `schoolClass()`, `subject()`, `teacher()`, `period()`, `term()`
- **Accessor**: `display_name` → `"10A · Monday · Mathematics"` (falls back to the period name for breaks)
- **Validation (saving hook)** — three guards, all throwing `ValidationException`:
    1. The period must belong to the same grade level as the class.
    2. A break period cannot have a subject or teacher.
    3. The teacher cannot be double-booked for the same term/day/period.

## 5.22 `SchoolSetting` (Phase 12)

- **Table**: key/value store. **Fillable**: `key, value`
- **Auditable**
- **Caching**: reads go through a single `Cache::rememberForever('school_settings.all')` map, cleared on `saved`/`deleted`.
- **Statics**: `all_settings()`, `get($key, $default)`, `set($key, $value)`, `hasPaymentDetails()`
- **`PAYMENT_KEYS`** const: bank name / account name / account number / branch, MTN + Airtel mobile-money numbers, and a parent-facing note — the labels the admin payment-instructions form renders.

## 5.23 `Announcement` (Phase 5)

- **PK**: `announcement_id`. **Soft deletes** + **Auditable**
- **Fillable**: `title, body, audience, published_at, expires_at, created_by`
- **Constants**: `AUDIENCE_ALL`, `AUDIENCE_CLASS`, `AUDIENCE_GRADE_LEVEL` (+ `AUDIENCES` label map)
- **Relationships**: `targets()` (AnnouncementTarget), `reads()` (AnnouncementRead), `author()` (User)
- **Scopes**:
    - `scopeLive()` — published in the past and not expired
    - `scopeVisibleTo(User)` — school-wide notices **OR** class targets matching the user's class (student) / children's classes (parent) **OR** matching grade levels
- **Statics**: `classIdsFor(?User)` — a student's own class, or every class a parent's children sit in; admins/teachers get none
- **Accessors**: `state` (Draft / Scheduled / Live / Expired), `audience_label`, `target_summary` (`"10A, 10B"`, `"Grade 10"`, `"Whole school"`)
- **Helpers**: `isPublished()`, `isExpired()`, `isReadBy(?User)`

## 5.24 `AnnouncementTarget` (Phase 5)

- **Fillable**: `announcement_id, targetable_type, targetable_id`
- **Relationships**: `announcement()`, morph `targetable()`
- **Helper**: `displayName()` → the class name or grade-level name for the target

## 5.25 `AnnouncementRead` (Phase 5)

- **Fillable**: `announcement_id, user_id, read_at`; **Casts**: `read_at` → datetime
- **Relationships**: `announcement()`, `user()`
- No row means unread; `AnnouncementService::markRead()` is idempotent via `firstOrCreate`.

## 5.26 `Assignment` (Phase 12 / teacher portal)

- **PK**: `assignment_id`. **Soft deletes** + **Auditable**
- **Fillable**: `class_subject_id, term_id, title, instructions, status, published_at, due_at, max_score, allows_file_upload, created_by`
- **Casts**: `published_at` / `due_at` → datetime, `max_score` → decimal:2, `allows_file_upload` → boolean
- **Constants**: `STATUS_DRAFT`, `STATUS_PUBLISHED`
- **Relationships**: `classSubject()`, `term()`, `createdByTeacher()`, `submissions()`
- **Scopes**: `scopePublished()`, `scopeForClass($classId)`, `scopeForTeacher($teacherId)`, `scopeOverdue()`
- **Helpers**: `isPublished()`, `isPastDue()`, `statusForStudent(?AssignmentSubmission)` → `Graded` / `Submitted` / `Overdue` / `Pending`

## 5.27 `AssignmentSubmission` (Phase 12 / teacher portal)

- **PK**: `submission_id`. **Auditable**
- **Fillable**: `assignment_id, student_id, notes, file_path, original_filename, submitted_at, score, feedback, graded_by, graded_at`
- **Relationships**: `assignment()`, `student()`, `gradedByTeacher()`
- **Helpers**: `isLate()` (submitted after `due_at`); `percentage` accessor (score ÷ assignment `max_score`)
- One submission per student per assignment — the student `submit()` flow updates the existing row and replaces its file.

## 5.28 `ReportCard` (Phase 11)

- **PK**: `report_card_id`. **Auditable**
- **Fillable**: `student_id, term_id, class_id, term_average, class_rank, class_size, class_teacher_comment, finalized_at, finalized_by, audit_reason`
- **Relationships**: `student()`, `term()`, `schoolClass()`, `finalizedByTeacher()`
- **Scopes**: `scopeFinalized()` (only finalized cards are visible to parents/students), `scopeForTerm($termId)`
- **Helpers**: `isFinalized()`, `rank_label` accessor → `"3rd of 28"`
- `unfinalize()` clears rank/size/finalization but keeps comments; `audit_reason` records the admin's stated reason.

## 5.29 `ReportCardComment` (Phase 11)

- **PK**: `comment_id`. **Auditable**
- **Fillable**: `student_id, term_id, class_subject_id, comment, teacher_id`
- **Relationships**: `student()`, `term()`, `classSubject()`, `teacher()`
- Holds the **per-subject** remark written by the subject teacher (the class teacher's overall comment lives on `report_cards.class_teacher_comment`).

## 5.30 `PromotionMapping` (Phase 6)

- **Fillable**: `from_class_id, to_class_id, graduates`; **Casts**: `graduates` → boolean; **Auditable**
- **Relationships**: `fromClass()`, `toClass()`
- Where a class's students go at year-end (10A → 11A), or out of the school when `graduates` is true.

## 5.31 `StudentPromotion` (Phase 6)

- **Fillable**: `batch_ref, student_id, from_class_id, to_class_id, outcome, previous_status, academic_year_id, promoted_by, rolled_back_at`
- **Constants**: `PROMOTED`, `RETAINED`, `GRADUATED`
- **Relationships**: `student()`, `fromClass()`, `toClass()`, `academicYear()`, `promotedBy()`
- **Scope**: `scopeActive()` — rows not rolled back
- One row per student per promotion run; `batch_ref` groups a run and is what `rollback()` reverses.

## 5.32 `RegistrationRequest` (Phase 12)

- **PK**: `registration_request_id`. **Auditable**
- **Fillable**: parent (`parent_first_name, parent_last_name, parent_email, parent_password, parent_phone, parent_address, parent_occupation, parent_national_id`) + child (`child_first_name, child_last_name, child_date_of_birth, child_gender, child_email`) + review (`status, reviewed_by, reviewed_at, review_notes`) + created records (`created_parent_user_id, created_student_id`)
- **Hidden**: `parent_password`
- **Constants**: `STATUS_PENDING`, `STATUS_APPROVED`, `STATUS_REJECTED` (+ matching `pending()`/`approved()`/`rejected()` scopes)
- **Relationships**: `reviewedBy()`, `createdParentUser()`, `createdStudent()`
- **Accessors**: `parent_full_name`, `child_full_name`, `status_label`
- Nothing real (no `users` / `students` row) exists until an admin approves.

## 5.33 `PaymentSubmission` (Phase 12)

- **PK**: `submission_id`. **Auditable**
- **Fillable**: `fee_id, amount, payment_method, reference_number, payment_date, proof_path, proof_original_filename, notes, status, submitted_by, reviewed_by, reviewed_at, review_notes, payment_id`
- **Casts**: `amount` → decimal:2, dates → datetime
- **Constants**: `STATUS_PENDING`, `STATUS_APPROVED`, `STATUS_REJECTED` (+ matching scopes)
- **Relationships**: `fee()`, `submittedBy()`, `reviewedBy()`, `payment()`
- **Accessors**: `method_label`, `status_label`, `proof_url` (uses `asset()` so links survive a mismatched `APP_URL`), `proof_is_image`
- A parent's *claim* of an outside payment. It never touches the fee balance; approval creates a real `Payment` via `Fee::applyPayment()`.

## 5.34 `FeeCredit` (Phase 12 / overpayment ledger)

- **PK**: `credit_id`. **Auditable**
- **Fillable**: `student_id, amount, type, source_payment_id, applied_fee_id, notes, recorded_by`
- **Casts**: `amount` → decimal:2
- **Relationships**: `student()`, `sourcePayment()`, `appliedFee()`, `recordedBy()`
- Append-only ledger: `overpayment` (positive), `applied` (negative), `refunded` (negative). Written by `Student::grantCredit()`, `Student::applyAvailableCredit()` and `Student::refundCredit()`.

---

_End of models documentation._
