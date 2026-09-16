# Middleware and Error Handling

> Last updated: 2026-09-16
> Update this file when middleware or error handling changes.

---

## 8.1 `App\Http\Middleware\CheckRole`

- **Signature**: `handle(Request, Closure, string $role): Response`
- **Behaviour**:
    1. If user not authenticated → redirect to `/login`.
    2. If user lacks the required role (via `User::hasRole($role)`) → abort with **403 Unauthorized**.
    3. Otherwise pass to next request.
- **Registered as**: the `role` alias in `bootstrap/app.php` (`$middleware->alias(['role' => CheckRole::class])`).

---

## 8.2 `App\Http\Middleware\EnsureAccountIsActive` (Phase 12)

- **Appended to the `web` group** — runs *first*, before `EnsurePasswordIsChanged`.
- **Behaviour**: if the authenticated user's `is_active` is false → `Auth::logout()`, invalidate the session,
  regenerate the CSRF token, and redirect to `login` with an error: *"This account has been deactivated. Please contact the school office."*
- **Why**: blocking at login alone is not enough — an already-signed-in user would keep their session until it expired.
  This ends it on the next request, so an admin deactivating an account revokes access immediately.
- **Interacts with**: `User::$attributes['is_active'] = true`, so a freshly created user is active in memory (not just in the DB).

---

## 8.3 `App\Http\Middleware\EnsurePasswordIsChanged`

- **Appended to the `web` group** after `EnsureAccountIsActive`.
- **Behaviour**: when `users.must_change_password` is true, the user is locked to their own settings page until they
  choose a new password. Where `must_change_password` is false (or no settings route exists for the role), the request passes through.
- **Always-allowed routes** (`ALLOWED_ROUTES`): `password.update`, `logout`, `admin.settings`, `teacher.settings`,
  `parent.settings`, `student.settings`.
- **Role → settings route**: `admin` → `admin.settings`, `teacher` → `teacher.settings`, `parent` → `parent.settings`,
  `student` → `student.settings`. Redirects carry the notification *"For security, please set a new password before continuing."*
- **Set by**: admin-created parent accounts (`AdminParentController@store`), admin password resets
  (`UserAccountController@resetPassword`), and approved registrations (child account).
- **Cleared by**: `PasswordController@update` once a new password is saved.

---

## 8.4 Custom 419 Error Handling (`bootstrap/app.php`)

- `TokenMismatchException` is caught via `$exceptions->render(...)` and rendered with the custom `errors.419` Blade view.
- JSON requests receive a JSON response (`{"message": "Page expired. Please refresh and try again."}`); web requests receive the HTML view.

---

## 8.5 Registration of the `web` middleware stack

From `bootstrap/app.php`:

```php
$middleware->web(append: [
    \App\Http\Middleware\EnsureAccountIsActive::class,
    \App\Http\Middleware\EnsurePasswordIsChanged::class,
]);
```

Order matters: a deactivated user is logged out outright rather than being locked to the settings page for a password change.

---

_End of middleware and error handling documentation._
