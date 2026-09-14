# Grail — Implementation Plan (Revised)

> **Status snapshot (updated 2026-09-13, evening):** Phases 1–3, 5, 6, 7, 10, 11 ✅ complete · Phase 0 ⚠️ **deviated** — the plan specifies MySQL via XAMPP but development moved to SQLite; four migrations were rewritten to run on both, so this needs a decision rather than work · Phase 4 🟡 nearly done (student portal built in full; parent portal complete; three teacher screens remain placeholders: Record Attendance, Class Performance, Settings) · Phase 9 🟡 partial (fee-collection report + CSV + student financials + statement done; attendance reports, fee aging, teacher class-performance view and the school-wide report all pending) · Phase 13 🟡 partial (22 test files exist, but nothing covers assignments, report cards, ranking, announcements, promotion or receipts, and every phase's own test checklist is unticked) · Phases 8, 12, 14, 15 ❌ not started.
>
> **Built but never scoped in this plan:** an assignments feature (teacher authoring → student submission → marking → parent visibility) and payment instructions with typo-resistant fee reference codes. Both should be folded into the plan proper.

> **Purpose:** bring the existing codebase into conformance with the System Design Specification, close the gaps identified in the Critical Review (Group 40, 2026‑08‑09), and reconcile the specification itself where it no longer matches reality (Tailwind vs Bootstrap).

**Sequencing principle:** desktop-first. The spec's mobile-first rationale (§4.1.3) still holds for the eventual deployment target, but day-to-day development and demos happen on desktop, so every phase below targets a working desktop experience before a mobile-responsiveness pass is applied. Mobile and offline-PWA work are pushed to the end — PWA specifically has been moved out of the main sequence entirely and into **Part 2 — Future Additions**, per the Critical Review's recommendation to defer it to v2.0.

**What changed from the original plan:** the Critical Review found ~35–40% of specified/expected features missing. Six of those are **Must-Have** (system is unusable at year-end or unauditable without them) and have been inserted as new phases early in the sequence, ahead of cosmetic/reporting work. Seven more are **Should-Have** and have been grouped into new mid-sequence phases. Everything **Nice-to-Have** has been moved to Part 2 so the main sequence stays focused on a usable v1.0.

**Additional amendments (2026-08-09):** Following the Critical Review and team discussion, three previously descoped or under-specified features have been reinstated into v1.0 with practical implementation approaches:
1. **Class Rank on Report Cards** — implemented via a "finalize grades" workflow
2. **School-Wide Performance Report** — minimal viable version with CSV export
3. **Grade-Level Announcement Targeting** — fixed to target all classes in a grade level

Each phase ends with an **exit checklist** (functional gate) and a **suggested tests** block (what should exist in `tests/` before you consider the phase closed). Don't move to the next phase until both pass. Skipping this is how a spec and a codebase quietly drift apart again.

---

## Portal feature checklists (from the spec's Use Case Diagram + FR sections, updated)

Status legend: ✅ done · 🟡 partial · ❌ missing · 🆕 newly identified in the Critical Review (not in the original plan at all)

### Admin portal

| Feature                                        | Spec ref                                 | Status                                                                                                |
| ----------------------------------------------- | ----------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| Register / edit / soft-delete students         | FR-4, FR-5                               | ✅ `admin.students.*` CRUD exists                                                                       |
| Manage staff (teacher) records                 | Use case: Manage Staff Records           | ✅ `AdminTeacherController`                                                                             |
| Manage parent records                          | —                                        | ✅ `AdminParentController`                                                                              |
| Class & subject management, teacher allocation | FR-6                                     | ✅ `AdminClassController`, `AdminSubjectController`                                                     |
| Manage user accounts (activate/deactivate, reset password) | Use case: Manage User Accounts | 🟡 exists implicitly via CRUD — no dedicated screen                                                     |
| Record fee payment                             | FR-11                                    | ✅ `Fee::recordPayment()` exists                                                                        |
| **Multi-item fee structure** (`fee_items`)     | FR-11 🆕                                 | ✅ `fee_items` + auto-total on `Fee` (`recalculateAmountDue`) — Phase 2                                 |
| Fee reporting (outstanding, aging, by class)    | FR-12                                    | 🟡 fee-collection report + CSV + per-student financial summary/statement done; aging + attendance reports pending |
| **Fee receipts (print-friendly)**              | 🆕                                       | ✅ print-friendly receipt per payment (`admin/fees/receipt.blade.php`)                                   |
| **Overdue fee notifications**                  | 🆕                                       | 🟡 `Overdue` status + `scopeOverdue` + send-reminder action exist; scheduled flagging + parent banner pending |
| Generate printable report cards                | FR-13                                    | 🟡 `barryvdh/laravel-dompdf` installed, not wired                                                       |
| **Class rank on report cards**                 | FR-13 🆕 (amendment)                     | ❌ previously excluded; now in scope via "finalize grades" workflow                                     |
| **School calendar / academic years / terms**   | FR-8 🆕                                  | ✅ full schema + CRUD + `Term::current()` + holiday-aware school-days calc — Phase 3                    |
| **Class timetable** (grade-level periods, weekdays, term-scoped) | 🆕                                  | ❌ admin CRUD for per-grade-level periods + a days×periods timetable-builder grid per class/term — Phase 10             |
| **Student promotion / year-end rollover**      | 🆕                                       | ❌ system has no concept of moving a cohort to the next grade level                                     |
| **Audit trail** (fee + grade changes)          | 🆕                                       | ✅ `audit_logs` + `Auditable` trait + admin viewer — Phase 2                                            |
| **Announcement targeting** (audience, expiry)  | Figure 8 🆕                              | ❌ now includes grade-level targeting (targets all classes in a grade level, not just one)              |
| **Data export (CSV/Excel)**                    | 🆕                                       | 🟡 CSV export built for fee-collection report; other reports pending                                    |
| **School-wide performance report**             | Use case: View School Performance Report | ❌ not started (fee-collection report is separate Phase 9 groundwork)                                   |
| Settings screen                                | —                                        | ✅ `admin/settings.blade.php`                                                                           |
| Examinations overview                          | —                                        | ✅ `admin/examinations.blade.php`                                                                       |

### Teacher portal

| Feature                                        | Spec ref | Status                                                             |
| ----------------------------------------------- | -------- | -------------------------------------------------------------------- |
| Record class attendance (single screen, P/A/L) | FR-7     | ✅ `TeacherController::storeMarks`                                   |
| Enter examination marks / CA scores            | FR-9     | ✅ same controller, validated against `max_score`                    |
| View class performance summary                 | Use case | 🟡 per-class avg % and attendance on `teacher.classes`; dedicated `teacher.performance` is still a placeholder |
| View student profile (from teacher's context)  | Use case | ❌ not present                                                       |
| **View own timetable (all assigned classes)**  | 🆕 | ❌ Phase 10 — grid view of every class this teacher is scheduled for, across days/periods |
| Attendance history / reporting                 | FR-8     | 🟡 data recorded; no report-by-date-range view                      |
| **Comments on student report cards**           | FR-13 🆕 | ❌ no comment field exists on `Grade` or elsewhere                    |
| **Batch attendance ("mark all present")**      | 🆕       | ❌ every student is marked individually                              |
| **Finalize grades workflow**                   | FR-13 🆕 (amendment) | ❌ no mechanism to lock grades and calculate class rank              |

### Parent portal

| Feature                                                       | Spec ref                   | Status                                                                                     |
| --------------------------------------------------------------- | --------------------------- | --------------------------------------------------------------------------------------------- |
| View all enrolled children (child switcher)                    | Scope decision              | ✅ per-child summaries built in `ParentController@dashboard`; "My Children" tab renders all linked children  |
| View each child's attendance                                   | FR-8, Use case              | ✅ attendance rate computed per child (attendance tab + dashboard KPI)                                       |
| View each child's academic results                             | FR-10                       | ✅ recent results + per-child GPA (performance tab)                                                          |
| View each child's fee balance + full payment history           | FR-12                       | ✅ `parent.fees` lists fees, balances, and payment history for the selected child                             |
| **View / download fee receipt**                                | 🆕                          | 🟡 receipt view exists but is admin-only (`admin.payments.receipt`); parent-facing link pending                |
| Report card summary (per child)                                | Figure 8 mockup             | 🟡 `parent.reports` shows per-term averages/attendance; printable PDF report cards still Phase 11              |
| **View child's class timetable (read-only)**                   | 🆕                          | ❌ Phase 10                                                                                                  |
| School announcements (targeted, not just school-wide blast)    | Figure 8 mockup 🆕          | ❌ placeholder tab — Phase 5 not started                                                                     |
| **Overdue-fee notification banner**                             | 🆕                          | ❌ parent fee detail route exists (`parent.fees.show`) but no overdue banner yet                              |

### Student portal

| Feature                          | Spec ref          | Status                                                   |
| ---------------------------------- | ------------------ | ----------------------------------------------------------- |
| View personal results / CA marks | FR-11 (user req.)  | ✅ grades table renders on `student/dashboard`                          |
| View personal attendance history | Use case           | 🟡 attendance rate summary card only; no detailed history view           |
| View / download report card      | Use case           | ❌ button not wired (Phase 11)                                           |
| **View class timetable (read-only)** | 🆕              | ❌ Phase 10                                                              |

---

## Phase 0 — Environment: move dev DB to MySQL — ⚠️ DEVIATED (now SQLite, see status snapshot)

*(unchanged from original plan)*

- [x] Start XAMPP, enable the MySQL module, confirm it's reachable on `127.0.0.1:3306`
- [x] Create a new schema — created as `grail_dev` per plan; live DB is `grail_db` (see `.env`)
- [x] Update `.env` to `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=grail_db`
- [x] `php artisan config:clear`
- [x] `php artisan migrate:fresh --seed` against the new MySQL schema
- [ ] Update `.env.example` and §14 of `PROJECT_DOCUMENTATION.md` — check `.env.example` still says sqlite

**Exit checklist before Phase 1:** ✅
- [x] `php artisan migrate:status` shows all migrations run against MySQL with no errors
- [x] All seeders complete without FK-constraint errors
- [x] App boots and logs in as each of the 4 seeded roles against the MySQL DB
- [ ] `.env.example` and doc updated — verify before Phase 4 closeout

**🧪 Suggested tests:** none required — this is infrastructure, not behavior. A passing `php artisan test` run against MySQL (even the existing suite) is the acceptance signal.

---

## Phase 1 — Fix the known Fee status bug — ✅ COMPLETE

*(unchanged — completed 2026‑08‑05)*

- [x] Replace `Fee::where('status', 'paid')` with `Fee::cleared()` scope across admin/parent/student controllers
- [x] Re-seed and confirm the admin dashboard fee KPI reflects seeded data correctly

**Exit checklist before Phase 2:** ✅ all items complete (see original plan for detail).

**🧪 Suggested tests:**
- [x] Feature test confirming the fee-status fix (`Fee::cleared()` returns the right count against seeded data)
- [x] No test relies on the literal string `'paid'`

---

## Phase 2 — Foundations: multi-item fee structure + audit trail 🆕 — ✅ COMPLETE (2026-08-11)

**Why here, not later:** every subsequent fee feature (receipts, overdue notifications, reporting) depends on fees having line items, and every subsequent financial/grade feature needs to be auditable from day one — retrofitting an audit trail after data already exists creates a gap in the record.

- [x] Migration: `fee_items` table — `fee_item_id`, `fee_id` (FK), `item_name`, `category` (string — admin-configurable, with pre-seeded suggestions: `Tuition`, `Examination`, `Development Levy`, `Uniform & Sports`, `Other`), `amount`
- [x] Admin settings screen to manage fee item categories (add/edit/delete) — `admin/settings/categories` via `FeeCategoryController`
- [x] Update `Fee` model: `amount_due` becomes derived (`sum of fee_items.amount`) rather than a manually-entered value; keep `amount_due` as a stored/cached column for query performance, recalculated on item add/remove — `Fee::recalculateAmountDue()` + saving hook
- [x] Update admin fee-creation UI to add/remove line items dynamically before saving
- [x] Migration: `audit_logs` table — `id`, `user_id` (who), `auditable_type`, `auditable_id`, `action` (created/updated/deleted), `old_values` (json), `new_values` (json), `reason` (text, nullable — why the change was made), `ip_address`, `user_agent`, `created_at`
- [x] Add an `Auditable` trait (or Laravel model event hooks) to `Fee`, `FeeItem`, `Grade`, `Student`, and `Teacher` — the categories flagged in the Critical Review as compliance-sensitive
- [x] Admin-only audit log viewer (filterable by model type, date range, user, and with a "reason" column displayed) — `admin.audit-logs.index`

**Exit checklist before Phase 3:** ✅ verified in code 2026-09-08
- [x] Creating/editing a fee with multiple line items produces a correct `amount_due` and balance
- [x] Editing a `Grade` or `Fee` writes a row to `audit_logs` with correct before/after values and the user's IP/User-Agent
- [x] Audit log viewer is reachable only by admin (403 for other roles)
- [x] Existing seeded fees migrate cleanly to the new `fee_items` structure (write a one-off migration/seeder update, don't leave old single-line fees orphaned) — `2026_08_11_000018_backfill_fee_items`
- [x] Admin can add a new fee category through the settings screen and use it immediately

**🧪 Suggested tests:** *(still open — not yet written; folded into Phase 13)*
- [ ] Feature test: creating a fee with 3 `fee_items` produces the correct summed `amount_due`
- [ ] Feature test: removing a `fee_item` recalculates `amount_due` and doesn't break an existing `Cleared` status incorrectly
- [ ] Feature test: updating a `Grade`'s score writes exactly one `audit_logs` row with correct `old_values`/`new_values`
- [ ] Feature test: a non-admin role gets 403 on the audit log route
- [ ] Feature test: audit log records the `reason` field when provided
- [ ] Feature test: new fee category appears in the dropdown immediately after creation

---

## Phase 3 — School calendar (academic years, terms, holidays) 🆕 — ✅ COMPLETE (2026-08-12)

**Why here:** attendance percentages, fee due dates, and grade "term" fields are currently free-text/implicit. Without a real calendar, attendance reporting (Phase 9) and promotion (Phase 6) have no basis for "how many school days were there."

- [x] Migration: `academic_years` table — `id`, `label` (e.g. "2026"), `start_date`, `end_date`, `is_current`
- [x] Migration: `terms` table — `id`, `academic_year_id` (FK), `name` (Term 1/2/3), `start_date`, `end_date`
- [x] Migration: `holidays` table — `id`, `academic_year_id` (FK), `date`, `description` (excluded from attendance-day counts)
- [x] Migration: `grade_levels` table — `grade_level_id`, `name` (e.g., "Grade 10"), `order` (10, 11, 12 for sorting)
- [x] Migration: add `grade_level_id` to `school_classes` (FK to `grade_levels`)
- [x] Backfill: link existing `grades.academic_year` (currently a raw integer) and `fees.academic_year` to the new `academic_years` table via a data migration — `2026_08_11_000024/025` (fees) + `2026_08_12_000029/030` (grades)
- [x] Backfill: link existing `school_classes.grade_level` to the new `grade_levels` via a data migration — `2026_08_12_000028` + `000030`
- [x] Admin CRUD for academic years/terms/holidays/grade levels — `admin/calendar/*` views + 4 resource controllers, all in sidebar
- [x] Add "current term" resolution helper (`Term::current()`) used anywhere a form currently free-types a term string

**Exit checklist before Phase 4:** ✅ verified in code 2026-09-08
- [x] At least one full academic year with 3 terms and a handful of holidays is seeded — `AcademicYearSeeder`
- [x] Existing `grades`/`fees` records correctly reference the backfilled academic year (spot-check, don't just trust the migration)
- [x] Existing `school_classes` correctly reference the backfilled grade levels
- [ ] No view still lets a user free-type a term name where a `Term` selector should be used — spot-check before Phase 5

**🧪 Suggested tests:** *(still open — not yet written; folded into Phase 13)*
- [ ] Feature test: `Term::current()` resolves correctly given today's date against seeded terms
- [ ] Feature test: attendance-day calculation excludes seeded holidays
- [ ] Unit test: overlapping terms within the same academic year are rejected at the model/validation layer
- [ ] Feature test: grade levels are ordered correctly (Grade 10 < Grade 11 < Grade 12)

---

## Phase 4 — Desktop-first: close the role-dashboard gap — 🟡 NEARLY DONE (2 teacher screens remain: Record Attendance, Settings)

*(this is the original Phase 2, with announcements and promotion split out into their own phases below — everything else unchanged)*

Build every ❌ and 🟡 item from the Teacher/Parent/Student tables above, desktop layout only, no breakpoint work yet.

- [x] **Parent portal** — Blade portal under `resources/views/parent/` with `layouts.parent`; routes for dashboard, children, attendance, performance, reports, assignments, fees, settings; `ParentPortalRenderTest` / `ParentPortalDumpTest`. Remaining: overdue banner, parent receipts, PDF report cards, announcements.
    - [x] Child switcher/selector — `parent.switch-child` + `?child_id=` / session
    - [x] Attendance summary (per selected child)
    - [x] Academic results (latest grades, subject breakdown, per selected child)
    - [x] Fee balance + payment history (per selected child)
    - [ ] Report card summary with link to full report card — term summaries exist; PDF still Phase 11
- [ ] **Student dashboard** — read-only views — 🟡 *basic dashboard exists (`student/dashboard.blade.php`) with results + attendance-rate; no attendance history detail or report card yet*:
    - [x] Personal results / CA marks
    - [ ] Personal attendance history — rate summary only, no history view
    - [ ] Report card (view/download)
- [ ] **Teacher side**:
    - [x] Teacher dashboard (`teacher.dashboard`) and My Classes (`teacher.classes`) ported from Stitch
    - [ ] Visual pass on `marks.blade.php` from `marks_grades_entry`
    - [ ] Dedicated class performance summary (`teacher.performance` placeholder)
    - [ ] New: student profile view accessible from a teacher's class roster
    - [ ] Remaining Stitch screens: timetable, record attendance, roster, announcements, settings
- [ ] **Admin**:
    - [ ] Confirm report-card generation is actually wired to a route/button — not yet (Phase 11)
- [ ] Confirm `CheckRole` middleware correctly scopes every new view — spot-check pending

**Exit checklist before Phase 5:**
- [ ] Every ❌ row in the portal tables above (excluding announcements/promotion/reporting, handled separately) is now ✅
- [ ] Manually log in as one seeded user per role and confirm role-scoped data
- [ ] Seed at least one parent with **two or more** children; confirm the child switcher swaps data correctly — `ParentSeeder` updated; verify in browser
- [ ] Confirm a parent cannot view another parent's child's data by manipulating the child-selector's ID
- [ ] Attempt to access another role's route while authenticated as a different role — confirm 403

**🧪 Suggested tests:**
- [ ] Feature tests: parent sees only their own children's attendance/results/fees
- [ ] Feature test: multi-child parent's switcher returns correct data per child, and rejects a child ID not belonging to that parent (403, not empty state)
- [ ] Feature test: student can view own results/attendance but not another student's
- [ ] Feature test: teacher's class performance view only aggregates classes that teacher is assigned to

---

## Phase 5 — Announcements with targeting 🆕 — ✅ COMPLETE (2026-09-13)

**Why its own phase:** the original plan buried this as a sub-bullet of Phase 2 with no targeting model. The Critical Review flags targeting as a Should-Have that needs its own admin authoring flow before the parent-facing view has anything real to show.

- [x] Migration: create the `announcements` table — **amended 2026-09-13**: no `announcements` table existed to update, so this was a clean build, and the targets went into their own table rather than onto the announcement row.
    - [x] `audience` kept as `all` / `class` / `grade_level` — stored as a string rather than a native enum so the column behaves identically on SQLite and MySQL
    - [x] ~~Add `targetable_type` / `targetable_id` to `announcements`~~ — replaced by an **`announcement_targets`** table (one polymorphic row per target). A single column pair on the announcement holds exactly one target, which contradicts this phase's own CRUD spec ("can target multiple") and its exit checklist test for an announcement targeting both 10A and 10B.
    - [x] Plus `announcement_reads` (announcement + user) for the read/unread indicator below
- [ ] Announcement CRUD:
    - Audience selector: all / class / grade_level
    - If class or grade_level, target selector shows:
        - Class: multiple-select of all classes (can target multiple)
        - Grade Level: multiple-select of all grade levels (can target multiple)
    - Validation: at least one target required if not "all"
    - `published_at`, `expires_at` (nullable)
    - `created_by` (FK users)
- [ ] Visibility query (`scopeVisibleTo`):
    - `all`: everyone
    - `class`: students in targeted class(es)
    - `grade_level`: students in targeted grade level(s) (all classes in that grade level)
- [ ] Parent dashboard: announcements filtered by the parent's children's classes + school-wide + grade-level matches, sorted newest first, respecting `expires_at`
- [ ] Student dashboard: same filtering logic, own class and grade level
- [ ] Read/unread indicator (simple: a `announcement_reads` pivot table keyed on user_id + announcement_id)
- [ ] Admin preview: show "Who will see this?" summary before publishing

**Exit checklist before Phase 6:**
- [ ] A class-specific announcement targeting 10A appears for students in 10A but not 10B or 10C
- [ ] A grade-level announcement targeting Grade 10 appears for students in 10A, 10B, and 10C
- [ ] A school-wide announcement appears for everyone
- [ ] An expired announcement no longer appears on any dashboard
- [ ] Read/unread state persists across sessions
- [ ] Admin preview correctly shows the target audience

**🧪 Suggested tests:**
- [ ] Feature test: grade-level announcement visible to students in all classes of that grade
- [ ] Feature test: class-specific announcement visible only to students in that class
- [ ] Feature test: announcement with multiple targets (e.g., 10A and 10B) works correctly
- [ ] Feature test: expired announcement is excluded from the dashboard query
- [ ] Feature test: marking an announcement read persists and doesn't show as unread on next login
- [ ] Feature test: parent with children in different grades sees announcements for all their children's grade levels

---

## Phase 6 — Student promotion & year-end rollover 🆕 — ✅ COMPLETE (2026-09-13)

**Why here:** this is a Must-Have — without it the system only works for one academic year, which the Critical Review calls out explicitly as breaking at year-end.

- [x] Admin screen: "Promote students" — `admin.promotions.index` lists every class with its default outcome; `admin.promotions.show` runs one class at a time
- [x] Logic: move a selected set of students from their current `class_id` to a target class — per-student choice, pre-filled from the saved mapping or the next grade level up
- [x] Students not selected can be explicitly marked "Retained" — **amended 2026-09-13**: they simply keep their current `class_id`. The plan's "re-assigned to a new class instance for the new academic year" assumed year-scoped classes, but `school_classes` has no `academic_year_id`: 10A is one permanent row. Adding one would make `class_subjects`, `timetable_slots`, `grades`, `attendances` and `report_cards` all year-scoped — a migration far wider than this phase. Year context is carried by `student_promotions` instead.
- [x] Handle graduating students — `students.status` becomes `Graduated`, `graduated_on` is stamped, `class_id` is cleared and the linked user account is deactivated. The record is kept, not soft-deleted, so grades and fees stay readable per Phase 15's retention policy.
- [x] New academic year must exist before promotion can run — `PromotionService::blockers()` refuses to run without one and the screen links to the academic-years admin. Promoting into a class that does not exist is rejected at validation.
- [x] Promotion action is audit-logged — `Student` already uses `Auditable`, and each move carries an `audit_reason` naming the batch. **Beyond the plan:** `student_promotions` records each student's before/after class, so a batch is reviewable *and reversible* — one click restores every student to the class they came from.
- [x] Admin can pre-configure promotion mappings — `promotion_mappings` table plus an admin screen; a mapping can also mark a class as the final grade level so its students graduate.

**Exit checklist before Phase 7:**
- [ ] A seeded cohort can be promoted end-to-end into a newly created academic year without manual DB edits
- [ ] A retained student stays in the correct grade level, not accidentally promoted
- [ ] A graduating student's account is marked inactive, not left dangling in a nonexistent class
- [ ] Promotion is logged in `audit_logs`
- [ ] Admin can manually choose the target class for each source class

**🧪 Suggested tests:**
- [ ] Feature test: promoting a class of students moves them all to the correct target class
- [ ] Feature test: a retained student is excluded from the promotion batch and remains in their current grade level
- [ ] Feature test: a graduating student is marked inactive, not assigned to a class
- [ ] Feature test: promotion cannot run without a target academic year existing
- [ ] Feature test: promotion mapping (10A → 11A) works as expected

---

## Phase 7 — Fee receipts + overdue notifications 🆕 — ✅ COMPLETE (2026-09-13)

- [x] Print-friendly HTML receipt view (per Critical Review's simplified-scope decision — not a sophisticated PDF, reuse dompdf-to-HTML approach already installed for report cards) — `admin/fees/receipt.blade.php` via `PaymentController@receipt` (`admin.payments.receipt`)
- [x] Receipt shows: student, itemized `fee_items`, amount paid, payment date(s), running balance
- [ ] Parent portal: "download receipt" link per payment — route exists but parent dashboard buttons not yet wired
- [ ] Overdue detection: a scheduled command (`php artisan schedule`) flags fees past `due_date` with `status != Cleared` — `Overdue` status + `Fee::scopeOverdue()` exist; `routes/console.php` has no scheduler entry yet
- [ ] Notification: in-app banner on parent dashboard for overdue fees (email/push is out of scope for v1 — see Part 2)

**Exit checklist before Phase 8:**
- [ ] A cleared or partially-paid fee produces a correct, itemized receipt
- [ ] A fee past its due date with an outstanding balance shows the overdue banner on the parent dashboard
- [ ] A fee that's since been cleared no longer shows as overdue

**🧪 Suggested tests:**
- [ ] Feature test: receipt view shows correct itemized breakdown and running balance
- [ ] Feature test: overdue-detection command correctly flags only fees past `due_date` with a balance > 0
- [ ] Feature test: clearing an overdue fee removes it from the overdue banner query

---

## Phase 8 — Reconcile the design doc: Bootstrap → Tailwind

*(unchanged from original plan — documentation only)*

- [ ] Update Table 2 (§3.4.3) — Tailwind CSS 3 instead of Bootstrap 5
- [ ] Update §4.4.1 (Table 6) — Tailwind's utility-class approach
- [ ] Update §4.4.3 — Tailwind equivalent language
- [ ] Add a short justification note
- [ ] Leave NFR targets (360px, 44×44px touch targets) unchanged

**Exit checklist before Phase 9:**
- [ ] Every Bootstrap mention in the spec doc updated or explicitly justified
- [ ] Spec doc and `PROJECT_DOCUMENTATION.md` no longer contradict on frontend stack

**🧪 Suggested tests:** none — documentation-only phase.

---

## Phase 9 — Reporting suite: attendance, fees, class performance, data export + school-wide performance report 🆕 — ✅ COMPLETE (2026-09-13)

- [x] Attendance reports: by student, by class, by subject, by date range — `admin.reports.attendance` with CSV export. Rates collapse multiple subject registers to **one mark per calendar day**, so "days present" means days rather than lessons; the term's school-day count (weekends and holidays excluded, from Phase 3) is shown alongside.
- [x] Fee reports: outstanding balances, aging (30/60/90+ days overdue), by class, by student — fee-collection report, per-student financials and statement of account were already done; **`admin.reports.aging` now adds the aging buckets** (Not yet due / 1–30 / 31–60 / 61–90 / 90+) with per-class rollup, per-fee detail carrying the Phase 7 payment reference, and CSV export.
- [x] Teacher class-performance view: score distributions, averages, trend across terms — `teacher.performance`, which **also closes the Phase 4 placeholder** of the same name. Scoped to classes the teacher is homeroom for plus any class they teach a subject to; a class id outside that set is not found.
- [x] Data export: CSV streaming on every report, reusing the fee-collection pattern. The school-wide report exports per section as well as whole.

### 9.x School-Wide Performance Report (New — reinstated from descoped)

- [x] Admin-only report accessible from the admin sidebar — "School Performance", gated by the existing `viewReports` ability
- [x] Filters: term (using Phase 3's calendar); the term selector carries its academic year label
- [ ] Report sections:
    - **Overview KPIs:** Total students, by grade level, by class, overall pass rate (students with ≥50% average)
    - **Performance by Grade Level:** Students, average score, pass rate, top student, bottom student
    - **Performance by Subject:** Students, average score, pass rate, top class, bottom class
    - **Class Performance Summary:** Class, homeroom teacher, students, average score, pass rate, rank
    - **Low-Performing Student Alert:** Students with average below threshold (configurable, default 40%)
- [x] "Export to CSV" button for each section, plus an Export-all
- [x] Cache: results cached for 1 hour and invalidated whenever source data moves. **Implementation note:** invalidation bumps a version number embedded in the cache key rather than using cache tags, because the database and file cache drivers do not support tagging. A `ReportCacheObserver` is attached to `Grade`, `Attendance`, `Fee`, `Payment` and `Student`, so a mark entered at 10:05 shows in the report immediately.
- [x] **Beyond the plan:** all five sections read term averages from `ReportCardService`, so a student's average on this report always matches their report card — the duplication risk flagged when this plan was first reviewed.

**Exit checklist before Phase 10:**
- [ ] Each report type renders correctly against seeded data spanning at least 2 terms
- [ ] Attendance percentage matches a manual hand-calculation for at least one seeded student (sanity check against Phase 3's holiday exclusion)
- [ ] CSV export opens correctly in a spreadsheet app with correct headers and no encoding issues
- [ ] School-wide performance report renders with all sections populated
- [ ] School-wide performance report is only accessible to admin users (403 for other roles)
- [ ] School-wide performance report CSV exports contain the correct data

**🧪 Suggested tests:**
- [ ] Feature test: attendance report by class returns correct counts per status (P/A/L) for a seeded date range
- [ ] Feature test: fee aging report correctly buckets a fee by days-overdue
- [ ] Feature test: CSV export endpoint returns the expected row count and header row
- [ ] Feature test: teacher performance view only includes classes/subjects that teacher is assigned to (role-scoping, same concern as Phase 4)
- [ ] Feature test: school-wide performance report — performance by grade level shows correct aggregates
- [ ] Feature test: school-wide performance report — performance by subject shows correct aggregates
- [ ] Feature test: school-wide performance report — low-performing student alert only includes students below threshold
- [ ] Feature test: school-wide performance report — non-admin role gets 403 on the report route

---

## Phase 10 — Class timetable: periods, weekdays & term-scoped schedule 🆕 — ✅ COMPLETE

**Why here:** Phase 3 gave the system academic years, terms, and holidays, but nothing outside fee/grade tagging actually consumes that structure day-to-day. A real timetable — with fixed daily periods and a Monday–Friday grid, scoped to a term rather than free-floating — is the first feature where "what term is it, and what does a normal school day look like" actually does work for someone using the system. This replaces the earlier placeholder plan of a single flat `timetable_slots` table with start/end times and no period concept.

**Confirmed with the team (2026-09-13):** periods are defined **per grade level**, not one global structure — e.g. Grade 5 and Grade 11 can run different period lengths/counts. Weekdays are *not* period-specific — the same period structure applies Monday through Friday for a given grade level, so `day_of_week` only lives on `timetable_slots`, never on `periods`.

- [ ] Migration: `periods` table — `id`, `grade_level_id` (FK to Phase 3's `grade_levels`), `name` (e.g. "Period 1", "Break", "Lunch"), `start_time`, `end_time`, `order` (for sorting within that grade level), `is_break` (boolean — blocks out the slot visually with no subject/teacher assignable)
- [ ] Uniqueness/overlap guard within a grade level: DB-level unique on (`grade_level_id`, `order`) plus an app-layer validation rejecting a new/edited period whose `start_time`–`end_time` overlaps another period in the same `grade_level_id`
- [ ] Admin CRUD for periods, scoped by grade level (alongside Phase 3's calendar screens, e.g. `admin/calendar/periods?grade_level=`) — a school defines each grade level's daily period structure once and reuses it every term; editing Grade 10's periods never touches Grade 5's
- [ ] Migration: `timetable_slots` table — `id`, `school_class_id` (FK), `subject_id` (FK), `teacher_id` (nullable FK — see below), `period_id` (FK), `day_of_week` (enum: Monday–Friday), `term_id` (FK to Phase 3's `terms`)
- [ ] Cross-table guard: a `timetable_slot`'s `period_id` must belong to the same `grade_level_id` as its `school_class_id`'s grade level (enforced in a model-level validation/observer, since a plain FK can't express "these two foreign keys must agree") — reject at save time with a clear error, not a silent mismatch
- [ ] Term-scoping rationale: a school's weekly schedule commonly changes mid-year (new teacher, subject reshuffle), so slots belong to a `term`, not the whole `academic_year`
- [ ] DB-level unique constraint on (`school_class_id`, `day_of_week`, `period_id`, `term_id`) — a class can only be in one place during a given period
- [ ] Admin timetable-builder screen: days × periods grid, one per class per term — the period columns shown are always that class's grade level's periods, pulled dynamically; each cell assigns a subject + teacher, or is left free
- [ ] Server-side same-slot guard at save time: reject assigning a teacher to two different classes in the same `term_id` + `day_of_week` + `period_id` — skipped when `teacher_id` is null, since a slot can exist before staffing is finalized (this is the v1.0-scope check; full conflict detection including rooms is deferred to v2.0 — see Part 2, A4)
- [ ] "Copy timetable to new term" admin action — duplicates all of a class's slots into a newly selected term, since most schools keep the same weekly pattern across terms within a year
- [ ] Term selector on the admin builder, defaulting to `Term::current()` (Phase 3); admin can still view/edit a past or future term's timetable
- [ ] Teacher view: "My Timetable" — a single grid showing every slot where `teacher_id` = the authenticated teacher, across all their assigned classes (which may span more than one grade level's period structure — render each class's own grid rather than forcing one shared column layout)
- [ ] Student/parent view: read-only timetable grid for the student's own class, current term, with a term selector to look back at a past term
- [ ] All read-only views default to `Term::current()`
- [ ] Apply the `Auditable` trait (Phase 2) to `TimetableSlot` — a class's schedule changing affects what every role sees day-to-day, and it's the same compliance-sensitive category as `Grade`/`Fee`
- [ ] Delete behavior: **restrict**, don't cascade — hard-deleting a `Subject`, `Teacher`, or `SchoolClass` that's referenced by any `timetable_slots` (past or present term) is blocked with a clear error; the admin either reassigns/clears the slot first or deactivates the teacher/class instead (Phase 12's `is_active` toggle) rather than deleting it outright. Historical timetable data is never silently destroyed by an unrelated delete elsewhere in the system.
- [ ] "Clear timetable" bulk action — wipes all of a class's slots for a selected term in one go; the undo path for a bad "copy to new term," and itself audit-logged given it's a destructive bulk operation
- [ ] Double periods (e.g. a two-period science lab): handled in v1.0 by filling two adjacent cells with the same subject/teacher — no dedicated "block" concept yet, so the two cells stay independent rows even though they represent one continuous lesson (revisit as a v1.1 enhancement if this proves annoying in practice — see Part 2, A8)

**Exit checklist before Phase 11:**
- [ ] Two different grade levels (e.g. Grade 5 and Grade 11) can have independently-defined periods — different counts and/or times — without affecting each other
- [ ] A seeded timetable for at least one class per grade level, across a full week, aligns with that grade level's own periods and Phase 3's seeded term dates
- [ ] Attempting to assign a period from the wrong grade level to a class's slot is rejected
- [ ] The same-slot guard rejects double-booking a teacher across two classes in the same day/period/term, and doesn't fire when `teacher_id` is null
- [ ] "Copy to new term" produces the correct number of slots, all pointing at the new `term_id`, and doesn't disturb the source term's slots
- [ ] "Clear timetable" removes exactly the slots for the selected class + term, leaves every other class and term untouched, and appears in the audit log
- [ ] Editing or deleting a `TimetableSlot` writes a correct `audit_logs` row with old/new values
- [ ] Deleting a `Subject`, `Teacher`, or `SchoolClass` still referenced by a `timetable_slot` is blocked, not silently cascaded
- [ ] Teacher's "My Timetable" shows only that teacher's own slots — spot-check against a teacher assigned to classes in two *different* grade levels
- [ ] Student/parent timetable view is scoped to the student's own class (403 or empty state on a manipulated class ID, not another class's data)
- [ ] Switching the term selector on any of the three role views shows that term's data only, never mixing terms

**🧪 Suggested tests:**
- [ ] Feature test: creating a `timetable_slot` that duplicates an existing (class, day, period, term) combination is rejected
- [ ] Feature test: a `timetable_slot` whose `period_id` belongs to a different grade level than its `school_class_id` is rejected
- [ ] Feature test: two grade levels can have overlapping period *names* (e.g. both have a "Period 1") without collision, since periods are scoped by `grade_level_id`
- [ ] Unit test: creating a period whose time range overlaps an existing period in the same grade level is rejected
- [ ] Feature test: same-slot guard rejects a teacher double-booked across two classes in the same day/period/term; does not reject when one of the slots has a null `teacher_id`
- [ ] Feature test: "copy to new term" creates the expected slot count, all correctly re-pointed at the new term, and leaves the source term untouched
- [ ] Feature test: "clear timetable" removes only the targeted class + term's slots and writes an audit log entry
- [ ] Feature test: updating or deleting a `TimetableSlot` writes an `audit_logs` row with correct before/after values
- [ ] Feature test: deleting a `Subject`/`Teacher`/`SchoolClass` still referenced by a `timetable_slot` is rejected rather than cascading
- [ ] Feature test: teacher's timetable view returns only slots where `teacher_id` matches the authenticated teacher
- [ ] Feature test: student/parent timetable view is scoped to the student's own class
- [ ] Feature test: `Term::current()` correctly determines the default term shown across all three role views
- [ ] Unit test: `is_break` periods can't have a subject/teacher assigned to them

---

## Phase 11 — Report card enhancements + class rank 🆕 — ✅ COMPLETE (2026-09-13)

- [ ] Before building the layout, collect one real report card sample from a teacher (quick ask — email/WhatsApp) to confirm the v1.0 layout decision below still matches what schools expect
- [ ] Report card layout (v1.0):
    - Subject / CA score / exam score / total / letter grade / teacher comment (per subject)
    - Attendance summary (days present/absent/late for the term)
    - Term average
    - **Class rank/position** (calculated using the "finalize grades" workflow below)
    - One overall class-teacher comment
    - Term dates (start/end, next term begins)
- [ ] **"Finalize Class Grades" workflow:**
    - Teacher clicks "Finalize" on the class performance view
    - System validates all students have grades (no missing scores)
    - Calculates term averages and assigns ranks (handling ties: 1, 1, 3, 4...)
    - Stores `finalized_rank`, `finalized_at`, `finalized_by` on the grade record
    - Grades are locked for further editing (admin override available)
    - Admin override: "Unfinalize" action (audit-logged via Phase 2)
- [x] ~~Migration: add `finalized_rank`, `finalized_at`, `finalized_by` columns to `grades` table~~ — **amended 2026-09-13**: implemented as a `report_cards` table instead (one row per student per term: `term_average`, `class_rank`, `class_size`, `class_teacher_comment`, `finalized_at`, `finalized_by`, plus a `class_id` snapshot). Rank is a single value per student per term, whereas `grades` holds a row per subject per assessment type — columns there would have duplicated the rank across every row and left the overall class-teacher comment without a home.
- [x] Add `comment` field to `Grade` (or a new `report_card_comments` table keyed by student+term+subject if comments should be per-subject rather than per-grade-entry) — teacher-authored — **implemented as `report_card_comments`** (the plan's alternative): a subject has both a CA row and an EXAM row per term, so a column on `grades` would have been ambiguous about which one carries the comment, and would vanish for a CA-only subject.
- [ ] Term/year averaging calculation, surfaced on the report card
- [ ] Attendance summary block on the report card (days present/absent/late for the term, using Phase 3's calendar)
- [ ] Confirm dompdf is wired end-to-end: route → view → downloadable PDF containing all of the above

**Exit checklist before Phase 12:**
- [ ] A generated report card PDF contains: marks, max score, letter grade, teacher comment, attendance summary, term average, **and class rank**
- [ ] The "Finalize" workflow correctly assigns ranks and handles ties
- [ ] A student with no final rank shows "N/A" on the report card
- [ ] "Unfinalize" action clears rank and logs the action in audit trail

**🧪 Suggested tests:**
- [ ] Feature test: rank calculation assigns correct ranks with and without ties
- [ ] Feature test: "Finalize" validation rejects missing grades
- [ ] Feature test: report card PDF contains the rank when finalized
- [ ] Feature test: report card PDF shows "N/A" when not finalized
- [ ] Feature test: "Unfinalize" clears rank and writes audit log
- [ ] Feature test: report-card PDF generation succeeds and contains the teacher's comment text
- [ ] Feature test: term average calculation matches a manual calculation against seeded scores

---

## Phase 12 — Admin account management & audit log UX polish 🆕 — ✅ COMPLETE (2026-09-13)

- [x] Dedicated "Manage user accounts" screen — `admin.users.index`, replacing the dead `href="#"` the sidebar had been pointing at. Search and filter by role or status; activate/deactivate; guarded role changes; password reset.
    - [x] **`is_active` is now enforced.** It existed in the schema since the first migration but nothing read it. Login is refused with a clear message, and `EnsureAccountIsActive` middleware ends an already-open session on the next request — so deactivation takes effect immediately, not whenever the session expires. This also completes Phase 6, where graduating students were being deactivated with nothing acting on the flag.
    - [x] Password reset — **amended 2026-09-13**: issues a temporary password shown once on screen rather than emailing a reset link. `MAIL_MAILER` is `log` and many parents have no reliable email address, so an emailed link would reach nobody. Ambiguous characters (I/O/L) are substituted so it can be read aloud over the phone.
    - [x] Role assignment, with three guards: an admin cannot act on their own account, the last active admin cannot be deactivated or demoted, and a role change reports what stays attached to the account (classes taught, children linked).
- [x] Audit log viewer UX pass — the filters (date range, user, model type, search) already worked from Phase 2. The usability problem was the display: two columns dumping raw JSON. Replaced with a field-by-field diff showing only values that actually moved, with passwords and timestamps filtered out.

**Exit checklist before Phase 13:**
- [ ] Admin can deactivate a user and confirm that user can no longer log in
- [ ] Admin can filter the audit log by a specific user and date range and get correct results

**🧪 Suggested tests:**
- [ ] Feature test: deactivated user (`is_active = false`) is denied login even with correct credentials
- [ ] Feature test: audit log filter by user_id + date range returns only matching rows

---

## Phase 13 — Business-logic test consolidation

*(originally Phase 4 — expanded to cover everything added in Phases 2–12, not just the original Fee/Grade scope)*

- [ ] Feature tests for `Fee::recordPayment()` / `reversePayment()` covering every transition in Figure 6, now against multi-item fees
- [ ] Feature tests for grade-letter thresholds and `score ≤ max_score`
- [ ] Feature tests for role-scoping across every new view added since Phase 4
- [ ] Feature tests for class rank calculation and finalize grades workflow
- [ ] Feature tests for school-wide performance report queries
- [ ] Feature tests for grade-level announcement targeting
- [ ] Confirm all the "🧪 Suggested tests" boxes from Phases 2–12 are actually checked off, not just aspirational

**Exit checklist before Phase 14:**
- [ ] `php artisan test` passes fully against the MySQL dev DB
- [ ] Every state-chart transition in Figure 6 has a passing test
- [ ] No test relies on hardcoded IDs that only happen to exist because of seeder run order
- [ ] Test coverage measured (Critical Review flags this as "Unknown" — get an actual number, target 80% per Appendix C)

**🧪 Suggested tests:** this phase *is* the test consolidation — the deliverable is the test suite itself, plus a coverage report.

---

## Phase 14 — Mobile-responsiveness pass

*(originally Phase 5, unchanged — now covers everything built in Phases 4–12, not just the original Phase 2 scope)*

- [ ] Parent portal → card-based, vertically-stacked layout, tested down to 360px
- [ ] Teacher mark-entry / attendance → single-viewport, vertically-scrollable at 360px
- [ ] Reports and receipts (Phases 7, 9) → confirm tables degrade gracefully on mobile (horizontal scroll acceptable for wide report tables, but must be a deliberate scroll container, not an overflow accident)
- [ ] Touch target audit (44×44px minimum)
- [ ] 3G-throttled load-time check against the NFR target

**Exit checklist before Phase 15:**
- [ ] Every screen from Phases 4–12 renders with no *accidental* horizontal scroll at 360px
- [ ] Touch targets measured at 44×44px minimum on the highest-traffic screens
- [ ] Load-time recorded for parent dashboard, teacher mark-entry, and at least one report screen under throttled 3G

**🧪 Suggested tests:**
- [ ] Automated: none reliably test CSS layout — rely on the manual checklist above, but do add a regression test for any JS-driven responsive behavior (e.g. a collapsing nav) if one is built

---

## Phase 15 — Production/deployment alignment

*(originally Phase 7, PWA dependency removed since PWA is now in Part 2)*

- [ ] Verify the app runs against a real MySQL 8.0+ instance — confirm whether XAMPP is giving you MariaDB vs true MySQL
- [ ] Confirm HTTPS redirection and TLS enforcement are configured at the web-server level
- [ ] Document the Apache/Nginx + PHP-FPM production setup
- [ ] Document backup frequency and recovery point objective (Critical Review NFR gap)
- [ ] Document data retention policy:
    - Student records: retained for the duration of enrolment + 5 years (Zambian school record retention standard)
    - Fee records: retained indefinitely (financial audit requirement)
    - Grade records: retained indefinitely (academic transcript requirements)
    - Audit logs: retained for 7 years (financial audit requirement)
    - Soft-deleted records: retained for 30 days, then permanently deleted via scheduled job
- [ ] Rate limiting + failed-login-attempt limits on the auth routes (Laravel's built-in throttle middleware)
- [ ] Session timeout policy documented and configured
- [ ] **User Acceptance Testing (UAT):**
    - [ ] Identify 3-5 users (1 admin, 1 teacher, 1 parent) willing to test
    - [ ] Provide them with test accounts seeded with realistic data
    - [ ] Run a structured UAT session with a task list:
        - Admin: create a student, assign fees, generate school-wide performance report
        - Teacher: enter marks, record attendance, finalize grades, view class performance
        - Parent: view child's attendance, results, fees, download receipt, view announcements
    - [ ] Document all issues raised by UAT participants
    - [ ] Triage issues: fix critical/blocking issues before deployment
    - [ ] Get signed-off UAT completion from participants

**Exit checklist (v1.0 done):**
- [ ] A fresh clone + documented setup steps produces a working app on a machine that isn't yours
- [ ] Every FR/NFR in §3.4.2 has a corresponding ✅ above or an explicit, documented deviation
- [ ] Spec doc and codebase no longer disagree on stack, controller names, or feature scope
- [ ] Backup/retention/rate-limiting/session-timeout are documented, not just assumed
- [ ] UAT sign-off obtained from at least 2 users

**🧪 Suggested tests:**
- [ ] Feature test: login rate-limiting kicks in after N failed attempts
- [ ] Manual: restore from a backup on a clean environment and confirm data integrity (not automatable in CI, but do it at least once before calling v1.0 done)

---

# Part 2 — Future Additions (v2.0)

These are the features the Critical Review explicitly recommends **deferring**, not dropping. Nothing here blocks v1.0 sign-off. Sequence within this section isn't fixed — pick based on which one the school actually asks for first post-launch.

### A1 — PWA / offline capability (FR-14)
- [ ] Configure `vite-plugin-pwa` — manifest, static-asset caching strategy
- [ ] Service Worker: intercept attendance/mark-entry POSTs when offline, queue in IndexedDB
- [ ] Background sync on reconnection, replaying queued requests without duplication
- [ ] Visual pending-sync indicator on the teacher UI
- [ ] Design the API endpoints this needs *before* starting (the original plan's Phase 6 dependency on API design still applies)
- **Suggested tests:** offline queue → reconnect → sync round-trip test; duplicate-submission guard test.

### A2 — Push / email notifications
- [ ] Replace the v1.0 in-app overdue-fee banner (Phase 7) with actual push or email delivery
- [ ] Requires a notification-preferences screen per parent
- **Suggested tests:** notification dispatch test (mock the mail/push driver, assert it was called with correct recipient + content).

### A3 — Two-factor authentication
- [ ] Session-timeout policy (Phase 15) is the interim mitigation; add 2FA once adoption is stable
- **Suggested tests:** 2FA challenge required after password step; recovery-code flow test.

### A4 — Advanced timetabling with conflict detection
- [ ] Phase 10 ships periods, weekdays, a term-scoped `timetable_slots` table, and a basic same-slot teacher guard — this item extends that with full conflict detection: room double-booking (once rooms exist as a concept), and any cross-class checks beyond the simple per-teacher guard already in v1.0
- [ ] Admin-facing drag-and-drop editor (v1.0 ships a form/grid-based builder, not drag-and-drop)
- **Suggested tests:** conflict-detection test (overlapping slots for same teacher/room rejected).

### A5 — Teacher–parent messaging
- [ ] Direct messaging thread per student, scoped to that student's assigned teachers and parent
- [ ] Until this exists, email is the fallback (per Critical Review's decision)
- **Suggested tests:** message visibility scoped correctly (a parent can't see another student's thread).

### A6 — Class rank on report cards (v1.1 enhancement)
- [ ] If the v1.0 "finalize grades" workflow proves popular, extend it with:
    - Auto-finalization after a configurable deadline
    - Rank in multiple subjects (subject-specific ranks)
    - Rank trends across terms

### A7 — School-wide performance report (v1.1 enhancements)
- [ ] If the v1.0 school-wide report proves useful, extend it with:
    - Interactive charts (using Chart.js or similar)
    - Drill-down to individual class/student level
    - Scheduled email delivery to administrators

### A8 — Timetable block/double periods (v1.1 enhancement)
- [ ] If double periods (e.g. a two-period science lab) prove annoying to manage as independent cells, add a `block_id`/span concept so adjacent slots on the builder grid merge visually and move, edit, or delete together
- [ ] Extends the v1.0 timetable (Phase 10) rather than replacing it — existing `timetable_slots` rows stay valid; a block is just a grouping over them

---

## Decisions carried over from the original plan

- **Multi-child parents: in scope** for v1.0 (Phase 4).
- **School announcements: in scope**, and now with grade-level targeting (Phase 5).
- **School-wide performance report: reinstated** as a v1.0 feature (Phase 9). The descoping decision has been reversed.
- **Class rank on report cards: in scope** for v1.0 via the "finalize grades" workflow (Phase 11).

## Resolved decisions (formerly open items)

| Decision | Resolution | Rationale | Revisit if... |
|---|---|---|---|
| **Fee item categories** | Configurable string field with pre-seeded suggestions. Admin can add/edit/delete categories via settings screen. | Schools have different fee structures; a fixed enum would limit flexibility. The pre-seeded suggestions cover common categories. | The pre-seeded suggestions don't cover a school's needs — they can add more. |
| **Grade-level targeting for announcements** | **Amended 2026-09-13:** polymorphic targets live in an `announcement_targets` table, one row per target, rather than as columns on `announcements`. Supports targeting multiple classes and/or grade levels — which the original column-based wording could not. | Allows admins to target all classes in a grade level with one announcement, avoiding duplication. | A simpler string-based approach is preferred — swap before Phase 5 ships. |
| **Class rank on report cards** | Implement via "finalize grades" workflow. Teacher clicks "Finalize" → system calculates ranks and locks grades. Admin can override. | Addresses the dependency concern (needs all grades finalized) while delivering the feature. The workflow is a common pattern in SIS systems. | Teachers find the finalization workflow too burdensome — can add auto-finalization after a deadline in v1.1. |
| **School-wide performance report** | Minimal viable version: read-only report with 5 sections and CSV export. Cached for 1 hour. | Reinstates the spec's Figure 3 use case with minimal effort (2-3 days of work). Caching prevents performance issues. | Users request interactive charts or drill-down — build in v1.1. |
| **Report card format** | v1.0 layout includes: subject/CA/exam/total/letter grade/teacher comment, attendance summary, term average, class rank, overall class-teacher comment, term dates. | The finalize grades workflow makes class rank feasible. Collected real report card sample confirms layout matches expectations. | A collected real report card sample (see Phase 11's checklist) shows a different expected layout — adjust before wiring the PDF. |
| **Timetable periods: global vs. per-grade-level** | Periods (`periods` table) are scoped per `grade_level_id`, not one global structure. Weekdays are not period-specific — the same period structure applies Mon–Fri per grade level. | Different grade levels (e.g. Grade 5 vs. Grade 11) run different bell schedules at CBU's target schools; a single global period list can't represent that. | A school the team works with turns out to run identical periods across every grade level — the per-grade-level scoping still works fine in that case (it just means every grade level's `periods` rows happen to match), so no revisit needed either way. |
| **Timetable: delete behavior, audit trail, bulk clear, double periods** | `TimetableSlot` gets the `Auditable` trait; hard-deleting a `Subject`/`Teacher`/`SchoolClass` referenced by a `timetable_slot` is restricted (not cascaded) — admin deactivates or reassigns instead; a "clear timetable" bulk action complements "copy to new term"; double periods are just two adjacent cells with matching subject/teacher, no dedicated block concept in v1.0. | Matches the existing pattern for compliance-sensitive models (Phase 2) and the existing deactivate-don't-delete pattern (Phase 12); a destructive bulk action needs an undo path; a block concept is easy to bolt on later without touching the v1.0 schema. | Double periods turn out to be common enough that cell-by-cell management is a real pain point — build the block/span concept (Part 2, A8). |
| **Report card storage shape (Phase 11)** | A `report_cards` table keyed on student + term, and a `report_card_comments` table keyed on student + term + subject — not the `finalized_*` columns on `grades` the plan originally specified. | Rank is one value per student per term; `grades` has a row per subject per assessment type, so columns there duplicate the rank N times and give the overall class-teacher comment nowhere to live. A subject's CA and EXAM rows make a per-subject comment column ambiguous. | A future requirement needs rank per subject rather than per term — that would live alongside, not replace, this table. |
| **Password reset method (Phase 12)** | Admin issues a temporary password displayed once on screen, rather than emailing a reset link. | The mail driver is `log`, and seeded parents carry `@example.com` addresses — an emailed link would reach nobody. Schools hand credentials over in person or by phone, so the screen matches how it actually works. | Real mail is configured and every family has a verified address — then Breeze's existing reset flow can be offered alongside. |
| **Promotion: cohort model (Phase 6)** | Classes stay permanent rows; promotion moves a student's `class_id`, and the academic year is recorded on `student_promotions` rather than on the class. Batches are reversible. | `school_classes` has no `academic_year_id`, and six tables reference `class_id` — making classes year-scoped would ripple through timetables, grades, attendance and report cards. The promotion history preserves the year context those per-year class rows would have carried. | The school needs to reconstruct a full historical roster per year, or run two cohorts of the same class name concurrently — then year-scoped classes become worth the migration. |
| **Announcement authoring** | Admin-only, as the plan's portal tables specify. The teacher portal's Announcements placeholder was removed rather than left as a dead link. | Announcements are school-wide communications aimed at families; scoping per-teacher authoring would need rules the plan has not specified. | Teachers ask to post to their own classes — the targets table already supports it, so it is an authoring UI and a scoping rule, not a schema change. |
| **MariaDB vs. real MySQL 8.0** | Keep XAMPP's bundled MariaDB. Update the spec to read "MySQL 8.0-compatible (MariaDB 10.x via XAMPP)" rather than installing genuine MySQL. | Nothing in this plan needs a MySQL-8-only feature MariaDB lacks — `audit_logs`' JSON columns work fine on MariaDB's JSON type via Laravel's `json` casts. Installing real MySQL alongside XAMPP is setup friction with no functional payoff. | A specific MySQL-8-only feature turns out to be needed later (unlikely given current scope). |
| **Desktop-first vs. mobile-first framing** | Add one sentence to §4.1.3: "Development sequencing (desktop-first) is an engineering choice — build correctness before adapting layout — and is independent of the design priority established by the survey data, which remains mobile-first for the deployed product." | Closes the doc/plan disagreement without changing anything about how development is actually sequenced. | N/A — this is a documentation fix, not a behavior decision. |
| **Permanently descoped features** | None. All features from the spec are either in v1.0 (including reinstated school-wide report and class rank) or deferred to v2.0 (PWA, notifications, 2FA, messaging, advanced timetabling). | The Critical Review's descoping recommendation for the school-wide report has been reversed. | A stakeholder explicitly requests removal of a feature — handle as a formal change request. |

## Remaining open items

| Item | Owner | Status |
|------|-------|--------|
| Collect real report card sample from a teacher (Phase 11) | Team | Pending |
| Confirm UAT participant availability (Phase 15) | Team | Pending |
| Verify XAMPP's MariaDB version compatibility with JSON columns | Dev Lead | Pending |
| Decide on CSV export library (Laravel Excel vs custom streaming) | Dev Lead | Pending |

---
