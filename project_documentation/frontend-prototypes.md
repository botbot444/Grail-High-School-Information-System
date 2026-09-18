# Frontend Prototypes (removed)

> Last updated: 2026-09-18
> Update this file when frontend prototypes change.

---

## 10. Current state: the prototypes have been deleted

The static mockups this file used to catalogue were **never served by Laravel** and have now been **removed from the
repository**. The Blade portals under `resources/views/` are the live UI; no file in `app/`, `routes/` or
`resources/` references the directories below any more. They are recorded here only as the provenance of the
current screens.

| Former directory                   | Former contents                                                                                                                                                                                                                                                      |
| ---------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Frontend/AdminViews/`             | `admin_dashboard.html`, `attendance_view.html`, `create_student.html`, `editTeacher.html`, `exam_management.html`, `settings.html`, `student_management.html`, `student_profile.html`, `teacher_management.html`, `timetable_management.html`                        |
| `Frontend/ParentViews/`            | `parentportal.html`                                                                                                                                                                                                                                                  |
| `stitch_grail_sis_teacher_portal/` | Stitch `code.html` screens (plus `screen.png`) per teacher destination, and `academic_command/DESIGN.md` holding the colour / type tokens for the teacher portal                                                                                                        |

---

## 10.1 Where those designs ended up

| Former Stitch folder              | Live integration                                        |
| --------------------------------- | ------------------------------------------------------- |
| `teacher_dashboard/`              | `teacher.dashboard`                                     |
| `my_classes/`                     | `teacher.classes`                                       |
| `marks_grades_entry/`             | `teacher.marks`                                         |
| `teacher_timetable/`              | `teacher.timetable` (read-only grids per class)         |
| `record_attendance/`              | `teacher.attendance` (`GET`/`POST teacher.attendance`)  |
| `class_performance_summary/`      | `teacher.performance` (includes grade finalization)     |
| `class_roster_teacher_portal/`    | `teacher.classes.roster` and `teacher.students.show`    |
| `announcements_messages/`         | `teacher.announcements` (read-only + read tracking)     |
| `academic_command/DESIGN.md`      | Colour / type tokens for the teacher portal             |

The admin and parent prototypes were likewise superseded by the Blade admin (`resources/views/admin/`) and parent
(`resources/views/parent/`) portals. `teacher.settings` is a real page (previously a placeholder), and
`teacher.placeholder` remains available as a fallback for any destination that has not yet been ported.


---

_End of frontend prototypes documentation._
