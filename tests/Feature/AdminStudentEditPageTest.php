<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentEditPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(array $overrides = []): Student
    {
        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => null,
        ]);

        return Student::create(array_merge([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'date_of_birth' => '2010-12-10',
            'gender' => 'Female',
            'student_number' => 'ST-2001',
            'class_id' => $class->class_id,
            'guardian_name' => 'Jane Doe',
            'guardian_phone' => '0999999999',
            'enrolment_date' => '2026-01-15',
        ], $overrides));
    }

    public function test_admin_can_view_the_edit_student_page(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $student = $this->makeStudent();

        $response = $this->actingAs($admin)->get(route('admin.students.edit', $student->student_id));

        $response->assertStatus(200);
        $response->assertSee('Edit Student: Ada Lovelace', false);
        $response->assertSee('ST-2001', false);
        $response->assertSee('Personal Information', false);
        $response->assertSee('Enrollment & Academic', false);
        $response->assertSee('Parent/Guardian Information', false);
        $response->assertSee('Delete Student', false);
    }

    public function test_edit_page_shows_computed_attendance_rate(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $student = $this->makeStudent();

        $teacherUser = User::create([
            'name' => 'Tina Teacher',
            'email' => 'tina.teacher@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'Tina',
            'last_name' => 'Teacher',
            'email' => 'tina.teacher@example.com',
            'phone' => '0977777777',
        ]);

        $subject = Subject::create(['subject_name' => 'Mathematics']);

        $classSubject = ClassSubject::create([
            'class_id' => $student->class_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id,
        ]);

        $statuses = collect(['Present', 'Absent', 'Late']);
        $statuses->each(function (string $status, int $i) use ($student, $classSubject, $teacher): void {
            Attendance::create([
                'student_id' => $student->student_id,
                'class_subject_id' => $classSubject->class_subject_id,
                'date' => sprintf('2026-02-%02d', $i + 1),
                'status' => $status,
                'recorded_by' => $teacher->teacher_id,
            ]);
        });

        $response = $this->actingAs($admin)->get(route('admin.students.edit', $student->student_id));

        $response->assertStatus(200);
        // (Present + Late) / total = 2/3 = 66.7%
        $response->assertSee('66.7%', false);
    }

    public function test_admin_can_update_a_student_from_the_edit_page(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $class = SchoolClass::create([
            'class_name' => '11B',
            'grade_level' => 'Grade 11',
            'teacher_id' => null,
        ]);

        $student = $this->makeStudent();

        $response = $this->actingAs($admin)->put(route('admin.students.update', $student->student_id), [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'date_of_birth' => '2010-03-03',
            'gender' => 'Female',
            'class_id' => $class->class_id,
            'guardian_name' => 'Mary Hopper',
            'guardian_phone' => '0988888888',
            'enrolment_date' => '2026-02-20',
        ]);

        $response->assertRedirect(route('admin.students.show', $student->student_id));

        $student->refresh();

        $this->assertSame('Grace', $student->first_name);
        $this->assertSame('Hopper', $student->last_name);
        $this->assertSame($class->class_id, $student->class_id);
        // Student number must remain untouched (read-only on the form)
        $this->assertSame('ST-2001', $student->student_number);
    }

    public function test_update_rejects_missing_required_fields(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $student = $this->makeStudent();

        $response = $this->actingAs($admin)->put(route('admin.students.update', $student->student_id), [
            'first_name' => '',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'date_of_birth', 'gender', 'class_id']);
    }
}
