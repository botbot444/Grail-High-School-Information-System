# Business Logic

> Last updated: 2026-09-18
> Update this file when business logic changes.

---

## 12.1 Fee State Machine (`Fee` model)

```
Pending ──(recordPayment > 0)──> Partially Paid ──(balance ≤ 0)──> Cleared
   ▲                                  │                                │
   │                                  └──(reversePayment)──────────────┤
   │                                                                    │
   └──────────────(reversePayment to 0)─────────────────────────────────┘
```

- Status is **always** derived via `computeStatus()` — never set manually.
- `recordPayment()` validates `amount > 0` and updates `amount_paid`, `balance`, `status`, `last_updated`.
- `reversePayment()` validates `0 < amount ≤ amount_paid`.

Additional implementation notes (Phase 1 — 2026-08-05):

- An Eloquent scope `scopeCleared()` has been added to the `Fee` model for convenience (`Fee::cleared()` → `where('status', 'Cleared')`).
- Controllers and views now use this scope where appropriate; seeded data was re-run against the MySQL dev DB and the admin KPI shows cleared fees as expected.

---

## 12.2 Grade Letter Calculation

| Percentage | Letter | Remark       |
| ---------- | ------ | ------------ |
| ≥ 90       | A+     | Excellent    |
| ≥ 80       | A      | Excellent    |
| ≥ 75       | B+     | Good         |
| ≥ 70       | B      | Good         |
| ≥ 65       | C+     | Satisfactory |
| ≥ 60       | C      | Satisfactory |
| ≥ 50       | D      | Pass         |
| < 50       | F      | Fail         |

---

## 12.3 Role-Based Authorization

- Implemented via `CheckRole` middleware reading `User::hasRole($role)`.
- Four roles: `admin`, `teacher`, `parent`, `student`.
- Roles are stored in the `roles` table (FK `users.role_id`) **and** mirrored in the legacy `users.role` string column (fallback).

---

## 12.4 Teacher Marks Entry

- Teacher sees only their `ClassSubject` assignments.
- Single form captures both **marks (0–100)** and **attendance (P/A/L)** for every student in the selected class.
- `storeMarks` uses `updateOrCreate` keyed by `(student_id, class_subject_id[, date])`.
- Admin teacher create/edit workflows keep independent assignments separate:
    - `teacher_subjects` stores the subjects assigned to a teacher.
    - `school_classes.teacher_id` stores homeroom class assignments.
    - Creating or editing a teacher does not generate `ClassSubject` rows by combining those two selections.

The teacher **portal dashboard** (`TeacherController@dashboard`) and **My Classes** (`classes()`) aggregate roster size, pending exam marks, and attendance from the teacher’s `ClassSubject` rows plus homeroom classes. The dedicated timetable, attendance, performance, announcements, settings, assignments and report-cards URLs are now all real pages (`teacher.placeholder` remains only as a fallback for future destinations).

---

## 12.5 Parent portal

- Children are `Student` rows with `parent_user_id` = the logged-in user’s id.
- Selected child: query `child_id`, else `session('selected_child_id')`, else first child. `switchChild` only persists an owned `student_id`.
- `showFee` 404s unless the fee’s student is a linked child, then redirects to `parent.fees`.
- Attendance, performance, reports, assignments, and fees all scope to the selected child. Reports summarise grades/attendance per term (not PDF report cards). Assignments list recent scored grade rows (there is no pending-homework model).

---

## 12.6 Authentication & Authorization Flow

1. User visits `/login` (Breeze `AuthenticatedSessionController`).
2. On success → `/dashboard` → `DashboardController@index` dispatches to role-specific dashboard.
3. Subsequent requests pass through `auth` middleware; admin/teacher/parent/student routes additionally pass through `role:<role>` middleware.
4. Logout via Breeze `AuthenticatedSessionController@destroy`.

---

## 12.7 Fee Credits / Overpayment Carry-Forward (Phase 12)

- `Fee::applyPayment()` splits the amount: `applied = min(amount, balance)`, `overage = amount - applied`.
  The full amount is recorded as a `Payment`; only `applied` reduces the balance.
- Any `overage` goes to `Student::grantCredit()`, which adds to `students.credit_balance`, writes a
  `fee_credits` row (`type = overpayment`), then calls `applyAvailableCredit()`.
- `applyAvailableCredit()` pays the student's outstanding fees **oldest due date first**, each application
  recorded as a real `Payment` with method `credit` plus a negative `applied` ledger row. Safe to call any time —
  it is a no-op with no credit or nothing owed.
- `refundCredit()` is the manual, logged exception for a withdrawing/graduating student. It records the event only —
  the money itself is returned outside the system.
- `AdminController@refundCredit` exposes this as `POST /admin/students/{student}/refund-credit`.

---

## 12.8 Announcements (Phase 5)

- **Audience**: `all` (whole school), `class` (specific classes), or `grade_level` (whole grade levels).
  Targets are polymorphic `announcement_targets` rows pointing at `SchoolClass` or `GradeLevel`.
- **Visibility** (`Announcement::scopeVisibleTo`): school-wide notices, plus class targets matching a student's
  own class or **every class a parent's children sit in**, plus matching grade levels. Admins/teachers match no
  classes and therefore see school-wide notices only.
- **Live window** (`scopeLive`): published in the past and not expired. A null `published_at` is a Draft;
  a future one is Scheduled; a past `expires_at` is Expired.
- **Reach preview**: `AnnouncementService::reachSummary()`/`audienceFor()` work on **unsaved** input, so
  `admin.announcements.preview` can report how many students/parents a notice will reach before it is saved.
- **Read tracking**: `announcement_reads` — no row means unread. `markRead()` is idempotent via `firstOrCreate`;
  `markAllRead()` marks everything currently visible; `unreadCount()` feeds sidebar badges.
- Only admins author announcements — there is no teacher authoring route.

---

## 12.9 Student Promotion (Phase 6)

- Classes are **permanent rows**, not per-year instances (amended 2026-09-13). Promotion changes a student's
  `class_id`; the academic-year context is carried on the `student_promotions` record instead.
- **Blockers** (`PromotionService::blockers`): no target academic year, or no classes at all.
- **Defaults** (`defaultFor`): use the saved `PromotionMapping` for the class; failing that, promote to the next
  class whose grade-level `order` is higher; if nothing is above, the student graduates.
- **Outcomes**: `promoted`, `retained`, `graduated`. Running requires at least one decision, validates every outcome,
  and refuses to promote into a class that does not exist.
- **Graduation** keeps the student record but deactivates the login (`users.is_active = false`); rollback reactivates it.
- Every student's move is recorded with `batch_ref`, `previous_status` and the from/to classes, which is what makes
  `rollback(batch)` possible. `batch_ref` groups one run for the history screen.

---

## 12.10 Report Cards (Phase 11)

- `ReportCardService::build($student, $term)` is the **single** assembly point used by all four portals, so the same
  student's card cannot disagree between screens. `RendersReportCards` renders it as HTML or PDF.
- **Subject rows**: grades grouped by `class_subject_id`; the total is the mean percentage of whichever of CA/EXAM
  exist, so a CA-only subject still reports sensibly. Letter grades use the same thresholds as `Grade::letter_grade`.
- **Attendance**: summarised for the term alongside the academics.
- **Finalize** (class teacher only, for their homeroom class): writes `term_average`, `class_rank`, `class_size`,
  `finalized_at`, `finalized_by` and an `audit_reason`. Ranking is **competition ranking** — equal averages share a
  rank and the next distinct average skips ahead.
- **Lock**: once a class + term is finalized, `isLocked()` blocks further mark entry.
- **Unfinalize** (admin override, `admin.report-cards.unfinalize`): clears rank/size/finalization but **keeps comments**,
  and requires a reason (min 5 chars) that is written to the audit log.
- **Visibility**: only `finalized()` cards are exposed to parents and students; a term they have no finalized card for
  simply 404s.
- **Comments**: `report_card_comments` holds the per-subject remark (subject teacher); the class teacher's overall
  comment lives on `report_cards.class_teacher_comment`.

---

## 12.11 Assignments (teacher + student portals)

- A teacher may only create assignments for their own `class_subjects` — enforced by `Rule::in(...)` over the ids of
  `teachableClassSubjects()`, which stops posting onto someone else's class.
- Statuses: `Draft` (hidden from students) and `Published`. `published_at` is stamped on publish.
- **Student visibility**: published **AND** set for a subject taught to the student's own class; anything else 404s,
  so an id from another class leaks nothing.
- **Submission**: one row per student per assignment. The student must supply notes or a file; the file is replaced
  rather than accumulated. Once `graded_at` is set, the student can no longer change it.
- **Grading**: score is validated `0..assignment.max_score`; `graded_by`/`graded_at` are stamped. Late submissions are
  flagged via `isLate()` (submitted after `due_at`).
- Per-student state: `Graded` / `Submitted` / `Overdue` / `Pending` (`Assignment::statusForStudent`).

---

## 12.12 Payment Submissions (proof of payment)

- A parent submits a claim of an outside payment with proof uploaded to `payment-proofs/{fee_id}`. This is a
  **claim only** — it never touches the fee balance.
- `store()` returns 403 unless the fee belongs to one of the parent's own children.
- **Approval** (`abort_unless($submission->isPending(), 422)`) reuses `Fee::applyPayment()` — the exact ledger logic the
  bursar's direct entry uses — then links the created `payment_id` and notifies the guardian.
- **Rejection** requires a `review_notes` reason (max 500) and notifies the guardian.
- Already-reviewed submissions return 422, so a double-click cannot post twice.

---

## 12.13 Registration Requests (Phase 12)

- Public sign-up creates a **pending `RegistrationRequest`** — no `users` row, no `students` row, no working login.
  The parent's chosen password is hashed at submission and never regenerated or emailed.
- **Approval** (admin) creates the parent `User` + `ParentProfile` and the child `User` + `Student` in one transaction.
  The child gets a `GeneratesTemporaryPassword` one-time password, `must_change_password = true`, and is left
  **unplaced** (`class_id` null) for the admin to assign separately.
- A belt-and-suspenders re-check refuses an approval whose `parent_national_id` already exists on another parent.
- Validation rejects a `child_email` that is not unique (against `users` **and** other pending requests) or that equals the
  `parent_email` (`different:parent_email`) — the child's login needs its own address.
- **Rejection** notifies the raw email address via `Notification::route()` (no `User` was ever created, so an
  `AnonymousNotifiable` is used and the `database` channel is skipped).
- Already-reviewed requests return 422.

---

## 12.14 Account Management (Phase 12)

- `UserAccountController` guards every action: an admin cannot act on **their own** account; the **last active admin**
  cannot be deactivated or demoted; a role change **warns** (but does not block) about records still attached to the
  old role (homeroom classes, subject assignments, linked children, a student record).
- `resetPassword()` issues a `GeneratesTemporaryPassword` value **shown once** in the flash message for the admin to
  pass on in person, sets `must_change_password = true`, and forces the user to their settings page on next login.
- Deactivation takes effect immediately via `EnsureAccountIsActive`; reactivation is the same toggle.

---

## 12.15 Reporting Cache (Phase 9)

- `AnalyticsService` caches report payloads under keys carrying a **version number** (`reports.v{N}.{name}.{hash}`).
  `flush()` bumps `reports.version`, invalidating every cached report with one cache write — this works on every cache
  driver, unlike tags (unsupported by the database and file drivers).
- `ReportCacheObserver` is attached to every model the reports read, calling `flush()` on `saved`, `deleted` and
  `restored`. A teacher entering marks at 10:05 sees them in the school-wide report immediately.
- The low-performer threshold is an admin setting (`SchoolSetting::report_low_threshold`), saved via
  `admin.reports.school-wide.threshold`, and changing it also flushes the cache.

---

_End of business logic documentation._
