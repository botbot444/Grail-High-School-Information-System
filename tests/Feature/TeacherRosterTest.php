<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_teacher_can_view_the_class_roster(): void
    {
        [$teacherUser, $teacher, $class] = $this->classContext('roster-teacher@test.local', '10A');
        $studentUser = User::create(['name' => 'Roster Student', 'email' => 'roster-student@test.local', 'password' => 'password', 'role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->id, 'first_name' => 'Roster', 'last_name' => 'Student',
            'date_of_birth' => '2010-01-01', 'gender' => 'Female', 'student_number' => 'ROSTER-1',
            'class_id' => $class->class_id, 'enrolment_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($teacherUser)->get(route('teacher.classes.roster', $class->class_id));

        $response->assertOk()->assertSee($student->full_name)->assertSee($student->student_number)->assertSee($class->display_name);
    }

    public function test_teacher_cannot_open_another_teachers_roster(): void
    {
        [$teacherUser] = $this->classContext('roster-owner@test.local', '10A');
        [, , $otherClass] = $this->classContext('roster-other@test.local', '11A');

        $this->actingAs($teacherUser)
            ->get(route('teacher.classes.roster', $otherClass->class_id))
            ->assertNotFound();
    }

    private function classContext(string $email, string $className): array
    {
        $user = User::create(['name' => 'Roster Teacher', 'email' => $email, 'password' => 'password', 'role' => 'teacher']);
        $teacher = Teacher::create(['user_id' => $user->id, 'first_name' => 'Roster', 'last_name' => 'Teacher', 'email' => $email.'-profile']);
        $class = SchoolClass::create(['class_name' => $className, 'grade_level' => 'Grade '.substr($className, 0, 2), 'teacher_id' => $teacher->teacher_id]);

        return [$user, $teacher, $class];
    }
}