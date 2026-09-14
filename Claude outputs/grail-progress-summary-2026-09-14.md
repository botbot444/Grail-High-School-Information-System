# Grail SIS — progress summary (2026-09-14)

## TL;DR

`php artisan test` is fully green. Today's session cleaned up merge-conflict fallout from the Phase 12 pull, fixed a real Eloquent bug in the new account-deactivation check, discovered and fixed a routing gap that had Phase 9's reporting suite and Phase 12's admin account screen fully built but unreachable, and closed out the teacher portal's last real visual gap (Settings). One correction to the plan doc worth flagging to the team: Phase 0's "⚠️ Deviated" marker is misleading — see below, there's no actual deviation on the machine the plan cares about.

## What today's session found and fixed

A `git pull` had merged in the Phase 12 work (account deactivation), but the merge left three files with **literal, never-resolved `<<<<<<<`/`=======`/`>>>>>>>` conflict markers committed as plain text** — leftovers from an earlier merge (`ae247bb`, "Progress upto Phase 12") that never got cleaned up before someone committed. That broke:

- `routes/web.php` — fatal parse error, every route down
- `TeacherController.php` — duplicate `performance()` method, fatal redeclaration
- `teacher/performance.blade.php` — entire file body was one unresolved conflict, undefined-variable crash

Separately, the new `is_active` account-deactivation check had a real bug: the column defaults to `true` in the database, but Eloquent's `Model::create()` builds its insert from data already in memory and never re-reads the row afterward, so every freshly created user came back with the field simply missing — read as `null`, which the "is this account active" middleware treated as deactivated. That was the single biggest cause of test failures (36 → 10 once fixed). One-line class default on the `User` model, no test/factory changes needed.

## The Phase 9/12 story: built, but not wired up — now fixed

`implementation_plan (2).md` marks Phase 9 (reporting suite) and Phase 12 (admin account management) as ✅ complete from 2026-09-13. My first pass through the code seemed to confirm the opposite — the admin sidebar's links to a school-wide performance report, an attendance report, a fee-aging report, and a user-management screen all 500'd with `RouteNotFoundException`. I initially reported these as unbuilt features and just guarded the dead links.

That was wrong, and worth knowing for next time: **the actual implementations were sitting in the repo the whole time**, complete and well-written —

- `UserAccountController.php` + `views/admin/users/index.blade.php` — full user management: search/filter, activate/deactivate, guarded role changes (can't touch your own account, can't demote the last admin), one-time temporary password reset.
- `AnalyticsController.php` + `AnalyticsService.php` (22KB) + three report views — attendance, fee aging, and the school-wide performance report, each with CSV export, reusing the same permission gates as the existing fee-collection report.

They just weren't registered in `routes/web.php` at all — no `use` import, no route. Same merge batch timestamp as everything else that went wrong today, so it looks like the routes got silently dropped during that merge, the same way the conflict markers did, just without an error to point at it (a missing route only breaks when someone actually clicks the link).

**Fixed:** added the ~11 missing route registrations (matching the route names the already-built views' forms expected), reverted the sidebar guards back to plain links, verified everything with `php -l` and a full test pass. Both features are live now.

**Lesson for the team:** "✅ COMPLETE" in the plan doesn't guarantee the routes are wired — worth spot-checking `routes/web.php` directly against any phase before trusting the checkbox, since this merge dropped things in two different ways (loud conflict markers in some files, silent missing routes in others) with no single tell that would catch both.

## Teacher portal: Settings redesigned — the teacher side of Phase 4 is now done

The plan's Phase 4 status line claimed "2 teacher screens remain: Record Attendance, Settings." Checking that against the code turned up the same kind of drift as Phase 9/12: `attendance.blade.php`, `timetable.blade.php`, `roster.blade.php`, `announcements.blade.php`, and `marks.blade.php` were all already fully built in the polished Material Design 3 style — the plan's checklist was stale on every one of them except Settings, which really was still wrapping Laravel Breeze's default unstyled form partials.

Generated a Stitch design for it, then wired it into `teacher/settings.blade.php` for real — same routes and field names the backend already expects (`profile.update`, `password.update`), so no controller changes were needed. Dropped a few fields from the raw Stitch mockup that had no real data behind them (a fabricated department, building/lab, academic level) and replaced them with real derived data instead: staff ID, homeroom class, and classes/subjects-taught counts, pulled from the teacher's actual relations. Kept the password-strength meter and show/hide toggles; replaced the mockup's fake JS-driven toast with the app's real session-flash-driven one.

With this, every teacher-portal screen the plan's detailed Phase 4 checklist called out is now built and styled. Two things I have **not** verified this session and didn't want to claim without checking: the Parent portal's "report card summary with link to full report card" (plan marks this unchecked — PDF report cards were pushed to Phase 11) and the Student dashboard's attendance-history detail view and report-card view/download (also unchecked in the plan). Worth someone confirming those before marking Phase 4 fully closed.

## Correction: Phase 0 isn't actually deviated

The plan flags Phase 0 ("move dev DB to MySQL") as "⚠️ DEVIATED (now SQLite, see status snapshot)". Per Lazarus: that's not accurate as a project-wide statement — this machine (his) runs the app on XAMPP against real MySQL, exactly as Phase 0 intended. The SQLite usage is local to teammates' machines, not a project-level decision to abandon MySQL. Worth the team updating the plan doc to say this is an environment-consistency gap between team members' local setups, not a deviation from the plan's actual target — otherwise it reads like MySQL was abandoned, which it wasn't.

## Where things stand phase-by-phase (per the plan doc)

| Phase | Plan status | Notes |
|---|---|---|
| 0 — MySQL dev DB | Not actually deviated | plan doc's wording is misleading — see correction above; this machine runs MySQL as intended, SQLite is a teammate-local setup difference, not a project decision |
| 1 — Fee status bug | ✅ Complete | |
| 2 — Multi-item fees + audit trail | ✅ Complete | |
| 3 — School calendar | ✅ Complete | |
| 4 — Close role-dashboard gap | 🟡 Nearly done | teacher side now fully done (fixed today); Parent report-card link and Student attendance-history/report-card items still unverified |
| 5 — Announcements with targeting | ✅ Complete | |
| 6 — Student promotion / rollover | ✅ Complete | |
| 7 — Fee receipts + overdue notifications | ✅ Complete | |
| 8 — Bootstrap → Tailwind doc reconcile | Not started | docs-only phase |
| 9 — Reporting suite | ✅ Confirmed complete and now reachable | fixed today — see above |
| 10 — Class timetable | ✅ Complete | |
| 11 — Report card enhancements + class rank | ✅ Complete | |
| 12 — Admin account management | ✅ Confirmed complete and now reachable | fixed today — see above |
| 13 — Test consolidation | In progress | `php artisan test` now passes fully — the plan's own exit checklist also wants an actual coverage number and every "🧪 Suggested tests" box across Phases 2–12 genuinely checked off, not just aspirational |
| 14 — Mobile-responsiveness pass | Not started | |
| 15 — Production/deployment alignment | Not started | |

Also flagged in the plan's own "Remaining open items" table, unrelated to today: collecting a real report-card sample from a teacher, confirming UAT participant availability, verifying XAMPP's MariaDB JSON-column compatibility, and deciding on a CSV export library — all still "Pending," owners assigned in the doc.

## Suggested next steps

1. Someone should click through the User Accounts screen, the three new report pages, and the redesigned teacher Settings page in a browser — I verified the code (syntax, routing, test suite) but haven't rendered any of them visually.
2. Phase 13 (test consolidation) is close — suite passes, but still wants a real coverage number and every phase's "🧪 Suggested tests" box actually checked, including new tests for the Phase 9/12 routes that just got wired up (they weren't covered by any existing test).
3. Confirm whether the Parent report-card-link and Student attendance-history/report-card items are actually done or actually open — the plan checklist has them unchecked but, given today's track record, that's not a reliable signal on its own.
4. Someone should update the plan doc's Phase 0 line so it doesn't read as "MySQL was abandoned" when it wasn't.
