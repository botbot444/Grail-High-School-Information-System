# Grail SIS — Manual Testing Guide

> For a person clicking through the app in a browser. For the automated PHPUnit
> suite, see `project_documentation/tests.md` instead — this document doesn't
> replace it, it's for hands-on QA of what the suite doesn't (or can't) cover:
> real page rendering, JS behavior, and cross-feature flows.

Last updated: 2026-09-15, after a testing pass that found and fixed several
real bugs (see "Known bug patterns" below — read that section first, it'll
save you time).

---

## 1. Setup

- Server: `php artisan serve` or XAMPP Apache, whichever this project is
  currently running under. Confirm which — `APP_URL` in `.env` doesn't
  necessarily match how you're actually browsing it (this caused broken file
  links once already; see Known Issues).
- Fresh data: `php artisan migrate:fresh --seed` gives you a clean, known
  dataset. Don't run this against data you care about.
- Mail: `MAIL_MAILER=log` — emails don't send anywhere, they're written to
  `storage/logs/laravel.log`. To check an email a feature claims to send,
  `grep` that file for the subject line rather than expecting an inbox.

### Seeded test accounts (password `12345678` unless noted)

| Role    | Email                              | Notes |
|---------|-------------------------------------|-------|
| Admin   | `admin@grail.school`               | Only admin account — can't deactivate/demote yourself, the app blocks it |
| Parent  | `parent@grail.school`              | "Demo Parent", linked to "Demo Student" |
| Student | `student@grail.school`             | "Demo Student", 2026/0001 |
| Teacher | `{subject}@grail.school`           | e.g. `english.language@grail.school`, `biology@grail.school` — one per subject |
| Student | `firstname.lastname{id}@student.grail.school` | Every other seeded student now has a login too (see Known Issues — this used to not be true) |

---

## 2. Known bug patterns — check for these everywhere

These aren't hypothetical — each one caused a real bug found and fixed this
session, and the same mistake could exist on pages not yet audited.

### a. Nested `<form>` tags
Invalid HTML. When a page has a main edit/save form and ALSO an inline
delete/reset-password/other-action form inside the same visual "card," check
the page source (`view-source:` or DevTools) for a `<form>` sitting inside
another `<form>`. The browser silently closes the *outer* form early at the
inner form's `</form>` tag — so "Save" can end up submitting to the delete
route, or fields physically below the nested form silently fall outside the
form and never get submitted at all (this is the sneakier variant — no error,
data just doesn't save).

**Found & fixed in:** `admin/classes/edit`, `admin/parents/edit`,
`admin/fees/index` (the per-row delete button nested inside the bulk-actions
form — this one also silently broke bulk select/actions for every row after
the first, since their checkboxes fell outside the form too), and
(previously) `admin/fees/edit`.

**Swept the entire `resources/views` tree for this pattern (2026-09-15) and
confirmed clean** — a small script that tracks `<form>`/`</form>` nesting
depth per file (stripping Blade/HTML comments first, since explanatory
comments mentioning `<form>` produce false positives) found no other
instances. If you add a new page with an inline action button next to a
bigger form, re-run this check rather than assuming it's fine:
```js
// Quick DevTools check for one page:
document.querySelectorAll('form').forEach(f => {
  if (f.closest('form') !== f) console.warn('NESTED FORM:', f.action);
});
```

### b. `destroy()` actions with no dependency guard
Several delete actions soft-delete a record with zero check for whether real
data still points at it (students still enrolled in a class, a fee already
paid, etc.). A soft-deleted record disappears from lists everywhere but the
foreign keys pointing at it don't — so whatever it was doesn't vanish, it just
becomes invisible and broken.

**Found & fixed:** `admin.classes.destroy` (blocks deleting a class with
enrolled students), `admin.teachers.destroy` (blocks deleting a homeroom
teacher or one with active class-subject assignments), `admin.parents.destroy`
(blocks deleting a parent with children still linked) — all verified live.
**Checked and found clean:** `admin.grade-levels.destroy` and
`admin.settings.categories` (fee categories) already had correct guards.
`admin.announcements.destroy` / teacher `assignments.destroy` have no guard
but don't need one — nothing else breaks when those disappear, unlike a
class/teacher/parent. **Still worth checking:** subjects (deleting a subject
a class still offers or has grades against).

### c. Unique validation that doesn't exclude soft-deleted rows
Every model using `SoftDeletes` was audited for this
(`SchoolClass`, `Student`, `Teacher`, `ParentProfile`, `FeeCategory`,
`Announcement`, `Assignment`). Found and fixed on **four** of them:
`school_classes.class_name`, `teachers.email`, `parents.email` (both the
admin edit forms and the public `/register` form), and `fee_categories.name`.
`Student` was already fine (`nextStudentNumber()` correctly uses
`withTrashed()`); `Announcement`/`Assignment` have no unique columns to begin
with. If you add a new unique field to any soft-deletable model, add
`Rule::unique(...)->whereNull('deleted_at')` (and `->ignore(...)` on update)
from the start.

### d. Shared browser session across roles
If you're testing multiple roles (admin, then parent, then student) in the
same browser/tab, you're on the SAME cookie jar. Navigating to `/login` while
already authenticated does **not** show you a blank login form ready for new
credentials in every case — sometimes it silently redirects you to your
current role's dashboard without processing anything you type. **Always sign
out explicitly (click "Sign Out," don't just navigate to `/login`) before
switching roles**, or use separate browser profiles / incognito windows per
role if testing them side by side.

### e. `EnsurePasswordIsChanged` locks a fresh account to its own settings page
Any account created with a temporary password (`must_change_password = true`)
can only reach its own `*.settings` route until the password is changed —
this is intentional, not a bug, but it means you can't "poke around" as a
freshly-created teacher/parent/student until you've completed that step on
their settings page first.

---

## 3. Admin portal (`/admin/...`, requires `role:admin`)

For every CRUD screen below: **create** a record with an obviously-fake name
(e.g. prefix with `QA Test`), **edit** it, **delete** it, and confirm the
list count returns to where it started. Don't leave test data behind — if a
test record can't be cleanly deleted through the UI (e.g. blocked by a
dependency guard), note that as a finding rather than forcing it.

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/admin/dashboard` | Loads, stat cards aren't obviously wrong (e.g. hardcoded "14 pending" style numbers — the "Suspensions" card was exactly this and got removed) |
| Students | `/admin/students` | List, search, filter by class/grade/status/gender, CSV export. Create (with and without a login email — check the one-time password flow when email is given). Edit. Bulk actions (Transfer/Archive/Delete) — **not yet manually tested this session**. Delete guard. |
| Fees — bulk select | `/admin/fees` | **Fixed this session**: per-row Delete buttons were nested inside the bulk-select form, which broke both the individual delete button *and* silently excluded every row after the first from bulk actions (Mark Cleared/Overdue, Send Reminder, Export, Delete Selected). Re-verify: check boxes on two different rows, confirm a bulk action includes both. |
| Teachers | `/admin/teachers` | Create (temp password flow), edit, assign subject to class (test the "already taught by X, confirm handover" prompt), unassign, delete (blocked if homeroom teacher or has class-subjects — verified), email reusable after a dependency-free delete (verified). |
| Parents | `/admin/parents` | Create (temp password flow), edit — linked-children checkboxes save correctly (fixed & verified), reset password, delete (blocked if children still linked — verified). |
| Classes | `/admin/classes` | Create/edit with subjects checked but no teacher (should succeed, show "No teacher assigned"), delete guard (blocked when students enrolled), duplicate name rejected, name reusable after a dependency-free delete. |
| Subjects | `/admin/subjects` | Create, edit, delete (check what happens if a class still offers it). |
| Grade Levels | `/admin/grade-levels` | Create, edit, delete — tested, clean (already had a proper dependency guard, hard-deletes so no soft-delete/uniqueness risk). |
| Academic Years | `/admin/academic-years` | Create, edit, delete (blocked if it has grades/fees or is the current year). |
| Terms | `/admin/terms` | Create, overlap validation (two terms in the same year with overlapping dates should be rejected), edit, delete. Note: the index defaults to the *current* academic year — a term you just added under a different year won't visibly appear until you switch the year filter. |
| Holidays | `/admin/holidays` | Create, edit, delete — confirm the affected term's "school days" count on the Terms page recalculates. |
| Periods | `/admin/periods` | Create/edit/delete, scoped per grade level. |
| Timetable | `/admin/timetable` | Assign a subject+teacher to a day/period cell, save. Try assigning a teacher who's already booked elsewhere at that time — should be rejected with a clear conflict error and nothing written. "Copy to Term" (duplicates a class's whole week into another term) and "Clear" (wipes one). |
| Fees | `/admin/fees` | Create a fee (with line items), edit, record a payment (including overpayment → check it becomes account credit on the student), send reminder, delete. Payment lookup by reference. |
| Payment Submissions | `/admin/payment-submissions` | Review queue (pending/approved/rejected tabs), approve (creates a real `Payment`, updates fee balance), reject (requires a reason, notifies the parent, no accounts/payments created). |
| Registration Requests | `/admin/registration-requests` | Review queue, approve (creates parent + child `User` + `Student`, child gets a temp password, parent's own chosen password carries over unchanged), reject (no accounts created, applicant notified with the reason). |
| Fee Categories | `/admin/settings/categories` | Create, inline rename, delete — tested, clean apart from the uniqueness/soft-delete gap (fixed, see §2c). |
| Payment Instructions | `/admin/settings/payments` | Edit the bank/mobile-money details shown to parents — **not yet manually tested this session**. |
| Promotions | `/admin/promotions` | Grade-level mapping setup, run a promotion for a class (promote/retain/graduate decisions), rollback a batch — **not yet manually tested this session**; this touches Student, StudentPromotion, and login deactivation on graduation, worth real attention. |
| Announcements | `/admin/announcements` | Create (check audience targeting), preview, edit, delete — **not yet manually tested this session**. |
| Report Cards | `/admin/report-cards` | Browse finalized cards, unfinalize override — **not yet manually tested this session**. |
| Reports & Analytics | `/admin/reports/*` | Fee collection, aging, attendance, school-wide performance — each has a CSV/export variant to check too. **Not yet manually tested this session**. |
| User Accounts | `/admin/users` | Filter by role/status, reset password (fixed this session — was silently failing with a wrong HTTP method), change role (check the "last active admin" guard), activate/deactivate. |
| Audit Logs | `/admin/audit-logs` | Confirm actions taken above actually produce entries here with sensible before/after values, and that `password`/`remember_token` never appear in them. |
| Student financials/statement | `/admin/students/{id}/financials`, `/statement` | **Not yet manually tested this session**. |

---

## 4. Teacher portal (`/teacher/...`, requires `role:teacher`)

Not manually tested this session — treat as a priority for the next pass.

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/teacher/dashboard` | |
| My Classes | `/teacher/classes`, `/teacher/classes/{class}/roster` | |
| Marks entry | `/teacher/marks` | Enter marks for a class/subject, confirm they land correctly against the right `class_subject_id` |
| Attendance | `/teacher/attendance` | Record attendance, confirm it feeds the same attendance-rate numbers the admin/parent/student portals show (there was a past bug where these disagreed across portals) |
| Assignments | `/teacher/assignments/*` | Create, view submissions, grade a submission |
| Performance / Report Cards | `/teacher/performance`, `/teacher/report-cards/*` | Finalize a class's report cards, request an unfinalize, subject comments |
| Student profile | `/teacher/students/{student}` | |
| Settings | `/teacher/settings` | Profile update, password change |
| Timetable | `/teacher/timetable` | |

---

## 5. Parent portal (`/parent/...`, requires `role:parent`)

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/parent/dashboard` | Overdue-fee banner, per-child summary cards |
| My Children | `/parent/children`, child switcher | |
| Attendance / Performance / Reports | `/parent/attendance`, `/parent/performance`, `/parent/reports` | |
| Fees | `/parent/fees` | "How to pay" panel (bank/mobile-money instructions + reference code), **submit proof of payment** (tested this session — works), payment history, receipts |
| Assignments | `/parent/assignments` | |
| Timetable | `/parent/timetable` | |
| Announcements | `/parent/announcements` | Mark read / mark all read |
| Settings | `/parent/settings` | Profile update (name/email/phone/address), password change |

---

## 6. Student portal (`/student/...`, requires `role:student`)

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/student/dashboard` | |
| Results | `/student/results` | |
| Attendance | `/student/attendance` | |
| Report Cards | `/student/report-cards*` | |
| Assignments | `/student/assignments/*` | Submit an assignment (with and without a file, if the assignment allows files) |
| Timetable | `/student/timetable` | |
| Settings | `/student/settings` | Password change (fixed this session — used to be a dead link to the forgot-password email flow, which a logged-in, forced-to-change-password student could never actually complete) |
| Announcements | `/student/announcements` | |

---

## 7. Auth & public

| Area | Route | What to verify |
|---|---|---|
| Login | `/login` | Wrong password shows an error, correct login redirects by role |
| Parent+child registration | `/register` | Submit while logged out — confirm **nothing** is created in `users`/`students`, only a pending `registration_request`. See it in the admin queue (§3). |
| Forgot password | `/forgot-password`, `/reset-password/{token}` | Email-based reset — check the log file for the email since nothing actually sends |
| Logout | Sign Out button (not a bare `/logout` GET — that route doesn't exist, it's POST-only) | |

---

## 8. Testing hygiene

- **Prefer a fresh seed over testing against data you or the user cares
  about.** If you can't reseed, use an obviously-fake name/email for anything
  you create (`QA Test ...`, `qa.test@example.test`) so it's easy to find and
  remove afterward, and easy to distinguish from a real user's own in-progress
  work if you're sharing the environment with them.
- **Check row counts before and after** a testing pass on any given table
  (`php artisan tinker` → `Model::count()`) so you can tell if you left
  something behind.
- **Verify at the DB level, not just the UI**, for anything that touches
  money, accounts, or grades — a success flash message doesn't prove the
  write actually happened or happened correctly. `Hash::check()` a password,
  count child rows, check foreign keys resolve, etc.
- **Never delete/modify a record you didn't create without checking its
  dependents first** (see §2b) — soft-deleting something with real students,
  payments, or grades attached breaks it silently rather than loudly.
