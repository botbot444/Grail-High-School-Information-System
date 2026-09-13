# Business Logic

> Last updated: 2026-09-13
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

The teacher **portal dashboard** (`TeacherController@dashboard`) and **My Classes** (`classes()`) aggregate roster size, pending exam marks, and attendance from the teacher’s `ClassSubject` rows plus homeroom classes. Dedicated timetable / attendance / performance / announcements / settings URLs still render `teacher.placeholder`.

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

_End of business logic documentation._
