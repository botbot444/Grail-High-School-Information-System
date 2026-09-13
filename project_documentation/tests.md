# Tests

> Last updated: 2026-09-13
> Update this file when tests are added or modified.

---

All located in `tests/`.

---

## Feature Tests (`tests/Feature/`)

| File                                | Purpose                                     |
| ----------------------------------- | ------------------------------------------- |
| `AdminStudentParentLinkTest.php`    | Admin student-parent linking                |
| `AdminStudentEditPageTest.php`      | Admin student edit page                     |
| `AdminTeacherCreatePageTest.php`    | Admin teacher create page                   |
| `AdminTeacherEditPageTest.php`      | Admin teacher edit page and update workflow |
| `AuthLoginTest.php`                 | Authentication login flow                   |
| `ExampleTest.php`                   | General feature test example                |
| `LoginCsrfProtectionTest.php`       | CSRF protection on login                    |
| `ParentPortalDumpTest.php`          | Parent portal HTML dump helper              |
| `ParentPortalRenderTest.php`        | Parent dashboard render + role isolation    |
| `Phase4ReportTest.php`              | Fee-collection report / student financials  |
| `ProfileTest.php`                   | User profile operations                     |
| `TeacherMarksEntryTest.php`         | Teacher marks entry workflow                |
| `Auth/AuthenticationTest.php`       | Authentication flow (login/logout)          |
| `Auth/EmailVerificationTest.php`    | Email verification flow                     |
| `Auth/PasswordConfirmationTest.php` | Password confirmation for sensitive actions |
| `Auth/PasswordResetTest.php`        | Password reset request + reset              |
| `Auth/PasswordUpdateTest.php`       | Password update on profile screen           |
| `Auth/RegistrationTest.php`         | New user registration                       |

---

## Unit Tests (`tests/Unit/`)

| File              | Purpose           |
| ----------------- | ----------------- |
| `ExampleTest.php` | Unit test example |

---

## Base Test Case

- `TestCase.php` — base test class for the suite

---

_End of tests documentation._
