# Tests

> Last updated: 2026-09-16
> Update this file when tests are added or modified.

---

All located in `tests/`. **23** feature test files (17 top-level + 6 under `Feature/Auth/`) and **2** unit tests.

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
| `TeacherPerformanceTest.php`        | Teacher performance page + grade finalization |
| `TeacherRosterTest.php`             | Teacher class roster page                   |
| `TeacherSettingsTest.php`           | Teacher settings page                       |
| `TimetableRoleScopeTest.php`        | Timetable visibility is scoped per role     |
| `TimetableWorkflowTest.php`         | Admin timetable builder / copy / clear      |
| `Auth/AuthenticationTest.php`       | Authentication flow (login/logout)          |
| `Auth/EmailVerificationTest.php`    | Email verification flow                     |
| `Auth/PasswordConfirmationTest.php` | Password confirmation for sensitive actions |
| `Auth/PasswordResetTest.php`        | Password reset request + reset              |
| `Auth/PasswordUpdateTest.php`       | Password update on profile screen           |
| `Auth/RegistrationTest.php`         | New user registration                       |

---

## Unit Tests (`tests/Unit/`)

| File                                 | Purpose                                              |
| ------------------------------------ | ---------------------------------------------------- |
| `ExampleTest.php`                    | Unit test example                                    |
| `TimetablePeriodValidationTest.php`  | Period overlap / time-order validation rules         |

---

## Base Test Case

- `TestCase.php` — base test class for the suite

---

_End of tests documentation._
