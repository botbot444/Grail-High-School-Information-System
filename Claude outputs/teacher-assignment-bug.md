# Teacher subject/class assignment — what was broken and what changed

**For:** Lazarus
**From:** Silas
**Date:** 14 Sep 2026
**Touches:** `AdminTeacherController`, `admin/teachers/show.blade.php`, `routes/web.php`, `UserAccountController`, `admin/announcements/index.blade.php`

---

## The bug in one line

A teacher created through the admin portal could never teach anything, because the create form wrote to a table nothing reads.

## Root cause

There are two different tables that both look like "teacher teaches subject":

| Table | Written by | Read by |
|---|---|---|
| `teacher_subjects` | the admin teacher create/edit form | **nothing**, except one counter on the subjects page |
| `class_subjects` | **nothing in the app** — only `ClassSubjectSeeder` | marks, attendance, assignments, report cards, class performance, timetable |

`Teacher::subjects()` is a `belongsToMany` through `teacher_subjects`. The form called `$teacher->subjects()->sync($subject_ids)`, so ticking subjects wrote rows there and nowhere else.

Everything the teacher portal does resolves through `ClassSubject::where('teacher_id', ...)`. A teacher with no `class_subjects` rows signs in to an empty portal: no classes, no marks to enter, no attendance, nothing to grade.

Confirmed against the live database before changing anything:

```
class_subjects   : 70 rows  (all 10 seeded teachers, 7 each — every one from the seeder)
teacher_subjects :  0 rows  (the table the form writes to)
```

Nowhere in `app/` or `database/seeders/` — outside `ClassSubjectSeeder` — does anything create a `class_subjects` row.

## What changed

**`AdminTeacherController::store()` / `update()`** now create real `class_subjects` rows. The form gives a flat list of classes and a flat list of subjects, so the pairing is every ticked subject in every ticked class. A pair another teacher already holds is **skipped, never stolen**, and the admin is told which ones were left alone and who has them.

`teacher_subjects` is left alone — the subjects-page counter still reads it. It should probably be dropped once that counter is rewritten.

Assignments are only ever **added** by the edit form. Removing one is done on the teacher's own page, where the consequences are visible — unticking a box shouldn't silently strand marks.

**New teacher page** at `admin/teachers/show.blade.php`, rebuilt from the old 3KB stub. Profile, login account, homeroom, timetable, and a teaching-assignments table with add/remove:

- `POST admin/teachers/{teacher}/assignments` → `teachers.assign`
- `DELETE admin/teachers/{teacher}/assignments/{classSubject}` → `teachers.unassign`

Removing is refused when marks, attendance or assignments already reference the row — the page shows the counts and says to hand it over instead.

**Passwords.** `store()` was hardcoding `Teacher@1234` for every teacher. It now generates a readable one-time password (no I/O/L), shows it once via the existing flash partial, and sets `must_change_password` so your `EnsurePasswordIsChanged` middleware forces them to pick their own at first login. `UserAccountController::resetPassword()` now sets that flag too — it wasn't, so a "temporary" password was permanent.

## What your class/subject CRUD needs to do

Creating a class or a subject does **not** make anything teachable. The pair has to exist in `class_subjects`, so the curriculum step is: *this class studies this subject, taught by this teacher.*

Constraints to build against:

- `unique(class_id, subject_id)` — a subject is taught once per class. Assigning a pair someone already holds is a **hand-over**, not a duplicate row.
- `class_subjects.teacher_id` is `restrictOnDelete` — a teacher with live rows can't be deleted until they're reassigned.
- `grades`, `attendances` and `assignments` all point at `class_subject_id`. Deleting a pair with records either fails on the FK or strands the data.
- `school_classes.teacher_id` is the **homeroom** teacher — a different thing from teaching a subject. Don't conflate them.

If your pages create `class_subjects` rows, say so and I'll drop the cross-product fallback from the teacher form.

## Two other things found on the way

**`Str::` isn't aliased in this app.** `config/app.php` has no `aliases` key, and Laravel 11+ only registers what's in that key plus package aliases. `admin/announcements/index.blade.php` used `{{ Str::limit(...) }}` and would have thrown `Class "Str" not found` the first time anyone opened the announcements list. Fixed by fully qualifying it. Worth grepping for other bare facade aliases in Blade.

**Homeroom assignment steals silently.** `SchoolClass::whereIn(...)->update(['teacher_id' => ...])` in `store()`/`update()` reassigns a class even if another teacher is already its form teacher, with no warning. Left as-is — it's your area right now — but it should probably warn the way the subject hand-over does.

## Files I touched

```
app/Http/Controllers/Admin/AdminTeacherController.php   rewritten
app/Http/Controllers/Admin/UserAccountController.php    one line (must_change_password)
resources/views/admin/teachers/show.blade.php           rebuilt
resources/views/admin/announcements/index.blade.php     one line (Str::)
routes/web.php                                          two routes added after the teachers resource
```

Nothing in `admin/teachers/create.blade.php` or `edit.blade.php` — the fix is entirely in the controller, so your forms are untouched.

## Not verified

None of this has been run. No PHP available in the shell this was written from: the files are syntax-checked, the Blade balances, every route name and view variable resolves, and the assignment logic was simulated against the live SQLite data — but nothing has rendered in a browser.
