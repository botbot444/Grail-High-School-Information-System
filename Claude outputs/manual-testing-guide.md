# Grail SIS — Manual Testing Guide

> For a person clicking through the app in a browser. For the automated PHPUnit
> suite, see `project_documentation/tests.md` instead — this document doesn't
> replace it, it's for hands-on QA of what the suite doesn't (or can't) cover:
> real page rendering, JS behavior, and cross-feature flows.

Last updated: 2026-09-16, after a testing pass that found and fixed several
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
enrolled students **and**, as of 2026-09-16, a class with active
class-subject/teacher assignments — see §2g below for why the second check
was missing and what it broke), `admin.teachers.destroy` (blocks deleting a
homeroom teacher or one with active class-subject assignments),
`admin.parents.destroy` (blocks deleting a parent with children still linked)
— all verified live.
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

### f. "Mark as Cleared" / "Mark as Overdue" (bulk fee actions) — removed
Found while checking the Fee Collection Report, then removed entirely on
2026-09-16 rather than fixed in place. Both set a fee's status by hand
instead of through the state machine every other part of this app trusts as
the only source of truth for status:

- **`mark_cleared`** set `amount_paid`/`balance`/`status` directly, with no
  `Payment` row behind it — no receipt (the receipt feature reads from
  `Payment`), and the Fee Collection Report's "Collected" figure (which sums
  real `Payment` rows) silently undercounted against what the fee ledger
  claimed was paid. On this test database, 11 of 14 "Cleared" fees
  (ZMW 27,500) had zero `Payment` records behind them.
- **`mark_overdue`** didn't even do what it said: it reset `due_date` to
  today, which doesn't satisfy the "past due" check until the next day.
  Overdue detection already happens correctly and automatically via the
  nightly `fees:flag-overdue` scheduled command (`routes/console.php`),
  which goes through the real state machine — there was nothing this manual
  action did that wasn't already handled, and handled correctly, without it.

Both options are gone from the bulk-actions dropdown and rejected
server-side if posted directly. **Not retroactively fixed**: fees that were
already bulk-cleared before this change still have no `Payment` row behind
them, so the admin Fees index's "Total Collected" stat (sums `amount_paid`)
and the Fee Collection Report's "Collected" stat (sums `Payment.amount`)
will keep disagreeing for *that* historical data specifically — same root
cause, just no longer able to recur going forward.

The "Overdue Fees" stat box on the Fees index is now a working filter link
(click it → `?status=Overdue`) instead of a static count, with an active
state when that filter is applied.

### g. Dependency guards need to check *every* kind of dependency, not just the obvious one
Real crash, found 2026-09-16 on first teacher login of the session:
`ErrorException: Attempt to read property 'class_name' on null` at
`teacher/marks.blade.php:42`, on the default post-login page for any teacher.

Root cause: `admin.classes.destroy`'s guard (added earlier this session, see
§2b) only checked `$class->students()->exists()`. A class with **zero
students but an active teacher/subject assignment** (a `class_subjects` row)
sailed straight past that guard and got soft-deleted — which is exactly what
happened to class 8C: 0 enrolled students, but two teachers still had live
`class_subjects` rows pointing at it. The class vanished from every list, but
those rows didn't, so the moment either teacher's marks/attendance/
performance page tried to read `$assignment->schoolClass->class_name`, it
crashed on null.

**Fixed two ways** (matching this guide's own root-cause-first pattern):
1. `admin.classes.destroy` now also blocks when `$class->classSubjects()->exists()`
   — the same fix shape as the teacher/parent guards, just a dependency type
   that got missed the first time. **Lesson for future dependency guards on
   this codebase: enumerate every relation, not just the first one you think
   of** — `students()` was the obvious one for a *class*, but `classSubjects()`
   (teacher assignments) is just as real a dependency.
2. Defense in depth on the read side, since old dangling rows (like 8C's, which
   predated the guard fix and had to be restored by hand) can still exist:
   `TeacherController::marks()/performance()/attendance()` now filter out any
   `ClassSubject` whose `schoolClass` is null before it reaches the view, and
   `marks.blade.php`'s dropdown uses `?->`/`?? '—'` instead of unguarded
   property access. `finalizeGrades()`/`unfinalizeRequest()` now `abort_unless`
   on a null `schoolClass` too.

**Not yet checked:** whether the same "only checks the obvious dependency"
gap exists on any other `destroy()` guard in this app (e.g. subjects,
academic years) — worth a deliberate pass rather than assuming it's isolated
to classes.

### h. Leftover Laravel Breeze scaffold — unguarded self-service account deletion, found & removed 2026-09-16
This was the most severe finding of the session, more so than any of the
soft-delete gaps above. This app was scaffolded from Breeze; most of that
scaffold was properly replaced with role-specific controllers/views (e.g.
`parent.settings.update`, the public `/register` → `RegistrationRequest`
flow from an earlier phase of this session), but **`/profile`
(edit/update/destroy) was never removed**, and the Teacher portal's
"Profile Information" save form was still wired to it.

What this actually meant, live, before the fix:
- `routes/web.php` registered `GET/PATCH/DELETE /profile` behind nothing but
  `auth` — reachable by **any signed-in user of any role**, not just teachers.
- The `DELETE` route (`ProfileController::destroy`) hard-deletes the
  authenticated user's own `users` row after nothing more than re-entering
  their current password — **no dependency check of any kind**. Unlike every
  `destroy()` guard fixed elsewhere this session (classes, teachers, parents),
  this one doesn't check for *anything* — not homeroom assignments, not
  enrolled students, not fee/grade/attendance history, not even "are you the
  last admin." A teacher, parent, student, or admin could permanently orphan
  every record pointing at their own account with three clicks.
- Separately (the bug that actually surfaced this): the Teacher portal's
  Profile Information form posted to `profile.update`, which **always**
  redirects to `profile.edit` — so saving your name/email as a teacher kicked
  you out of the themed Teacher Portal into a raw, unstyled default-Breeze
  page, which is also where the Delete Account button lives. Confirmed live:
  saving on `/teacher/settings` landed on a page titled "Grail - School
  System" with "Update Password" and "Delete Account" sections, no sidebar,
  no branding.
- Student settings turned out **not** to be affected — that page is
  correctly read-only by design ("students cannot edit their own record");
  only its password form touches Breeze, and that one route
  (`password.update`) redirects with `back()`, which is safe.

**Fixed:**
- Added `TeacherController::updateSettings()` + `PATCH teacher/settings`
  (`teacher.settings.update`), matching `ParentController::updateSettings()`'s
  existing pattern exactly (validates name/email, `Rule::unique('users',
  'email')->ignore(...)`, redirects back to `teacher.settings`). Teacher's
  Profile Information form now posts there instead of `profile.update`.
- Removed the dead/dangerous scaffold entirely rather than leaving it as an
  unused-but-reachable route: `ProfileController`, `ProfileUpdateRequest`,
  the `/profile` route group, `resources/views/profile/edit.blade.php` +
  its `update-profile-information-form`/`delete-user-form` partials, and
  `resources/views/layouts/navigation.blade.php` (confirmed dead — not
  extended by any view; `layouts/app.blade.php` never included it, so the
  live nav teachers/admins actually see never linked to `/profile` either).
  **Kept** `profile.partials.update-password-form` and the `password.update`
  route — still legitimately used by both Teacher and Parent settings pages,
  and safe (`back()` redirect, no destructive action).
- Verified live: `/profile` now 404s for every role, the teacher settings
  save stays on `/teacher/settings` with a proper success toast, and the
  underlying `users` row is unaffected by the fix (re-saving identical
  values correctly left `updated_at` untouched — no spurious writes).

**Lesson for future sweeps on this codebase:** when a Laravel app is
scaffolded from Breeze/Jetstream and then customized per-role, explicitly
check whether the generic scaffold routes/controllers were actually removed
or just no longer *linked* from the main nav — "unlinked" and "unreachable"
are not the same thing when the route only requires `auth`, and the Breeze
delete-account flow in particular is far more dangerous in a records-of-truth
SIS than in Breeze's own todo-app-tutorial origins.

### i. Duplicate flash toasts — pages still carrying their own local toast alongside the shared `<x-flash>`, found & removed 2026-09-16
`components/flash.blade.php` is this app's single, deliberately unified flash
component — its own header comment explains it replaced four separate
ad-hoc toast implementations. That consolidation was never fully finished:
five views still had their own local copy left in place, so a save on any of
them showed **two** success messages at once (confirmed live on Teacher
Settings' password change and Parent Settings' both forms — two
"...updated successfully" toasts stacked on screen).

**Found and removed** (kept the shared `<x-flash>` as the only source of
truth in every case):
- `teacher/settings.blade.php` — a full custom top-right toast `<div>` +
  inline `<script>` (`session('status')`), entirely redundant with
  `<x-flash>` already rendered by `layouts.teacher`.
- `student/settings.blade.php` — a small inline "Saved." span next to the
  password button (`session('status')`), same redundancy, smaller blast
  radius.
- `profile/partials/update-password-form.blade.php` — an Alpine.js inline
  "Saved." indicator (`session('status')`), `@include`d by Parent Settings.
- `parent/settings.blade.php` — its own inline `session('notification')`
  check next to the Profile "Save Changes" button.
- `admin/students/edit.blade.php` and `admin/teachers/create.blade.php` —
  each had a full custom bottom-right toast (`#toast` div + a `showToast()`
  JS function) that read `session('notification')`/`session('error')` on
  page load, on top of `<x-flash>` already included via
  `admin.partials.flash`. **Care taken on `admin/teachers/create.blade.php`
  specifically:** its `showToast()` function is also used for a genuine
  client-side-only "Save Draft" feature with no server round-trip — that
  function and its HTML were *kept*, only the two `@if (session(...))
  showToast(...)` calls that duplicated `<x-flash>` were removed.

**Verified live after the fix:** Teacher Settings (profile save, password
change), Parent Settings (profile save), and Admin → Students → Edit (save)
each now show exactly one toast. Admin → Teachers → Create's "Save Draft"
button still shows its own toast correctly (that feature was untouched).

**Deliberately not touched:** `auth/verify-email.blade.php` and
`auth/forgot-password.blade.php` also check `session('status')` locally, but
`<x-guest-layout>` never includes `<x-flash>` — those are the *only* message
on their pages, not duplicates.

**Separately noticed while in this area, not fixed:** `User` doesn't
implement `MustVerifyEmail`, so the entire email-verification flow
(`verify-email`, `verification.send`, `verification.verify` routes) is inert
scaffold — the `verified` middleware no-ops for a model that isn't
`MustVerifyEmail`. Same category of leftover-Breeze issue as §2h, but inert
rather than dangerous (no live route touches it the way `/profile` did), so
it was left alone rather than expanding this cleanup pass further.

### j. Cascade-delete foreign keys make a missing dependency guard *destructive*, not just orphaning — found & fixed 2026-09-16
Every dependency-guard gap fixed earlier in this guide (§2b, §2g) was about
*orphaning* — a reference left pointing at something invisible, causing a
crash somewhere else. Subjects and the academic calendar are a sharper
version of the same root cause: several of their foreign keys are declared
`cascadeOnDelete()` in the migrations, so a missing guard doesn't orphan
data, it **silently and permanently destroys it**, several tables deep, with
nothing but a generic confirm() dialog standing in the way.

- **`Admin\AdminSubjectController::destroy()` had no guard at all.**
  `class_subjects.subject_id` cascades on delete, and `Attendance`, `Grade`,
  `Assignment` and `ReportCardComment` all cascade off `class_subjects` in
  turn — deleting a Subject that's offered in any class would have wiped
  every grade, attendance record, assignment and report-card comment ever
  entered for that class's teaching of it. Found by reading the migrations
  after noticing a delete on an unoffered QA test subject silently didn't
  fire (see the automation note below) — the cascade chain was traced and
  fixed *before* ever attempting the delete against real, offered data like
  Biology, rather than discovering it by breaking something. **Fixed:**
  blocks delete when
  `classSubjects()`, `teachers()` (the independent `teacher_subjects` table,
  also cascades), or `timetableSlots()` (`restrictOnDelete` — would’ve
  surfaced as a raw 500 instead) exist. Verified live: blocked on Biology
  (1 class offering it, data untouched), succeeded on an unoffered QA test
  subject.
- **`Admin\AcademicYearController::destroy()` only checked its own direct
  `grades()`/`fees()`, not `terms()`.** `terms.academic_year_id` cascades on
  delete, and a term's own `report_cards`/`report_card_comments` cascade off
  *that* — so a freshly-created academic year with terms defined (dates set
  up, no grades entered yet) could have its terms silently destroyed, along
  with any report cards those terms already carried, without the existing
  guard ever noticing. **Fixed:** now also blocks when `terms()->exists()`.
  Verified live: blocked deleting a fresh year with one empty term attached;
  succeeded once the term was removed first.
- **`Admin\TermController::destroy()` had the identical gap one level
  down.** Checked `grades()`/`fees()` (fees is `nullOnDelete`, harmless
  either way) but not `report_cards.term_id` (`cascadeOnDelete`) or
  `timetable_slots.term_id` (`restrictOnDelete`). **Fixed:** added a
  `reportCards()` relation to the `Term` model and guards on both.

**Lesson for future dependency guards on this codebase:** before writing a
guard, check the migration for that foreign key's `onDelete` behavior, not
just whether a relation exists. `nullOnDelete` needs no guard (the data
survives, just loses the reference). `restrictOnDelete` needs a guard only
to turn a raw DB crash into a friendly message. `cascadeOnDelete` needs a
guard because the dependent rows do not survive — treat every
`cascadeOnDelete()` in a migration as a live list of "what this destroy()
must check before deleting."

**Testing-automation note:** every delete confirmation in this app runs
through a JS `confirm()` dialog, which the browser automation used for this
sweep auto-dismisses as cancelled by default — so a delete button appearing
to "do nothing" when clicked is expected, not a finding. Override with
`window.confirm = () => true` before clicking (same for `window.prompt` on
the unfinalize-request flow, §3) before concluding a delete path is broken.

### k. Students list (`/admin/students`) — fake bulk actions and a broken `->parent` relation, found & fixed 2026-09-16
- **Bulk "Delete" did nothing to the database.** The button lived inside the
  page's `GET` filter `<form>` (`type="button"`, no `action`), and its click
  handler only faded out the selected `<tr>` elements client-side —
  `checked.forEach(cb => { ...row.remove() })`, no `fetch`, no form submit —
  behind a confirm dialog that explicitly said *"This action cannot be
  undone."* Refreshing the page brought every "deleted" student straight
  back. Bulk "Transfer" and "Archive" were permanently `disabled` with no
  handler at all — inert, not deceptive, but also not real. **Fixed:**
  removed the entire bulk-actions toolbar and its row-checkbox column rather
  than build real multi-select transfer/archive/delete from scratch in a
  testing pass — the same treatment as the §3 System Settings mockup.
- **The individual per-row delete button, by contrast, was already real**
  (`<form action="{{ route('admin.students.destroy', ...) }}">`, proper
  `@csrf`/`@method('DELETE')`) — but a leftover JS click handler stacked a
  *second*, differently-worded `confirm()` in front of the form's own, plus
  a pointless fade-and-remove animation that raced with the real page
  navigation the POST triggers. Removed the redundant JS; the form's own
  `onsubmit` confirm now does the whole job, reworded to name the student
  and state the consequence plainly. Verified live end-to-end: created a QA
  test student, deleted it through this button with the confirm overridden,
  confirmed via tinker it was actually (soft-)deleted, not just hidden.
- **The "Parent / Guardian" column showed "N/A" for every student, including
  ones with a real linked parent.** `Student::with('schoolClass', 'user')`
  never loaded a `parent` relation, and — more to the point — `Student` has
  no `parent()` relation method at all, only `parentUser()`/`guardian()`
  (both `belongsTo(User::class, 'parent_user_id')`). The blade called
  `$student->parent?->full_name`, which Eloquent resolves to a silent `null`
  for a nonexistent relation rather than an error, so it always fell through
  to "N/A" no matter what the data said. Confirmed live: `Student::find(1)`
  (Demo Student, `parent_user_id = 19`) — `->parent` returned `null`,
  `->guardian->name` correctly returned "Demo Parent". **Fixed:** query now
  eager-loads `guardian`, and the blade reads `$student->guardian?->name`
  (also matches what the working Financials page already used). **Grep
  turned up no other view or controller referencing `->parent` on a
  `Student`** — this looks isolated to this one page.

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
| Students | `/admin/students` | Create (with and without a login email — one-time password flow verified), search/filter, individual delete (real, guard-checked, verified end to end). **Found & fixed this session — see §2k:** bulk actions were fake (Delete did nothing to the DB despite claiming to be permanent; Transfer/Archive were inert placeholders) and the Parent/Guardian column always showed "N/A" due to a nonexistent `->parent` relation. Bulk toolbar removed; guardian display fixed. CSV export and the class/grade/status/gender filter combinations not independently re-verified this pass. |
| Fees — bulk select | `/admin/fees` | **Fixed this session**: per-row Delete buttons were nested inside the bulk-select form, which broke both the individual delete button *and* silently excluded every row after the first from bulk actions (Mark Cleared/Overdue, Send Reminder, Export, Delete Selected). Re-verify: check boxes on two different rows, confirm a bulk action includes both. |
| Teachers | `/admin/teachers` | Create (temp password flow), edit, assign subject to class (test the "already taught by X, confirm handover" prompt), unassign, delete (blocked if homeroom teacher or has class-subjects — verified), email reusable after a dependency-free delete (verified). |
| Parents | `/admin/parents` | Create (temp password flow), edit — linked-children checkboxes save correctly (fixed & verified), reset password, delete (blocked if children still linked — verified). |
| Classes | `/admin/classes` | Create/edit with subjects checked but no teacher (should succeed, show "No teacher assigned"), delete guard (blocked when students enrolled), duplicate name rejected, name reusable after a dependency-free delete. |
| Subjects | `/admin/subjects` | Tested — create, duplicate-name rejection, edit, delete. **Found & fixed a severe bug this session — see §2j:** deleting a subject offered in any class had no guard and would have cascaded through the database to permanently destroy every grade, attendance record, assignment and report-card comment for that class's teaching of it. Now blocked (verified against Biology, 1 class offering it — correctly blocked, data untouched) while an unoffered subject still deletes cleanly (verified). |
| Grade Levels | `/admin/grade-levels` | Create, edit, delete — tested, clean (already had a proper dependency guard, hard-deletes so no soft-delete/uniqueness risk). |
| Academic Years | `/admin/academic-years` | Tested — create, delete guard (grades/fees/current-year, all pre-existing and verified). **Found & fixed this session — see §2j:** the guard never checked for terms, so a year with terms defined but no grades yet (a realistic state for a freshly-set-up year) could cascade-destroy those terms and anything already on them. Now also blocked when `terms()->exists()` — verified live against a throwaway year+term, then a clean delete once the term was removed first. |
| Terms | `/admin/terms` | Tested — create, overlap validation (rejected an overlapping Term 3 date range, verified), edit, delete guard (grades/fees, pre-existing). **Found & fixed this session — see §2j:** same shape of gap as Academic Years, one level down — `report_cards`/`report_card_comments` cascade off a deleted term, and a timetable slot would have hard-crashed the delete instead of failing cleanly. Both now guarded. Note: the index defaults to the *current* academic year — a term you just added under a different year won't visibly appear until you switch the year filter (confirmed still true, not a bug). |
| Holidays | `/admin/holidays` | Create, edit, delete — confirm the affected term's "school days" count on the Terms page recalculates. |
| Periods | `/admin/periods` | Create/edit/delete, scoped per grade level. |
| Timetable | `/admin/timetable` | Assign a subject+teacher to a day/period cell, save. Try assigning a teacher who's already booked elsewhere at that time — should be rejected with a clear conflict error and nothing written. "Copy to Term" (duplicates a class's whole week into another term) and "Clear" (wipes one). |
| Fees | `/admin/fees` | Create a fee (with line items), edit, record a payment (including overpayment → check it becomes account credit on the student), send reminder, delete. Payment lookup by reference. |
| Payment Submissions | `/admin/payment-submissions` | Review queue (pending/approved/rejected tabs), approve (creates a real `Payment`, updates fee balance), reject (requires a reason, notifies the parent, no accounts/payments created). |
| Registration Requests | `/admin/registration-requests` | Review queue, approve (creates parent + child `User` + `Student`, child gets a temp password, parent's own chosen password carries over unchanged), reject (no accounts created, applicant notified with the reason). **Found & fixed 2026-09-16 (reported live from a separate clone of this codebase):** `parent_national_id` was the one field on the public sign-up form (`StoreParentRegistrationRequest`) that got no uniqueness check, even though `parents.national_id` is a nullable-but-unique column and every sibling field (`parent_email`, `child_email`) already had one, including a check against other still-*pending* requests. Two requests with the same national ID both passed submission validation, and approving the second one crashed with a raw `UniqueConstraintViolationException` instead of a validation error. Fixed at both ends: the sign-up form now validates uniqueness the same way email does, and `RegistrationRequestController::approve()` re-checks immediately before the transaction (catches a request submitted before this fix, or two pending requests that both slipped through) — verified live: a colliding approval now shows "Another parent already has national ID ... on file," the request stays Pending, and no `User` rows are created (transaction never starts). |
| System Settings | `/admin/settings` | **Found & fixed this session.** This page was a non-functional mockup — "School Profile" fields, a "Save Changes" button, "Security & Access" toggles (2FA, session timeout), and three dead nav buttons (Academic Settings, Roles & Permissions, Notifications) were all static HTML with no `<form>`, no route, nothing persisted. Rebuilt as a simple, honest hub linking to the two settings areas that actually exist and work (Payment Details, Fee Categories — the two rows below). If any of the removed sections (school profile fields, 2FA, session timeout, roles/permissions) become a real requirement, they need an actual settings table/controller behind them, not a re-add of the static markup. |
| Fee Categories | `/admin/settings/categories` | Create, inline rename, delete — tested, clean apart from the uniqueness/soft-delete gap (fixed, see §2c). |
| Payment Instructions | `/admin/settings/payments` | Tested — edits correctly propagate to the parent-facing "How to pay" panel. Clean. |
| Promotions | `/admin/promotions` | Tested — promote/retain/graduate mix, rollback (restores students to their exact prior class), and mapping overrides (default outcome updates correctly) all verified. Graduate outcome specifically tested end to end with a throwaway student: `class_id` cleared, `status` → Graduated, `graduated_on` stamped, and — the part that needed its own login to verify — `User.is_active` correctly flipped to `false` (portal access actually removed, not just a status label). Rollback tested against the same batch: class, status, and login access all correctly restored. Clean. |
| Announcements | `/admin/announcements` | Tested — create with class-targeted audience, delivery to the right parent portal, mark-read, delete. Clean. |
| Report Cards | `/admin/report-cards` | Preview/download tested and clean (figures cross-check correctly against the term's holiday-adjusted school-day count). Unfinalize tested end-to-end (teacher finalizes → requests unlock with a reason → admin unfinalizes with their own reason → both reasons correctly land in Audit Logs, ranks clear, status reverts to Draft) — clean. **Minor gap, not a bug:** the teacher's unfinalize-request reason isn't surfaced anywhere on this admin page itself — an admin has to already know to check Audit Logs (filter model=SchoolClass, action=Unfinalize_requested) to see *why* a teacher asked. Every other request-style workflow in this app (Payment Submissions, Registration Requests) has a dedicated review queue; this one doesn't. Worth a small UX fix if unfinalize requests turn out to be common in practice. |
| Reports & Analytics | `/admin/reports/*` | Fee collection, aging, attendance, school-wide performance, and the school-wide pass-threshold setting all render correctly with sensible numbers. **Found a real finding, not a crash** — see "Known issues" below: the Fee Collection Report's "Collected" figure only counts real `Payment` records, but the bulk "Mark as Cleared" action on the Fees list sets a fee to paid-in-full without creating one, so fees cleared that way go uncounted in "Collected" while still counting as "Cleared" in the status breakdown. CSV exports not independently re-verified against their on-screen counterparts. |
| User Accounts | `/admin/users` | Filter by role/status, reset password (fixed this session — was silently failing with a wrong HTTP method), change role (check the "last active admin" guard), activate/deactivate. |
| Audit Logs | `/admin/audit-logs` | Confirm actions taken above actually produce entries here with sensible before/after values, and that `password`/`remember_token` never appear in them. |
| Student financials/statement | `/admin/students/{id}/financials`, `/statement` | **Not yet manually tested this session**. |

---

## 4. Teacher portal (`/teacher/...`, requires `role:teacher`)

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/teacher/dashboard` | Tested — KPI tiles, teaching schedule table, recent activity, pending instructional tasks all render correctly. Clean. |
| My Classes | `/teacher/classes`, `/teacher/classes/{class}/roster` | Both tested — index (grade/subject/term filters, per-class stat cards) and roster (enrolled count, class average/attendance, per-subject teacher list, student table). Clean. |
| Marks entry | `/teacher/marks` | **Crashed on load this session — fixed, see §2g.** Retested after fix: loads, class/subject picker, term selector, score entry with live grade calc all work. Save not yet exercised this pass. |
| Attendance | `/teacher/attendance` | Tested — load class, roster with per-student P/L/A/E marking, "Mark All Present," save & finalize all work. Not cross-checked against admin/parent/student attendance-rate figures for agreement this pass. |
| Assignments | `/teacher/assignments/*` | Full loop tested: create (published, 100 marks, due date), a student submits, teacher grades from the Submissions screen, student sees the score/feedback and the submission correctly locks ("Graded work can no longer be changed"). Clean end to end. |
| Performance / Report Cards | `/teacher/performance`, `/teacher/report-cards/*` | Full finalize loop tested: finalize a class (ranks assigned correctly by average, marks locked, comments still editable) → request unlock with a reason (`window.prompt`, min 5 chars enforced) → confirmed on the admin side (§3) → re-finalized to restore original state. Clean. |
| Student profile | `/teacher/students/{student}` | Tested — all four tabs (Personal Info, Academic Records, Attendance, Parent Details). Clean. |
| Settings | `/teacher/settings` | **Found & fixed a serious bug this session — see §2h.** The "Profile Information" save form was wired to a leftover, unguarded Laravel Breeze route that (a) kicked the teacher out of the portal into a raw unstyled page on every save, and (b) exposed a no-dependency-check **hard delete of their own account** to any signed-in user of any role via `/profile`. Fixed: teacher settings now has its own `teacher.settings.update` route (mirrors the parent settings pattern), and the entire dead `/profile` scaffold was removed. Retested — save now stays on `/teacher/settings` with a proper toast. Password change tested and works (verified by changing then reverting), and no longer double-toasts (see §2i). |
| Timetable | `/teacher/timetable` | Tested — week view across all rostered classes, period grid, weekly load/roster summary. Clean. |

---

## 5. Parent portal (`/parent/...`, requires `role:parent`)

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/parent/dashboard` | Tested — overdue-fee banner, per-child summary card. Clean. |
| My Children | `/parent/children`, child switcher | Tested — per-child stat card (fees/attendance/GPA/balance). Clean. |
| Attendance | `/parent/attendance` | **Found & fixed this session.** The top summary stats (rate/punctual/absent-late) are deliberately scoped to the *current term only* (an earlier fix aligned them with the report card's own methodology — see the comment in `ParentController::attendance()`), but the "records" badge and History table below were unlabeled and showed **all-time** data — e.g. summary said "10 records," the table right below it paginated 147. Fixed: the badge now reads "10 records this term · Term 3," a "THIS TERM · TERM 3" caption sits above the stat cards, and the History table caption now says "every term on record" — the numbers were always correct, they just weren't legible as two different scopes on one screen. |
| Performance | `/parent/performance` | Tested — GPA, class rank, subject mastery bars, full assessment table. Clean. |
| Reports | `/parent/reports` | Tested — year/term filter, finalized report card card (average, subjects, attendance, position, View/PDF). Consistent with the Teacher-portal finalize test done earlier (position 1st of 4). Clean. |
| Fees | `/parent/fees` | "How to pay" panel (bank/mobile-money instructions + reference code), **submit proof of payment** (tested — works), payment history, receipts. Clean. |
| Assignments | `/parent/assignments` | Tested — read-only view of child's assignments (pending/overdue/submitted/graded counts). Clean. |
| Timetable | `/parent/timetable` | Tested — read-only weekly schedule, term selector. Clean. |
| Announcements | `/parent/announcements` | Tested — mark read / mark all read. Clean. |
| Settings | `/parent/settings` | **Found & fixed a real bug this session.** `ParentController::updateSettings()` validated the email field's *format* but not its *uniqueness* — saving an email already used by another account crashed with a raw 500 (`UniqueConstraintViolationException`) instead of a clean validation message, because only the DB-level unique constraint caught it. This is the same class of gap as §2c, just in a self-service settings controller rather than an admin CRUD form. Fixed by adding `Rule::unique('users','email')->ignore($user->id)` to the validation, matching the pattern used everywhere else in this codebase. Verified: duplicate email now shows "The email has already been taken," a legitimate save still works, and the parent's data was never actually corrupted (the DB constraint held even before the fix — this was a crash/UX bug, not a data-integrity one). Password change reuses the same safe shared route already verified for Teacher (§2h), and its own double-toast on both Profile and Password save was cleaned up — see §2i. |

---

## 6. Student portal (`/student/...`, requires `role:student`)

| Area | Route | What to verify |
|---|---|---|
| Dashboard | `/student/dashboard` | Tested — term average, all-time attendance ("147 days recorded" — deliberately all-time, unlike the Attendance page below, and it says so in plain language rather than needing a term label), assignments due, fee balance, recent results, today's schedule. Clean. |
| Results | `/student/results` | Tested — all-terms and per-term filter, per-subject CA/EXAM/overall breakdown. Matches the same figures on Teacher/Parent Performance pages exactly. Clean. |
| Attendance | `/student/attendance` | Tested — defaults to current term (`Term::current()`), correctly labelled via the page subtitle ("Term 3 · 2026") and a working term filter including "All time." **Checked specifically for the same unlabeled-scope bug just found and fixed on the Parent portal (§5) — this page does NOT have it,** the term is clearly shown. Clean. |
| Report Cards | `/student/report-cards*` | Tested — only finalized cards are offered (by design), average/position/View/PDF. Consistent with Teacher/Parent. Clean. |
| Assignments | `/student/assignments/*` | Full loop tested via the Teacher-portal assignment sweep (§4): student opened an assignment, submitted with a note, saw it graded, and confirmed the submission correctly locks ("Graded work can no longer be changed"). Clean. |
| Timetable | `/student/timetable` | Tested — read-only weekly schedule, term selector. Clean. |
| Settings | `/student/settings` | Tested — read-only student details (by design; "students cannot edit their own record"), password change (verified by changing then reverting). Clean, single toast. |
| Announcements | `/student/announcements` | Tested — unread count, mark read / mark all read. Clean. |

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
