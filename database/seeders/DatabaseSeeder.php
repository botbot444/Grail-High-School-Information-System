<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Fee;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            // The calendar must exist before grades, fees or timetables are
            // seeded — they anchor to a term_id, and a term cannot be resolved
            // from a table that has not been populated yet.
            AcademicYearSeeder::class,
            SubjectSeeder::class,
            TeacherSeeder::class,
            SchoolClassSeeder::class,
            ClassSubjectSeeder::class,
            StudentSeeder::class,
            // Must follow StudentSeeder: it links a parent to every student that
            // does not have one, which finds nothing if students do not exist yet.
            ParentSeeder::class,
            AttendanceSeeder::class,
            GradeSeeder::class,
            FeeSeeder::class,
            FeeCategorySeeder::class,
            AuditLogSeeder::class,
            GradeLevelSeeder::class,
            AssignmentSeeder::class,
            TimetableSeeder::class,
            // Needs classes and grade levels to aim at.
            AnnouncementSeeder::class,
        ]);
    }
}
