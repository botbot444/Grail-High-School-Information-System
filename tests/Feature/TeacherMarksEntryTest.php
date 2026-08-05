<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherMarksEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_marks_entry_form_for_assigned_classes(): void
    {
        $teacherUser = User::create([
            'name' => 'John Doe',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'teacher@example.com',
        ]);

        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => $teacher->teacher_id,
        ]);

        $subject = Subject::create([
            'subject_name' => 'Mathematics',
        ]);

        $classSubject = ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id,
        ]);

        $studentUser = User::create([
            'name' => 'Jane Smith',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'date_of_birth' => '2010-01-01',
            'gender' => 'Female',
            'student_number' => 'ST-1002',
            'class_id' => $class->class_id,
            'enrolment_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($teacherUser)->get(route('teacher.marks'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('Jane Smith');
    }

    public function test_teacher_can_store_marks_and_attendance(): void
    {
        $teacherUser = User::create([
            'name' => 'John Doe',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'teacher@example.com',
        ]);

        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => $teacher->teacher_id,
        ]);

        $subject = Subject::create([
            'subject_name' => 'Mathematics',
        ]);

        $classSubject = ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id,
        ]);

        $studentUser = User::create([
            'name' => 'Jane Smith',
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'date_of_birth' => '2010-01-01',
            'gender' => 'Female',
            'student_number' => 'ST-1002',
            'class_id' => $class->class_id,
            'enrolment_date' => '2026-01-01',
        ]);

        $response = $this->actingAs($teacherUser)->post(route('teacher.marks.store'), [
            'assignment_id' => $classSubject->class_subject_id,
            'marks' => [
                $student->student_id => 85,
            ],
            'attendance' => [
                $student->student_id => 'P',
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $grade = Grade::where('student_id', $student->student_id)
            ->where('class_subject_id', $classSubject->class_subject_id)
            ->first();

        $this->assertNotNull($grade);
        $this->assertEquals(85, $grade->score);
        $this->assertEquals(85, $grade->marks); // through accessor
        $this->assertEquals($teacher->teacher_id, $grade->recorded_by);

        $attendance = Attendance::where('student_id', $student->student_id)
            ->where('class_subject_id', $classSubject->class_subject_id)
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('Present', $attendance->status);
        $this->assertEquals($teacher->teacher_id, $attendance->recorded_by);
    }
}
