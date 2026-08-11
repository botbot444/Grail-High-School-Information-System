# Tests

> Last updated: 2026-08-09
> Update this file when tests are added or modified.

---

All located in `tests/`.

---

## Feature Tests (`tests/Feature/`)

| File                                | Purpose                                     |
| ----------------------------------- | ------------------------------------------- |
| `AdminStudentParentLinkTest.php`    | Admin student-parent linking                |
| `AuthLoginTest.php`                 | Authentication login flow                   |
| `ExampleTest.php`                   | General feature test example                |
| `LoginCsrfProtectionTest.php`       | CSRF protection on login                    |
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
