# Controllers

> Last updated: 2026-09-18
> Update this file when controllers are added or modified.

---

## 6.1 Top-level (`app/Http/Controllers/`)

| File                      | Purpose                                                                                                                            |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `Controller.php`          | Base controller                                                                                                                    |
| `AuthController.php`      | Custom login dispatcher (redirects by role after auth)                                                                             |
| `DashboardController.php` | `/dashboard` role redirect; `adminDashboard()` for `/admin/dashboard`                                                              |
| `TeacherController.php`   | Teacher portal: `dashboard()`, `classes()`, `roster()`, `studentProfile()`, `marks()`, `storeMarks()`, `timetable()`, `attendance()`, `storeAttendance()`, `performance()`, `finalizeGrades()`, `unfinalizeRequest()`, `announcements()`, `readAnnouncement()`, `readAllAnnouncements()`, `settings()`, `updateSettings()` |

> `ProfileController` and `ProfileUpdateRequest` have been **removed** — `/profile` is no longer routed and settings
> now live on each portal's `settings` action (see setup-and-conventions.md).

### `Concerns/RendersReportCards.php`

Shared trait used by the Admin, Teacher, Student and Parent portals. `renderReportCard()` returns the on-screen preview;
`downloadReportCard()` returns the PDF (`report-card-{number}-{name}-{term}.pdf`). It decides **what** is rendered; each portal decides **who** may see a card.

---

## 6.2 Admin (`app/Http/Controllers/Admin/`) — 24 controllers

| File                             | Methods                                                                                                                                                                        |
| -------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `AdminController.php`            | Student CRUD (`index`–`destroy`), `export()`, `refundCredit()`, `settings()`, `examinations()`. `store()` now **requires** a unique student `email` and always provisions that student's `User` (one-time password from `GeneratesTemporaryPassword`, `must_change_password = true`); the optional-login branch was removed in `7dbf85f`. `dashboard()` still exists but the live `/admin/dashboard` route uses `DashboardController@adminDashboard`. |
| `AdminTeacherController.php`     | CRUD for teachers + `assignSubject()` / `unassignSubject()` (write `class_subjects`)                                                                                            |
| `AdminParentController.php`      | CRUD for parents (new parents get a system-generated temporary password)                                                                                                       |
| `AdminClassController.php`       | CRUD for classes                                                                                                                                                                |
| `AdminSubjectController.php`     | CRUD for subjects                                                                                                                                                               |
| `FeeController.php`              | Fee CRUD, `lookup()`, `bulkAction()`, `sendReminder()`                                                                                                                          |
| `PaymentController.php`          | `store()` payment on a fee (via `Fee::applyPayment()`), `receipt()`                                                                                                            |
| `PaymentSubmissionController.php`| Parent proof-of-payment review queue: `index()`, `show()`, `approve()`, `reject()`                                                                                              |
| `PaymentSettingsController.php`  | Bank / mobile-money instructions shown to parents: `edit()`, `update()` (via `SchoolSetting`)                                                                                   |
| `FeeCategoryController.php`      | Category index/store/update/destroy under settings                                                                                                                              |
| `AuditLogController.php`         | `index()` audit log viewer                                                                                                                                                      |
| `ReportController.php`           | Fee-collection report + CSV export, `studentFinancials()`, `statement()`                                                                                                        |
| `AnalyticsController.php`        | Phase 9 reports: `attendance()`, `exportAttendance()`, `aging()`, `exportAging()`, `schoolWide()`, `exportSchoolWide()`, `saveThreshold()`                                       |
| `AnnouncementController.php`     | Phase 5 admin authoring: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`, `preview()` (reach JSON for the unsaved form)                                     |
| `PromotionController.php`        | Phase 6: `index()`, `show()`, `store()` (run), `rollback()`, `mappings()`, `saveMappings()`                                                                                     |
| `ReportCardController.php`       | Phase 11 browse/print any class + `unfinalize()` override (requires a reason, audit-logged)                                                                                     |
| `RegistrationRequestController.php` | Phase 12 review queue: `index()`, `show()`, `approve()` (creates parent + student), `reject()`                                                                                 |
| `UserAccountController.php`      | Phase 12 account management: `index()`, `toggleActive()`, `resetPassword()`, `updateRole()`. Guards: no self-action, last active admin can’t be deactivated/demoted, role change warns about attached records. |
| `AcademicYearController.php`     | CRUD for academic years                                                                                                                                                         |
| `TermController.php`             | CRUD for terms                                                                                                                                                                   |
| `HolidayController.php`          | CRUD for holidays                                                                                                                                                               |
| `GradeLevelController.php`       | CRUD for grade levels                                                                                                                                                            |
| `PeriodController.php`           | Grade-level-scoped period CRUD                                                                                                                                                   |
| `TimetableController.php`        | `index()` class/term builder, `store()` slot, `copyToTerm()`, `clear()`                                                                                                          |

---

## 6.3 Parent / Student / Teacher

- `Parent/ParentController.php` — `dashboard()`, `children()`, `switchChild()`, `attendance()`, `performance()`, `reports()`, `timetable()`, `assignments()`, `fees()`, `showFee()`, `reportCard()`, `receipt()`, `announcements()`, `readAnnouncement()`, `readAllAnnouncements()`, `settings()`, `updateSettings()`. Child selection uses `?child_id=` then `session('selected_child_id')`, then the first linked child. Fees belonging to another parent’s child abort 404.
- `Parent/PaymentSubmissionController.php` — `store()`: a parent’s claim of an outside payment, with proof uploaded to `payment-proofs/{fee_id}`. Returns 403 unless the fee belongs to one of their own children.
- `Student/StudentController.php` — `dashboard()`, `results()`, `attendance()`, `timetable()`, `reportCards()`, `reportCard()` (finalized cards only), `announcements()`, `readAnnouncement()`, `readAllAnnouncements()`, `settings()`. Every action resolves the student from the authenticated user (never a route id).
- `Student/AssignmentController.php` — `index()`, `show()`, `submit()`. An assignment is visible only if published **and** set for a subject taught to the student’s own class; otherwise 404. A graded submission can no longer be changed.
- `Teacher/AssignmentController.php` — `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`, `submissions()`, `grade()`. Scoped to the teacher’s own `class_subjects`; `Rule::in(...)` over those ids prevents posting to someone else’s class.
- `Teacher/ReportCardController.php` — `index()`, `show()`, `finalize()`, `saveOverallComment()`, `subjectComments()`, `saveSubjectComments()`, `preview()`. Distinguishes the **subject teacher** (per-subject remark) from the **class teacher** (overall comment + finalize + rank).
- `TeacherController::timetable()` returns separate class grids for the authenticated teacher's scheduled slots; `ParentController::timetable()` scopes the selected child through the parent's owned children before querying timetable slots.

Fee totals on parent pages use `amount_due` / `amount_paid` sums (not only `Fee::cleared()`). Admin KPI collection figures on the live dashboard come from `Payment` records for today plus student/staff counts.

---

## 6.4 Auth (`app/Http/Controllers/Auth/`)

`AuthenticatedSessionController`, `ConfirmablePasswordController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, `NewPasswordController`, `ParentRegistrationController`, `PasswordController`, `PasswordResetLinkController`, `VerifyEmailController`.

`ParentRegistrationController` (`create()`, `store()`, `submitted()`) replaces Laravel’s stock `RegisteredUserController`.
Sign-up creates a **pending `RegistrationRequest`** — no `users` row and no working login — until an admin approves it.
Sign-up is throttled (`throttle:5,1`) and always represents a brand-new admission (linking a parent to an already-enrolled student stays admin-only).
`StoreParentRegistrationRequest` validates `child_email` as required, unique against `users` **and** against other *pending*
requests, and `different:parent_email` — a child cannot be registered with the guardian's own address.

---

_End of controllers documentation._
