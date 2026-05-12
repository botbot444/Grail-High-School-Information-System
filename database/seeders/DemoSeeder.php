<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a Demo Teacher
        $teacher = User::create([
            'name' => 'Mr.Chanda',
            'email' => 'teacher@grail.com',
            'password' => bcrypt('pass1234'),
            'role' => 'teacher',
        ]);

        // 2. Create a Class (Grade 10A)
        $class = SchoolClass::create(['grade_level' => 10, 'section' => 'A']);

        // 3. Create a Subject
        $subject = Subject::create(['name' => 'Mathematics', 'subject_code' => 'MATH10', 'category' => 'Core']);

        // 4. Assign Teacher to Subject in that Class
        ClassSubject::create([
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'academic_year' => '2026'
        ]);

        // 5. Create 10 Students in Grade 10A
        $num = 1000;
        $names = ['Musa', 'Joy', 'Chipo', 'Loveness', 'Mwape', 'Isaac', 'Precious', 'Kondwani', 'Blessing', 'Kelvin'];
        foreach ($names as $name) {
            Student::create([
                'school_class_id' => $class->id,
                'first_name' => $name,
                'last_name' => 'Zimba',
                'id' => rand(1000, 9999),
                'gender' => rand(0, 1) ? 'M' : 'F',
                'date_of_birth' => '2008-05-12',
                'enrolment_id' => $num++
            ]);
        }
    }
}