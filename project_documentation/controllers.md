# Controllers

> Last updated: 2026-08-02
> Update this file when controllers are added or modified.

---

## 6.1 Top-level (`app/Http/Controllers/`)

| File                      | Purpose                                                                       |
| ------------------------- | ----------------------------------------------------------------------------- |
| `Controller.php`          | Base controller                                                               |
| `AuthController.php`      | Custom login dispatcher (redirects by role after auth)                        |
| `DashboardController.php` | Dispatches user to role-specific dashboard (`/dashboard`)                     |
| `ProfileController.php`   | Edit/update/delete user profile                                               |
| `TeacherController.php`   | `marks()` view + `storeMarks()` save — bulk marks + attendance per assignment |

---

## 6.2 Admin (`app/Http/Controllers/Admin/`)

| File                         | Methods (resource-style)                                                                                                                                |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `AdminController.php`        | `dashboard()`, `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`, `teachers()`, `classes()`, `settings()`, `examinations()` |
| `AdminTeacherController.php` | CRUD for teachers                                                                                                                                       |
| `AdminParentController.php`  | CRUD for parents                                                                                                                                        |
| `AdminClassController.php`   | CRUD for classes                                                                                                                                        |
| `AdminSubjectController.php` | CRUD for subjects                                                                                                                                       |

---

## 6.3 Parent / Student

- `Parent/ParentController.php` — `dashboard()`
- `Student/StudentController.php` — `dashboard()`

### Notes (Phase 1 fix)

- `AdminController::dashboard()` now uses `Fee::cleared()` to compute `feesCollected` and per-student paid totals (replaced prior `where('status', 'paid')`).
- `ParentController::dashboard()` and `StudentController::dashboard()` now use `Fee::cleared()` when computing paid amounts / fee balances.
- `resources/views/admin/students/show.blade.php` updated to label cleared fees as `Cleared` and sum cleared amounts accordingly.

---

## 6.4 Auth (Laravel Breeze, `app/Http/Controllers/Auth/`)

`AuthenticatedSessionController`, `ConfirmablePasswordController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, `NewPasswordController`, `PasswordController`, `PasswordResetLinkController`, `RegisteredUserController`, `VerifyEmailController`.

---

_End of controllers documentation._
