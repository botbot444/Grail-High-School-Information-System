# Frontend Prototypes

> Last updated: 2026-09-13
> Update this file when frontend prototypes change.

---

Static mockups are **not** served by Laravel. Blade portals are the live UI.

---

## 10.1 AdminViews (`Frontend/AdminViews/`)

- `admin_dashboard.html`
- `attendance_view.html`
- `create_student.html`
- `editTeacher.html`
- `exam_management.html`
- `settings.html`
- `student_management.html`
- `student_profile.html`
- `teacher_management.html`
- `timetable_management.html`

---

## 10.2 ParentViews (`Frontend/ParentViews/`)

- `parentportal.html`

The live parent portal is Blade under `resources/views/parent/`, not this HTML file.

---

## 10.3 Teacher Stitch screens (`stitch_grail_sis_teacher_portal/`)

HTML `code.html` (and `screen.png` where present) used as design sources:

| Folder | Live integration |
| ------ | ---------------- |
| `teacher_dashboard/` | Wired as `teacher.dashboard` |
| `my_classes/` | Wired as `teacher.classes` |
| `marks_grades_entry/` | Existing `teacher.marks` (not yet a full Stitch restyle) |
| `teacher_timetable/` | Placeholder route `teacher.timetable` |
| `record_attendance/` | Placeholder `teacher.attendance` (marks form still records P/A/L) |
| `class_performance_summary/` | Placeholder `teacher.performance` |
| `class_roster_teacher_portal/` | Not a dedicated route yet |
| `announcements_messages/` | Placeholder `teacher.announcements` |
| `academic_command/DESIGN.md` | Colour / type tokens for the teacher portal |

---

_End of frontend prototypes documentation._
