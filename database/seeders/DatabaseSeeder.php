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
            SubjectSeeder::class,
            TeacherSeeder::class,
            ParentSeeder::class,
            SchoolClassSeeder::class,
            ClassSubjectSeeder::class,
            StudentSeeder::class,
            AttendanceSeeder::class,
            GradeSeeder::class,
            FeeSeeder::class,
            FeeCategorySeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
