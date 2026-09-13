# Controllers

> Last updated: 2026-09-13
> Update this file when controllers are added or modified.

---

## 6.1 Top-level (`app/Http/Controllers/`)

| File                      | Purpose                                                               |
| ------------------------- | --------------------------------------------------------------------- |
| `Controller.php`          | Base controller                                                       |
| `AuthController.php`      | Custom login dispatcher (redirects by role after auth)                |
| `DashboardController.php` | `/dashboard` role redirect; `adminDashboard()` for `/admin/dashboard` |
| `ProfileController.php`   | Edit/update/delete user profile                                       |
| `TeacherController.php`   | Teacher portal: `dashboard()`, `classes()`, `roster()`, `marks()`, `storeMarks()`, `performance()`, and grade-finalization actions |

---

## 6.2 Admin (`app/Http/Controllers/Admin/`)

| File                         | Methods                                                                                                                                                                       |
| ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `AdminController.php`        | Student CRUD (`index`–`destroy`), `settings()`, `examinations()`. `dashboard()` still exists but the live `/admin/dashboard` route uses `DashboardController@adminDashboard`. |
| `AdminTeacherController.php` | CRUD for teachers                                                                                                                                                             |
| `AdminParentController.php`  | CRUD for parents                                                                                                                                                              |
| `AdminClassController.php`   | CRUD for classes                                                                                                                                                              |
| `AdminSubjectController.php` | CRUD for subjects                                                                                                                                                             |
| `FeeController.php`          | Fee CRUD, bulk action, send reminder                                                                                                                                          |
| `PaymentController.php`      | `store()` payment on a fee, `receipt()`                                                                                                                                       |
| `FeeCategoryController.php`  | Category index/store/update/destroy under settings                                                                                                                            |
| `AuditLogController.php`     | `index()` audit log viewer                                                                                                                                                    |
| `ReportController.php`       | Fee-collection report + CSV export, student financials, statement                                                                                                             |
| `AcademicYearController.php` | CRUD for academic years                                                                                                                                                       |
| `TermController.php`         | CRUD for terms                                                                                                                                                                |
| `HolidayController.php`      | CRUD for holidays                                                                                                                                                             |
| `GradeLevelController.php`   | CRUD for grade levels                                                                                                                                                         |
| `PeriodController.php`       | Grade-level-scoped period CRUD                                                                                                                                                |
| `TimetableController.php`    | Admin class/term builder, copy-to-term, and clear actions                                                                                                                     |

---

## 6.3 Parent / Student

- `Parent/ParentController.php` — `dashboard()`, `children()`, `switchChild()`, `attendance()`, `performance()`, `reports()`, `assignments()`, `fees()`, `showFee()`, `settings()`, `updateSettings()`. Child selection uses `?child_id=` then `session('selected_child_id')`, then the first linked child. Fees belonging to another parent’s child abort 404.
- `Student/StudentController.php` — `dashboard()`, `timetable()` scoped to the authenticated student's class.

`TeacherController::timetable()` returns separate class grids for the authenticated teacher's scheduled slots. `ParentController::timetable()` scopes the selected child through the parent's owned children before querying timetable slots.

Fee totals on parent pages use `amount_due` / `amount_paid` sums (not only `Fee::cleared()`). Admin KPI collection figures on the live dashboard come from `Payment` records for today plus student/staff counts.

---

## 6.4 Auth (Laravel Breeze, `app/Http/Controllers/Auth/`)

`AuthenticatedSessionController`, `ConfirmablePasswordController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, `NewPasswordController`, `PasswordController`, `PasswordResetLinkController`, `RegisteredUserController`, `VerifyEmailController`.

---

_End of controllers documentation._
