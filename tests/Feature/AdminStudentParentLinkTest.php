<?php

namespace Tests\Feature;

use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentParentLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_link_a_student_to_an_existing_parent_profile(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $parentUser = User::create([
            'name' => 'Parent User',
            'email' => 'parent@example.com',
            'password' => bcrypt('password'),
            'role' => 'parent',
        ]);

        $parent = ParentProfile::create([
            'user_id' => $parentUser->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '0999999999',
            'address' => '123 Main Street',
            'occupation' => 'Teacher',
            'national_id' => '123456/12/12/1234',
        ]);

        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.students.create'));

        $response->assertStatus(200);
        $response->assertSee('Link to existing', false);
        $response->assertSee('Jane Doe', false);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'date_of_birth' => '2010-12-10',
            'gender' => 'Female',
            'student_number' => 'ST-1001',
            'class_id' => $class->class_id,
            'parent_user_id' => $parent->user_id,
            'guardian_name' => '',
            'guardian_phone' => '',
            'enrolment_date' => '2026-01-15',
        ]);

        $response->assertRedirect(route('admin.students.index'));

        $student = Student::where('student_number', 'ST-1001')->first();

        $this->assertNotNull($student);
        $this->assertSame($parent->user_id, $student->parent_user_id);
    }
}
