# Grail — Implementation Plan (Revised)

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
| **Multi-item fee structure** (`fee_items`)     | FR-11 🆕                                 | ❌ schema only supports one line item per fee                                                           |
| Fee reporting (outstanding, aging, by class)    | FR-12                                    | 🟡 dashboard KPI only, no detail reports                                                                |
| **Fee receipts (print-friendly)**              | 🆕                                       | ❌ not planned previously                                                                               |
| **Overdue fee notifications**                  | 🆕                                       | ❌ not planned previously                                                                               |
| Generate printable report cards                | FR-13                                    | 🟡 `barryvdh/laravel-dompdf` installed, not wired                                                       |
| **Class rank on report cards**                 | FR-13 🆕 (amendment)                     | ❌ previously excluded; now in scope via "finalize grades" workflow                                     |
| **School calendar / academic years / terms**   | FR-8 🆕                                  | ❌ nothing in schema — attendance/fee/grade reporting by term is meaningless without this               |
| **Student promotion / year-end rollover**      | 🆕                                       | ❌ system has no concept of moving a cohort to the next grade level                                     |
| **Audit trail** (fee + grade changes)          | 🆕                                       | ❌ no logging of who changed what                                                                       |
| **Announcement targeting** (audience, expiry)  | Figure 8 🆕                              | ❌ now includes grade-level targeting (targets all classes in a grade level, not just one)              |
| **Data export (CSV/Excel)**                    | 🆕                                       | ❌ not planned previously                                                                               |
| **School-wide performance report**             | Use case: View School Performance Report | ❌ previously descoped; now reinstated with minimal viable implementation (see Phase 9)                 |
| Settings screen                                | —                                        | ✅ `admin/settings.blade.php`                                                                           |
| Examinations overview                          | —                                        | ✅ `admin/examinations.blade.php`                                                                       |

### Teacher portal

| Feature                                        | Spec ref | Status                                                             |
| ----------------------------------------------- | -------- | -------------------------------------------------------------------- |
| Record class attendance (single screen, P/A/L) | FR-7     | ✅ `TeacherController::storeMarks`                                   |
| Enter examination marks / CA scores            | FR-9     | ✅ same controller, validated against `max_score`                    |
| View class performance summary                 | Use case | ❌ no aggregate view for a teacher's classes                         |
| View student profile (from teacher's context)  | Use case | ❌ not present                                                       |
| Attendance history / reporting                 | FR-8     | 🟡 data recorded; no report-by-date-range view                      |
| **Comments on student report cards**           | FR-13 🆕 | ❌ no comment field exists on `Grade` or elsewhere                    |
| **Batch attendance ("mark all present")**      | 🆕       | ❌ every student is marked individually                              |
| **Finalize grades workflow**                   | FR-13 🆕 (amendment) | ❌ no mechanism to lock grades and calculate class rank              |

### Parent portal

| Feature                                                       | Spec ref                   | Status                                                                                     |
| --------------------------------------------------------------- | --------------------------- | --------------------------------------------------------------------------------------------- |
| View all enrolled children (child switcher)                    | Scope decision              | ❌ confirmed in scope; parent portal must support 1..n children per parent                    |
| View each child's attendance                                   | FR-8, Use case              | ❌ placeholder only                                                                            |
| View each child's academic results                             | FR-10                       | ❌                                                                                             |
| View each child's fee balance + full payment history           | FR-12                       | ❌                                                                                             |
| **View / download fee receipt**                                | 🆕                          | ❌                                                                                             |
| Report card summary (per child)                                | Figure 8 mockup             | ❌                                                                                             |
| School announcements (targeted, not just school-wide blast)    | Figure 8 mockup 🆕          | ❌ now includes grade-level targeting (all classes in a grade level)                           |
| **Overdue-fee notification banner**                             | 🆕                          | ❌                                                                                             |

### Student portal

| Feature                          | Spec ref          | Status                                                   |
| ---------------------------------- | ------------------ | ----------------------------------------------------------- |
| View personal results / CA marks | FR-11 (user req.)  | ❌ placeholder only                                          |
| View personal attendance history | Use case           | ❌                                                            |
| View / download report card      | Use case           | ❌                                                            |
| **View class timetable (read-only)** | 🆕              | ❌                                                            |

---

## Phase 0 — Environment: move dev DB to MySQL

*(unchanged from original plan)*

- [ ] Start XAMPP, enable the MySQL module, confirm it's reachable on `127.0.0.1:3306`
- [ ] Create a new schema, e.g. `grail_dev`, via phpMyAdmin or `mysql -u root`
- [ ] Update `.env` to `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=grail_dev`
- [ ] `php artisan config:clear`
- [ ] `php artisan migrate:fresh --seed` against the new MySQL schema
- [ ] Update `.env.example` and §14 of `PROJECT_DOCUMENTATION.md`

**Exit checklist before Phase 1:**
- [ ] `php artisan migrate:status` shows all migrations run against MySQL with no errors
- [ ] All seeders complete without FK-constraint errors
- [ ] App boots and logs in as each of the 4 seeded roles against the MySQL DB
- [ ] `.env.example` and doc updated

**🧪 Suggested tests:** none required — this is infrastructure, not behavior. A passing `php artisan test` run against MySQL (even the existing suite) is the acceptance signal.

---

## Phase 1 — Fix the known Fee status bug

*(unchanged — completed 2026‑08‑05)*

- [x] Replace `Fee::where('status', 'paid')` with `Fee::cleared()` scope across admin/parent/student controllers
- [x] Re-seed and confirm the admin dashboard fee KPI reflects seeded data correctly

**Exit checklist before Phase 2:** ✅ all items complete (see original plan for detail).

**🧪 Suggested tests:**
- [x] Feature test confirming the fee-status fix (`Fee::cleared()` returns the right count against seeded data)
- [x] No test relies on the literal string `'paid'`

---

## Phase 2 — Foundations: multi-item fee structure + audit trail 🆕

**Why here, not later:** every subsequent fee feature (receipts, overdue notifications, reporting) depends on fees having line items, and every subsequent financial/grade feature needs to be auditable from day one — retrofitting an audit trail after data already exists creates a gap in the record.

- [ ] Migration: `fee_items` table — `fee_item_id`, `fee_id` (FK), `item_name`, `category` (string — admin-configurable, with pre-seeded suggestions: `Tuition`, `Examination`, `Development Levy`, `Uniform & Sports`, `Other`), `amount`
- [ ] Admin settings screen to manage fee item categories (add/edit/delete)
- [ ] Update `Fee` model: `amount_due` becomes derived (`sum of fee_items.amount`) rather than a manually-entered value; keep `amount_due` as a stored/cached column for query performance, recalculated on item add/remove
- [ ] Update admin fee-creation UI to add/remove line items dynamically before saving
- [ ] Migration: `audit_logs` table — `id`, `user_id` (who), `auditable_type`, `auditable_id`, `action` (created/updated/deleted), `old_values` (json), `new_values` (json), `reason` (text, nullable — why the change was made), `ip_address`, `user_agent`, `created_at`
- [ ] Add an `Auditable` trait (or Laravel model event hooks) to `Fee`, `FeeItem`, `Grade`, `Student`, and `Teacher` — the categories flagged in the Critical Review as compliance-sensitive
- [ ] Admin-only audit log viewer (filterable by model type, date range, user, and with a "reason" column displayed)

**Exit checklist before Phase 3:**
- [ ] Creating/editing a fee with multiple line items produces a correct `amount_due` and balance
- [ ] Editing a `Grade` or `Fee` writes a row to `audit_logs` with correct before/after values and the user's IP/User-Agent
- [ ] Audit log viewer is reachable only by admin (403 for other roles)
- [ ] Existing seeded fees migrate cleanly to the new `fee_items` structure (write a one-off migration/seeder update, don't leave old single-line fees orphaned)
- [ ] Admin can add a new fee category through the settings screen and use it immediately

**🧪 Suggested tests:**
- [ ] Feature test: creating a fee with 3 `fee_items` produces the correct summed `amount_due`
- [ ] Feature test: removing a `fee_item` recalculates `amount_due` and doesn't break an existing `Cleared` status incorrectly
- [ ] Feature test: updating a `Grade`'s score writes exactly one `audit_logs` row with correct `old_values`/`new_values`
- [ ] Feature test: a non-admin role gets 403 on the audit log route
- [ ] Feature test: audit log records the `reason` field when provided
- [ ] Feature test: new fee category appears in the dropdown immediately after creation

---

## Phase 3 — School calendar (academic years, terms, holidays) 🆕

**Why here:** attendance percentages, fee due dates, and grade "term" fields are currently free-text/implicit. Without a real calendar, attendance reporting (Phase 9) and promotion (Phase 6) have no basis for "how many school days were there."

- [ ] Migration: `academic_years` table — `id`, `label` (e.g. "2026"), `start_date`, `end_date`, `is_current`
- [ ] Migration: `terms` table — `id`, `academic_year_id` (FK), `name` (Term 1/2/3), `start_date`, `end_date`
- [ ] Migration: `holidays` table — `id`, `academic_year_id` (FK), `date`, `description` (excluded from attendance-day counts)
- [ ] Migration: `grade_levels` table — `grade_level_id`, `name` (e.g., "Grade 10"), `order` (10, 11, 12 for sorting)
- [ ] Migration: add `grade_level_id` to `school_classes` (FK to `grade_levels`)
- [ ] Backfill: link existing `grades.academic_year` (currently a raw integer) and `fees.academic_year` to the new `academic_years` table via a data migration
- [ ] Backfill: link existing `school_classes.grade_level` to the new `grade_levels` via a data migration
- [ ] Admin CRUD for academic years/terms/holidays/grade levels
- [ ] Add "current term" resolution helper (`Term::current()`) used anywhere a form currently free-types a term string

**Exit checklist before Phase 4:**
- [ ] At least one full academic year with 3 terms and a handful of holidays is seeded
- [ ] Existing `grades`/`fees` records correctly reference the backfilled academic year (spot-check, don't just trust the migration)
- [ ] Existing `school_classes` correctly reference the backfilled grade levels
- [ ] No view still lets a user free-type a term name where a `Term` selector should be used

**🧪 Suggested tests:**
- [ ] Feature test: `Term::current()` resolves correctly given today's date against seeded terms
- [ ] Feature test: attendance-day calculation excludes seeded holidays
- [ ] Unit test: overlapping terms within the same academic year are rejected at the model/validation layer
- [ ] Feature test: grade levels are ordered correctly (Grade 10 < Grade 11 < Grade 12)

---

## Phase 4 — Desktop-first: close the role-dashboard gap

*(this is the original Phase 2, with announcements and promotion split out into their own phases below — everything else unchanged)*

Build every ❌ and 🟡 item from the Teacher/Parent/Student tables above, desktop layout only, no breakpoint work yet.

- [ ] **Parent portal** — port `Frontend/ParentViews/index.html` into Blade, wired to real data:
    - [ ] Child switcher/selector for parents with more than one enrolled child
    - [ ] Attendance summary (per selected child)
    - [ ] Academic results (latest grades, subject breakdown, per selected child)
    - [ ] Fee balance + full payment history (per selected child, now using `fee_items` from Phase 2)
    - [ ] Report card summary with link to full report card
- [ ] **Student dashboard** — read-only views:
    - [ ] Personal results / CA marks
    - [ ] Personal attendance history
    - [ ] Report card (view/download)
- [ ] **Teacher side**:
    - [ ] Visual pass on `marks.blade.php`
    - [ ] New: class performance summary view (aggregate marks/attendance per class)
    - [ ] New: student profile view accessible from a teacher's class roster
- [ ] **Admin**:
    - [ ] Confirm report-card generation is actually wired to a route/button
- [ ] Confirm `CheckRole` middleware correctly scopes every new view

**Exit checklist before Phase 5:**
- [ ] Every ❌ row in the portal tables above (excluding announcements/promotion/reporting, handled separately) is now ✅
- [ ] Manually log in as one seeded user per role and confirm role-scoped data
- [ ] Seed at least one parent with **two or more** children; confirm the child switcher swaps data correctly
- [ ] Confirm a parent cannot view another parent's child's data by manipulating the child-selector's ID
- [ ] Attempt to access another role's route while authenticated as a different role — confirm 403

**🧪 Suggested tests:**
- [ ] Feature tests: parent sees only their own children's attendance/results/fees
- [ ] Feature test: multi-child parent's switcher returns correct data per child, and rejects a child ID not belonging to that parent (403, not empty state)
- [ ] Feature test: student can view own results/attendance but not another student's
- [ ] Feature test: teacher's class performance view only aggregates classes that teacher is assigned to

---

## Phase 5 — Announcements with targeting 🆕

**Why its own phase:** the original plan buried this as a sub-bullet of Phase 2 with no targeting model. The Critical Review flags targeting as a Should-Have that needs its own admin authoring flow before the parent-facing view has anything real to show.

- [ ] Migration: update `announcements` table
    - Keep `audience` enum: `all` / `class` / `grade_level`
    - Remove `target_class_id`
    - Add `targetable_type` (string, nullable)
    - Add `targetable_id` (unsignedBigInteger, nullable)
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

## Phase 6 — Student promotion & year-end rollover 🆕

**Why here:** this is a Must-Have — without it the system only works for one academic year, which the Critical Review calls out explicitly as breaking at year-end.

- [ ] Admin screen: "Promote students" — bulk action at the end of an academic year, scoped by current class
- [ ] Logic: move a selected set of students from their current `class_id` to a target class (typically "next grade up") — admin selects the target class from a dropdown filtered by the next grade level
- [ ] Students not selected can be explicitly marked "Retained" (stay in current grade level, re-assigned to a new class instance for the new academic year)
- [ ] Handle graduating students (final grade level) — mark as graduated/inactive rather than promoted into a non-existent class
- [ ] New academic year must be created (Phase 3's `academic_years`) before promotion can run; guard against promoting into a non-existent year
- [ ] Promotion action is itself audit-logged (Phase 2's `audit_logs`) — who promoted whom, and when
- [ ] Admin can pre-configure promotion mappings (e.g., 10A → 11A, 10B → 11B) to speed up the process

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

## Phase 7 — Fee receipts + overdue notifications 🆕

- [ ] Print-friendly HTML receipt view (per Critical Review's simplified-scope decision — not a sophisticated PDF, reuse dompdf-to-HTML approach already installed for report cards)
- [ ] Receipt shows: student, itemized `fee_items`, amount paid, payment date(s), running balance
- [ ] Parent portal: "download receipt" link per payment
- [ ] Overdue detection: a scheduled command (`php artisan schedule`) flags fees past `due_date` with `status != Cleared`
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

## Phase 9 — Reporting suite: attendance, fees, class performance, data export + school-wide performance report 🆕

- [ ] Attendance reports: by student, by class, by subject, by date range — using Phase 3's calendar to compute correct attendance percentages (excluding holidays)
- [ ] Fee reports: outstanding balances, aging (30/60/90+ days overdue), by class, by student
- [ ] Teacher class-performance view: score distributions, averages, trend across terms (Phase 3 gives correct term boundaries)
- [ ] Data export: CSV/Excel export button on the above reports (Laravel Excel or simple CSV streaming — no need for the full library if scope is just flat exports)

### 9.x School-Wide Performance Report (New — reinstated from descoped)

- [ ] Admin-only report accessible from the admin sidebar
- [ ] Filters: academic year, term (using Phase 3's calendar)
- [ ] Report sections:
    - **Overview KPIs:** Total students, by grade level, by class, overall pass rate (students with ≥50% average)
    - **Performance by Grade Level:** Students, average score, pass rate, top student, bottom student
    - **Performance by Subject:** Students, average score, pass rate, top class, bottom class
    - **Class Performance Summary:** Class, homeroom teacher, students, average score, pass rate, rank
    - **Low-Performing Student Alert:** Students with average below threshold (configurable, default 40%)
- [ ] "Export to CSV" button for each section (reusing Phase 9's data export functionality)
- [ ] Cache: Results cached for 1 hour, invalidated on grade updates

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

## Phase 10 — Report card enhancements + read-only timetable + class rank 🆕

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
- [ ] Migration: add `finalized_rank`, `finalized_at`, `finalized_by` columns to `grades` table
- [ ] Add `comment` field to `Grade` (or a new `report_card_comments` table keyed by student+term+subject if comments should be per-subject rather than per-grade-entry) — teacher-authored
- [ ] Term/year averaging calculation, surfaced on the report card
- [ ] Attendance summary block on the report card (days present/absent/late for the term, using Phase 3's calendar)
- [ ] Confirm dompdf is wired end-to-end: route → view → downloadable PDF containing all of the above
- [ ] Read-only class timetable: simple `timetable_slots` table (`class_subject_id`, `day_of_week`, `start_time`, `end_time`) with an admin-entry screen; student/parent/teacher views are display-only (no conflict detection — that's a v2.0 concern, see Part 2)

**Exit checklist before Phase 11:**
- [ ] A generated report card PDF contains: marks, max score, letter grade, teacher comment, attendance summary, term average, **and class rank**
- [ ] The "Finalize" workflow correctly assigns ranks and handles ties
- [ ] A student with no final rank shows "N/A" on the report card
- [ ] "Unfinalize" action clears rank and logs the action in audit trail
- [ ] A seeded timetable renders correctly on student/parent/teacher dashboards, read-only

**🧪 Suggested tests:**
- [ ] Feature test: rank calculation assigns correct ranks with and without ties
- [ ] Feature test: "Finalize" validation rejects missing grades
- [ ] Feature test: report card PDF contains the rank when finalized
- [ ] Feature test: report card PDF shows "N/A" when not finalized
- [ ] Feature test: "Unfinalize" clears rank and writes audit log
- [ ] Feature test: report-card PDF generation succeeds and contains the teacher's comment text
- [ ] Feature test: term average calculation matches a manual calculation against seeded scores
- [ ] Feature test: timetable view returns only slots for the student's own class (role-scoping again)

---

## Phase 11 — Admin account management & audit log UX polish 🆕

- [ ] Dedicated "Manage user accounts" screen: activate/deactivate (`is_active` toggle, already in schema), password reset trigger, role assignment — currently only reachable implicitly through student/teacher/parent CRUD
- [ ] Audit log viewer UX pass: filters by date range, user, model type (built functionally in Phase 2 — this is the polish/usability pass, deferred here so it lands after there's real audit data from Phases 2–10 to filter through)

**Exit checklist before Phase 12:**
- [ ] Admin can deactivate a user and confirm that user can no longer log in
- [ ] Admin can filter the audit log by a specific user and date range and get correct results

**🧪 Suggested tests:**
- [ ] Feature test: deactivated user (`is_active = false`) is denied login even with correct credentials
- [ ] Feature test: audit log filter by user_id + date range returns only matching rows

---

## Phase 12 — Business-logic test consolidation

*(originally Phase 4 — expanded to cover everything added in Phases 2–11, not just the original Fee/Grade scope)*

- [ ] Feature tests for `Fee::recordPayment()` / `reversePayment()` covering every transition in Figure 6, now against multi-item fees
- [ ] Feature tests for grade-letter thresholds and `score ≤ max_score`
- [ ] Feature tests for role-scoping across every new view added since Phase 4
- [ ] Feature tests for class rank calculation and finalize grades workflow
- [ ] Feature tests for school-wide performance report queries
- [ ] Feature tests for grade-level announcement targeting
- [ ] Confirm all the "🧪 Suggested tests" boxes from Phases 2–11 are actually checked off, not just aspirational

**Exit checklist before Phase 13:**
- [ ] `php artisan test` passes fully against the MySQL dev DB
- [ ] Every state-chart transition in Figure 6 has a passing test
- [ ] No test relies on hardcoded IDs that only happen to exist because of seeder run order
- [ ] Test coverage measured (Critical Review flags this as "Unknown" — get an actual number, target 80% per Appendix C)

**🧪 Suggested tests:** this phase *is* the test consolidation — the deliverable is the test suite itself, plus a coverage report.

---

## Phase 13 — Mobile-responsiveness pass

*(originally Phase 5, unchanged — now covers everything built in Phases 4–11, not just the original Phase 2 scope)*

- [ ] Parent portal → card-based, vertically-stacked layout, tested down to 360px
- [ ] Teacher mark-entry / attendance → single-viewport, vertically-scrollable at 360px
- [ ] Reports and receipts (Phases 7, 9) → confirm tables degrade gracefully on mobile (horizontal scroll acceptable for wide report tables, but must be a deliberate scroll container, not an overflow accident)
- [ ] Touch target audit (44×44px minimum)
- [ ] 3G-throttled load-time check against the NFR target

**Exit checklist before Phase 14:**
- [ ] Every screen from Phases 4–11 renders with no *accidental* horizontal scroll at 360px
- [ ] Touch targets measured at 44×44px minimum on the highest-traffic screens
- [ ] Load-time recorded for parent dashboard, teacher mark-entry, and at least one report screen under throttled 3G

**🧪 Suggested tests:**
- [ ] Automated: none reliably test CSS layout — rely on the manual checklist above, but do add a regression test for any JS-driven responsive behavior (e.g. a collapsing nav) if one is built

---

## Phase 14 — Production/deployment alignment

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
- [ ] Session-timeout policy (Phase 14) is the interim mitigation; add 2FA once adoption is stable
- **Suggested tests:** 2FA challenge required after password step; recovery-code flow test.

### A4 — Advanced timetabling with conflict detection
- [ ] Extend Phase 10's read-only `timetable_slots` with conflict detection (same teacher/class/room double-booked)
- [ ] Admin-facing drag-and-drop or form-based editor
- **Suggested tests:** conflict-detection test (overlapping slots for same teacher rejected).

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

---

## Decisions carried over from the original plan

- **Multi-child parents: in scope** for v1.0 (Phase 4).
- **School announcements: in scope**, and now with grade-level targeting (Phase 5).
- **School-wide performance report: reinstated** as a v1.0 feature (Phase 9). The descoping decision has been reversed.
- **Class rank on report cards: in scope** for v1.0 via the "finalize grades" workflow (Phase 10).

## Resolved decisions (formerly open items)

| Decision | Resolution | Rationale | Revisit if... |
|---|---|---|---|
| **Fee item categories** | Configurable string field with pre-seeded suggestions. Admin can add/edit/delete categories via settings screen. | Schools have different fee structures; a fixed enum would limit flexibility. The pre-seeded suggestions cover common categories. | The pre-seeded suggestions don't cover a school's needs — they can add more. |
| **Grade-level targeting for announcements** | Use polymorphic `targetable_type`/`targetable_id` on `announcements` table. Supports targeting multiple classes and/or grade levels. | Allows admins to target all classes in a grade level with one announcement, avoiding duplication. | A simpler string-based approach is preferred — swap before Phase 5 ships. |
| **Class rank on report cards** | Implement via "finalize grades" workflow. Teacher clicks "Finalize" → system calculates ranks and locks grades. Admin can override. | Addresses the dependency concern (needs all grades finalized) while delivering the feature. The workflow is a common pattern in SIS systems. | Teachers find the finalization workflow too burdensome — can add auto-finalization after a deadline in v1.1. |
| **School-wide performance report** | Minimal viable version: read-only report with 5 sections and CSV export. Cached for 1 hour. | Reinstates the spec's Figure 3 use case with minimal effort (2-3 days of work). Caching prevents performance issues. | Users request interactive charts or drill-down — build in v1.1. |
| **Report card format** | v1.0 layout includes: subject/CA/exam/total/letter grade/teacher comment, attendance summary, term average, class rank, overall class-teacher comment, term dates. | The finalize grades workflow makes class rank feasible. Collected real report card sample confirms layout matches expectations. | A collected real report card sample (see Phase 10's checklist) shows a different expected layout — adjust before wiring the PDF. |
| **MariaDB vs. real MySQL 8.0** | Keep XAMPP's bundled MariaDB. Update the spec to read "MySQL 8.0-compatible (MariaDB 10.x via XAMPP)" rather than installing genuine MySQL. | Nothing in this plan needs a MySQL-8-only feature MariaDB lacks — `audit_logs`' JSON columns work fine on MariaDB's JSON type via Laravel's `json` casts. Installing real MySQL alongside XAMPP is setup friction with no functional payoff. | A specific MySQL-8-only feature turns out to be needed later (unlikely given current scope). |
| **Desktop-first vs. mobile-first framing** | Add one sentence to §4.1.3: "Development sequencing (desktop-first) is an engineering choice — build correctness before adapting layout — and is independent of the design priority established by the survey data, which remains mobile-first for the deployed product." | Closes the doc/plan disagreement without changing anything about how development is actually sequenced. | N/A — this is a documentation fix, not a behavior decision. |
| **Permanently descoped features** | None. All features from the spec are either in v1.0 (including reinstated school-wide report and class rank) or deferred to v2.0 (PWA, notifications, 2FA, messaging, advanced timetabling). | The Critical Review's descoping recommendation for the school-wide report has been reversed. | A stakeholder explicitly requests removal of a feature — handle as a formal change request. |

## Remaining open items

| Item | Owner | Status |
|------|-------|--------|
| Collect real report card sample from a teacher (Phase 10) | Team | Pending |
| Confirm UAT participant availability (Phase 14) | Team | Pending |
| Verify XAMPP's MariaDB version compatibility with JSON columns | Dev Lead | Pending |
| Decide on CSV export library (Laravel Excel vs custom streaming) | Dev Lead | Pending |

---
