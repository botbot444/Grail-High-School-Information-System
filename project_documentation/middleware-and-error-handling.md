# Middleware and Error Handling

> Last updated: 2026-08-02
> Update this file when middleware or error handling changes.

---

## 8.1 `App\Http\Middleware\CheckRole`

- **Signature**: `handle(Request, Closure, string $role): Response`
- **Behaviour**:
    1. If user not authenticated → redirect to `/login`.
    2. If user lacks the required role (via `User::hasRole($role)`) → abort with **403 Unauthorized**.
    3. Otherwise pass to next request.
- **Registered as**: the `role` alias (verify in `bootstrap/app.php`).

---

## 8.2 Custom 419 Error Handling (`bootstrap/app.php`)

- `TokenMismatchException` is caught and rendered with a custom `errors.419` Blade view.
- JSON requests receive a JSON response; web requests receive the HTML view.

---

_End of middleware and error handling documentation._
