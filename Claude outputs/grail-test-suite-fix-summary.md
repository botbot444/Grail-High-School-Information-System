# Grail SIS — Test Suite Fix Summary

**Date:** September 14, 2026
**Status:** `php artisan test` is fully green — all tests passing.

## Where things stood

After pulling in the Phase 12 work (account deactivation), the test suite was badly broken: 36 failing, 25 passing. Working through it turned up two separate problems layered on top of each other, plus a handful of incomplete features that were quietly crashing pages rather than causing test failures directly.

## The merge left literal conflict markers in committed files

The root cause of most of the breakage was that an earlier merge (the commit tagged `ae247bb`, "Progress upto Phase 12") never actually got resolved properly — the `<<<<<<<` / `=======` / `>>>>>>>` conflict markers were left in place and committed as regular text. This showed up in three different files: `routes/web.php` (a fatal parse error that broke every single route), `TeacherController.php` (a duplicate `performance()` method causing a fatal redeclaration error), and `teacher/performance.blade.php` (the entire page body was one giant unresolved conflict block, causing an "undefined variable `$classes`" error). In each case the fix was the same: keep the current, correct half of the conflict and delete the stale half along with the marker lines.

## The deactivation feature had a subtle Eloquent bug

Once the parse errors were cleared, 36 of the remaining failures all had the same error: "This account has been deactivated." The new `is_active` column was set up correctly in the database (default `true`), but Laravel's `Model::create()` builds its database insert from the model's in-memory data at the moment it's constructed and never re-reads the row afterward. That meant every freshly created user in a test (or in the app itself) had no `is_active` value in memory at all, which reads as `null` — and the new deactivation check treats a `null` as "deactivated." The fix was a one-line addition to `User.php` giving every new user instance an in-memory default of `true`, independent of the database default. That single change took the suite from 36 failing down to 10.

## Four sidebar links point at pages that were never built

The admin sidebar — which renders on every single admin page — had four navigation links pointing at routes that don't exist anywhere in the codebase: no route, no controller, no view. These are: a "User Accounts" page (`admin.users.index`, presumably meant to go with the deactivation feature so admins can actually activate/deactivate accounts), and three report pages under "School Performance," "Attendance Report," and "Fee Aging." Only the "Fee Collection Report" link in that group is actually wired up.

Because the sidebar is shared across every admin page, these four dead links were crashing any admin page the tests tried to load with a `RouteNotFoundException`. Rather than build out four unstarted features as a side effect of fixing the test suite, each link was wrapped in a check that only shows it once its route actually exists — so the pages load cleanly now, but the links themselves are invisible until someone builds those four screens. **These still need to be built by whoever owns the admin user-management and reporting work.**

## Net result

| Stage | Failing | Passing |
|---|---|---|
| After the Phase 12 merge | 36 | 25 |
| After fixing the conflict markers and the `is_active` bug | 10 | 51 |
| After guarding the sidebar's dead report links | 9 | 52 |
| After guarding the remaining dead links | 0 | 61 |

Full details, including the exact code changes, are saved in the project docs under `test-suite-fixes-and-known-gaps.md` for anyone who wants to dig into the specifics.
