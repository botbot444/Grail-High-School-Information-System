# Database Seeders and Factories

> Last updated: 2026-09-16
> Update this file when seeders or factories change.

---

## Seeders

All located in `database/seeders/` — **19** files (18 domain seeders + the orchestrator). `DatabaseSeeder` call order:

| Seeder                   | Purpose                                               |
| ------------------------ | ----------------------------------------------------- |
| `DatabaseSeeder.php`     | Orchestrator                                          |
| `RoleSeeder.php`         | Inserts `admin`, `teacher`, `parent`, `student` roles |
| `AdminSeeder.php`        | Creates initial admin user                            |
| `SubjectSeeder.php`      | Sample subjects                                       |
| `TeacherSeeder.php`      | Sample teachers                                       |
| `ParentSeeder.php`       | Sample parents                                        |
| `SchoolClassSeeder.php`  | Sample school classes                                 |
| `ClassSubjectSeeder.php` | Wires classes ↔ subjects ↔ teachers                   |
| `StudentSeeder.php`      | Sample students                                       |
| `AttendanceSeeder.php`   | Sample attendance records                             |
| `GradeSeeder.php`        | Sample grades                                         |
| `FeeSeeder.php`          | Sample fees                                           |
| `FeeCategorySeeder.php`  | Fee category catalog                                  |
| `AuditLogSeeder.php`     | Sample audit log rows                                 |
| `GradeLevelSeeder.php`   | Canonical grade levels                                |
| `AcademicYearSeeder.php` | Academic year (and related calendar seed data)        |
| `TimetableSeeder.php`    | Periods + timetable slots for the sample classes       |
| `AssignmentSeeder.php`   | Sample assignments (and submissions)                  |
| `AnnouncementSeeder.php` | Sample announcements, targets and read rows            |

---

## Factories

All located in `database/factories/` — **12** factories.

| Factory                    | Purpose                        |
| -------------------------- | ------------------------------ |
| `AttendanceFactory.php`    | Attendance records for testing |
| `ClassSubjectFactory.php`  | Class–subject assignments      |
| `FeeFactory.php`           | Fee records for testing        |
| `GradeFactory.php`         | Grade records for testing      |
| `ParentProfileFactory.php` | Parent profiles for testing    |
| `PeriodFactory.php`        | Timetable periods for testing  |
| `SchoolClassFactory.php`   | School classes for testing     |
| `StudentFactory.php`       | Student records for testing    |
| `SubjectFactory.php`       | Subjects for testing           |
| `TeacherFactory.php`       | Teacher records for testing    |
| `TimetableSlotFactory.php` | Timetable slots for testing    |
| `UserFactory.php`          | Users for testing              |

---

_End of seeders and factories documentation._
