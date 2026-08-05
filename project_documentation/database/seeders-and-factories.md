# Database Seeders and Factories

> Last updated: 2026-08-02
> Update this file when seeders or factories change.

---

## Seeders

All located in `database/seeders/`.

| Seeder                   | Purpose                                               |
| ------------------------ | ----------------------------------------------------- |
| `DatabaseSeeder.php`     | Orchestrator                                          |
| `RoleSeeder.php`         | Inserts `admin`, `teacher`, `parent`, `student` roles |
| `AdminSeeder.php`        | Creates initial admin user                            |
| `SchoolClassSeeder.php`  | Sample school classes                                 |
| `SubjectSeeder.php`      | Sample subjects                                       |
| `TeacherSeeder.php`      | Sample teachers                                       |
| `ClassSubjectSeeder.php` | Wires classes ↔ subjects ↔ teachers                   |
| `StudentSeeder.php`      | Sample students                                       |
| `ParentSeeder.php`       | Sample parents                                        |
| `AttendanceSeeder.php`   | Sample attendance records                             |
| `GradeSeeder.php`        | Sample grades                                         |
| `FeeSeeder.php`          | Sample fees                                           |

---

## Factories

All located in `database/factories/`.

| Factory                    | Purpose                        |
| -------------------------- | ------------------------------ |
| `AttendanceFactory.php`    | Attendance records for testing |
| `FeeFactory.php`           | Fee records for testing        |
| `GradeFactory.php`         | Grade records for testing      |
| `ParentProfileFactory.php` | Parent profiles for testing    |
| `SchoolClassFactory.php`   | School classes for testing     |
| `StudentFactory.php`       | Student records for testing    |
| `TeacherFactory.php`       | Teacher records for testing    |

---

_End of seeders and factories documentation._
