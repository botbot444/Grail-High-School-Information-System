# Grail — Implementation Plan

**Purpose:** bring the existing codebase into conformance with the System Design Specification, and reconcile the specification itself where it no longer matches reality (Tailwind vs Bootstrap).

**Sequencing principle:** desktop-first. The spec's mobile-first rationale (§4.1.3) still holds for the eventual deployment target, but day-to-day development and demos happen on desktop, so every phase below targets a working desktop experience before a mobile-responsiveness pass is applied. Mobile and offline-PWA work are pushed to the end, not dropped.

Each phase ends with an **exit checklist** — don't move to the next phase until every item passes. Skipping this is how a spec and a codebase quietly drift apart again.

---

## Portal feature checklists (from the spec's Use Case Diagram + FR sections)

Status legend: ✅ done · 🟡 partial · ❌ missing (per the current PROJECT_DOCUMENTATION.md snapshot)

### Admin portal

| Feature                                        | Spec ref                                 | Status                                                                                                                                                                        |
| ---------------------------------------------- | ---------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Register / edit / soft-delete students         | FR-4, FR-5                               | ✅ `admin.students.*` CRUD exists                                                                                                                                             |
| Manage staff (teacher) records                 | Use case: Manage Staff Records           | ✅ `AdminTeacherController`                                                                                                                                                   |
| Manage parent records                          | —                                        | ✅ `AdminParentController`                                                                                                                                                    |
| Class & subject management, teacher allocation | FR-6                                     | ✅ `AdminClassController`, `AdminSubjectController`                                                                                                                           |
| Manage user accounts                           | Use case: Manage User Accounts           | 🟡 exists implicitly via student/teacher/parent CRUD — no dedicated account/role management screen                                                                            |
| Record fee payment                             | FR-11                                    | ✅ `Fee::recordPayment()` exists — needs UI confirmation                                                                                                                      |
| Fee reporting (school-wide + per-student)      | FR-12                                    | 🟡 dashboard KPI present but bugged (Phase 1 fix)                                                                                                                             |
| Generate printable report cards                | FR-13                                    | 🟡 `barryvdh/laravel-dompdf` installed — confirm a route/view actually calls it                                                                                               |
| ~~View school-wide performance report~~        | Use case: View School Performance Report | **Scrapped** — descoped by decision, not being built. Note as a deliberate deviation in the spec's use case diagram (Figure 3) rather than leaving it silently unimplemented. |
| Settings screen                                | —                                        | ✅ `admin/settings.blade.php` (from prototype)                                                                                                                                |
| Examinations overview                          | —                                        | ✅ `admin/examinations.blade.php` (from prototype)                                                                                                                            |

### Teacher portal

| Feature                                        | Spec ref | Status                                                             |
| ---------------------------------------------- | -------- | ------------------------------------------------------------------ |
| Record class attendance (single screen, P/A/L) | FR-7     | ✅ `TeacherController::storeMarks` handles both marks + attendance |
| Enter examination marks / CA scores            | FR-9     | ✅ same controller, validated against `max_score`                  |
| View class performance summary                 | Use case | ❌ not present — no aggregate view for a teacher's classes         |
| View student profile (from teacher's context)  | Use case | ❌ not present                                                     |
| Attendance history / reporting                 | FR-8     | 🟡 data is recorded; no report-by-date-range view yet              |

### Parent portal

| Feature                                                       | Spec ref                   | Status                                                                                                                                                                     |
| ------------------------------------------------------------- | -------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| View all enrolled children (child switcher, not single-child) | Scope decision (see below) | ❌ not addressed in spec or original code — now confirmed **in scope**; parent portal must support 1..n children per parent, not just the one shown in the Figure 8 mockup |
| View each child's attendance                                  | FR-8, Use case             | ❌ only `parent/dashboard.blade.php` placeholder exists                                                                                                                    |
| View each child's academic results                            | FR-10                      | ❌                                                                                                                                                                         |
| View each child's fee balance                                 | FR-12                      | ❌                                                                                                                                                                         |
| View each child's fee payment history                         | FR-12                      | ❌                                                                                                                                                                         |
| Report card summary (per child)                               | Figure 8 mockup            | ❌                                                                                                                                                                         |
| School announcements                                          | Figure 8 mockup            | ❌ — confirmed **in scope**, not just illustrative                                                                                                                         |

### Student portal

| Feature                          | Spec ref          | Status                                                   |
| -------------------------------- | ----------------- | -------------------------------------------------------- |
| View personal results / CA marks | FR-11 (user req.) | ❌ only `student/dashboard.blade.php` placeholder exists |
| View personal attendance history | Use case          | ❌                                                       |
| View / download report card      | Use case          | ❌                                                       |

---

## Phase 0 — Environment: move dev DB to MySQL

Current: SQLite (`database/database.sqlite`). Target: MySQL via XAMPP, matching the spec's Tier 3 (§4.4.1, Table 6) and avoiding the "npm can't find the DB without XAMPP running" failure mode.

- [ ] Start XAMPP, enable the MySQL module, confirm it's reachable on `127.0.0.1:3306`
- [ ] Create a new schema, e.g. `grail_dev`, via phpMyAdmin or `mysql -u root`
- [ ] Update `.env`:
    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=grail_dev
    DB_USERNAME=root
    DB_PASSWORD=
    ```
- [ ] `php artisan config:clear`
- [ ] `php artisan migrate:fresh --seed` against the new MySQL schema
- [ ] Update `.env.example` and §14 of `PROJECT_DOCUMENTATION.md` to show MySQL as the default, not SQLite

**Exit checklist before Phase 1:**

- [ ] `php artisan migrate:status` shows all 14 migrations run against MySQL with no errors
- [ ] All 11 seeders complete without FK-constraint errors (MySQL enforces FK rules more strictly than SQLite — this is the most likely place for a silent SQLite-only assumption to surface)
- [ ] App boots and logs in as each of the 4 seeded roles (admin/teacher/parent/student) against the MySQL DB
- [ ] `.env.example` and doc updated so a teammate can reproduce this without asking you

---

## Phase 1 — Fix the known Fee status bug

- [x] In `AdminController::dashboard`, replace `Fee::where('status', 'paid')` with `Fee::where('status', 'Cleared')`, or add/use a `scopeCleared()` on `Fee`
- [x] Re-seed and confirm the admin dashboard fee KPI now reflects seeded data correctly

**Status (2026-08-05):** Completed. The `Fee::cleared()` scope was applied across admin/parent/student controllers and the student financials view; the database was re-seeded using XAMPP's MySQL and the admin dashboard fee KPI now reports non-zero cleared fees as expected.

**Exit checklist before Phase 2:**

- [x] Admin dashboard fee KPI shows a non-zero, correct count against seeded `Cleared` fees
- [x] No other controller references the literal string `'paid'` (grep confirmed)
- [x] A quick manual test: recording a payment that brings a fee to `Cleared` causes it to appear in the KPI without re-seeding

---

## Phase 2 — Desktop-first: close the role-dashboard gap

Build every ❌ and 🟡 item from the Teacher/Parent/Student tables above, desktop layout only, no breakpoint work yet.

- [ ] **Parent portal** — port `Frontend/ParentViews/index.html` into Blade, wired to real data:
    - [ ] Child switcher / selector for parents with more than one enrolled child — this changes the data-fetching pattern from "the parent's child" to "the parent's _selected_ child," so build it first, not as an afterthought
    - [ ] Attendance summary (per selected child)
    - [ ] Academic results (latest grades, subject breakdown, per selected child)
    - [ ] Fee balance + full payment history (per selected child)
    - [ ] Report card summary with link to full report card (reuse Phase-3-adjacent PDF work if ready, otherwise stub the link)
    - [ ] School announcements section (per Figure 8) — confirm with the rest of the group whether announcements are authored by admin somewhere, since there's no `Announcement` model/migration yet; this likely needs its own small migration + admin CRUD before the parent-facing view has anything real to show
- [ ] **Student dashboard** — read-only views:
    - [ ] Personal results / CA marks
    - [ ] Personal attendance history
    - [ ] Report card (view/download)
- [ ] **Teacher side**:
    - [ ] Visual pass on `marks.blade.php` to match admin's styled prototypes
    - [ ] New: class performance summary view (aggregate marks/attendance per class)
    - [ ] New: student profile view accessible from a teacher's class roster
- [ ] **Admin**:
    - [ ] Confirm report-card generation is actually wired to a route/button, not just a library import
    - [ ] New: `Announcement` model + migration (title, body, published_at at minimum) and a simple admin CRUD/authoring screen — nothing in the current schema supports the parent-facing announcements feature yet, so this has to land before Phase 2's parent portal can show real announcements rather than placeholder text
    - [ ] _(Scrapped: school-wide performance report — no longer part of this phase's scope, see portal table above)_
- [ ] Confirm `CheckRole` middleware correctly scopes every new view (a parent shouldn't be able to hit a student or teacher route by guessing the URL)

**Exit checklist before Phase 3:**

- [ ] Every ❌ row in the four portal tables above is now ✅ (the school-wide report row is intentionally struck through, not counted)
- [ ] Manually log in as one seeded user per role and confirm each dashboard shows correct, role-scoped data (a parent sees only their own children's records, a teacher sees only their assigned classes)
- [ ] Seed at least one parent with **two or more** children and confirm the child switcher shows both, and switching correctly swaps attendance/results/fees for the newly-selected child
- [ ] Confirm a parent cannot view another parent's child's data by manipulating the child-selector's underlying ID (e.g. changing a query param) — this is the most likely place for the multi-child feature to introduce an access-control gap
- [ ] Announcements: at least one admin-authored announcement is visible on the parent dashboard end to end
- [ ] Attempt to access another role's route while authenticated as a different role — confirm 403, not a data leak
- [ ] Admin's report-card PDF actually downloads and contains real seeded data (mark, max, grade, remark, attendance summary — per FR-13)

---

## Phase 3 — Reconcile the design doc: Bootstrap → Tailwind

Documentation change only — the codebase already uses Tailwind + Alpine.js and that isn't changing.

- [ ] Update Table 2 (§3.4.3, Presentation Layer) — Tailwind CSS 3 instead of Bootstrap 5
- [ ] Update §4.4.1 (Table 6, Tier 1 description) — Tailwind's utility-class approach instead of Bootstrap's grid
- [ ] Update §4.4.3 (Physical Design) — "Bootstrap's grid/card component" language → Tailwind equivalent
- [ ] Add a short justification note for choosing Tailwind over the originally-specified Bootstrap
- [ ] Leave the NFR _targets_ (360px, 44×44px touch targets, no horizontal scroll) unchanged — those are requirements, not implementation choices

**Exit checklist before Phase 4:**

- [ ] Every Bootstrap mention in the spec doc has been updated or explicitly justified as a deviation
- [ ] The spec doc and `PROJECT_DOCUMENTATION.md` no longer contradict each other on frontend stack
- [ ] Re-read §4.4.3's UI descriptions against the actual Phase 2 screens — confirm the prose still accurately describes what was built

---

## Phase 4 — Business-logic tests

- [ ] Feature tests for `Fee::recordPayment()` / `reversePayment()` covering every transition in Figure 6: `Pending→Partially Paid`, `Partially Paid→Cleared`, `Pending→Cleared` (single full payment), and reversal
- [ ] Feature tests for grade-letter thresholds (≥90 A+ … <50 F) and the `score ≤ max_score` validation rule
- [ ] Feature test confirming the Phase 1 fee-status fix
- [ ] Feature tests for the role-scoping added in Phase 2 (parent/student/teacher can't reach each other's data)

**Exit checklist before Phase 5:**

- [ ] `php artisan test` passes fully against the MySQL dev DB (not just SQLite's `RefreshDatabase` default — some tests may need `DB_CONNECTION` in `.env.testing` set to match)
- [ ] Every state-chart transition in Figure 6 has at least one corresponding passing test
- [ ] No test relies on hardcoded IDs that only happen to exist because of seeder run order

---

## Phase 5 — Mobile-responsiveness pass

Only after Phases 0–4 are solid on desktop.

- [ ] Parent portal → card-based, vertically-stacked layout (Figure 8), tested down to 360px
- [ ] Teacher mark-entry / attendance → single-viewport, vertically-scrollable at 360px (Figure 7), no horizontal scroll
- [ ] Touch target audit (44×44px minimum) across everything built in Phase 2
- [ ] 3G-throttled load-time check against the ≤5s target (dev-tools network throttling)

**Exit checklist before Phase 6:**

- [ ] Every screen from Phase 2 renders with no horizontal scroll at 360px
- [ ] Touch targets measured (not eyeballed) at 44×44px minimum on at least the highest-traffic screens (attendance, mark entry, fee view)
- [ ] Load-time check recorded for at least the parent dashboard and teacher mark-entry screen under throttled 3G

---

## Phase 6 — PWA / offline capability (FR-14)

The largest gap between spec and implementation — sequenced last because it depends on the mobile UI from Phase 5.

- [ ] Configure `vite-plugin-pwa` — manifest, static-asset caching strategy
- [ ] Service Worker: intercept attendance/mark-entry POSTs when offline, queue in IndexedDB
- [ ] Background sync on reconnection, replaying queued requests
- [ ] Visual pending-sync indicator on the teacher UI

**Exit checklist before Phase 7:**

- [ ] Manually go offline mid-attendance-submission (dev tools "offline" throttle) and confirm the record queues rather than failing silently
- [ ] Reconnect and confirm the queued record actually syncs to the server without duplication
- [ ] Pending-sync indicator visibly appears while offline and clears after successful sync

---

## Phase 7 — Production/deployment alignment

- [ ] Verify the app runs against a real MySQL 8.0+ instance — confirm whether XAMPP is actually giving you MariaDB (common) vs true MySQL, since Table 3 specifically says MySQL 8.0+
- [ ] Confirm HTTPS redirection and TLS enforcement are configured at the web-server level, not just assumed from Laravel defaults
- [ ] Document the Apache/Nginx + PHP-FPM production setup that §4.4.1 describes, since local dev (XAMPP + `artisan serve`) doesn't exercise this path

**Exit checklist (project done):**

- [ ] A fresh clone + documented setup steps produces a working app on a machine that isn't yours
- [ ] Every FR/NFR in §3.4.2 has a corresponding ✅ in the portal tables above or an explicit, documented deviation
- [ ] Spec doc and codebase no longer disagree on stack, controller names, or feature scope

---

## Decisions made

- **Multi-child parents: in scope.** The parent portal must show all enrolled children per parent, not just one — this is now a Phase 2 requirement (child switcher, per-child data fetching, access-control test).
- **School announcements: in scope.** Requires a new `Announcement` model/migration and a minimal admin authoring screen (Phase 2), since nothing in the current schema supports it yet.
- **School-wide performance report: scrapped.** Not being built. Since this is a named use case in the spec's Figure 3, note the descoping explicitly in the spec doc (a one-line "descoped for MVP" note next to that use case) rather than leaving a silent gap between the two documents.

## Remaining open items

- **XAMPP's bundled DB is usually MariaDB, not MySQL.** Compatible for almost everything Eloquent does, but the spec says "MySQL 8.0+" — decide whether to note the substitution in the doc or install real MySQL alongside/instead of XAMPP's bundled version.
- **Desktop-first vs. the spec's mobile-first framing.** Sequencing desktop first is a reasonable _development_ choice, but the spec's rationale chapter (§4.1.3) argues mobile-first from the survey data. Consider a sentence in the doc clarifying that development order and design priority aren't the same thing.
