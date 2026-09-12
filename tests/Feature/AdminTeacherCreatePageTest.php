<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherCreatePageTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_the_add_teacher_page(): void
    {
        $admin = $this->makeAdmin();

        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => null,
        ]);
        $subject = Subject::create(['subject_name' => 'Mathematics']);

        $response = $this->actingAs($admin)->get(route('admin.teachers.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New Teacher');
        $response->assertSee('Personal Information', false);
        $response->assertSee('Academic Assignments &amp; Workload', false);
        $response->assertSee('Contact Details', false);
        // Data-driven options are rendered from the real tables
        $response->assertSee('Mathematics', false);
        $response->assertSee('10A', false);
    }

    public function test_store_creates_user_teacher_and_assignments(): void
    {
        $admin = $this->makeAdmin();

        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => null,
        ]);
        $subject = Subject::create(['subject_name' => 'Mathematics']);

        $response = $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'first_name' => 'Eleanor',
            'last_name' => 'Vance',
            'email' => 'eleanor.vance@grail.edu',
            'phone' => '+260 97 000 0000',
            'class_ids' => [$class->class_id],
            'subject_ids' => [$subject->subject_id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.teachers.index'));

        $teacherUser = User::where('email', 'eleanor.vance@grail.edu')->first();
        $this->assertNotNull($teacherUser);
        $this->assertSame('Eleanor Vance', $teacherUser->name);

        $teacher = Teacher::where('email', 'eleanor.vance@grail.edu')->first();
        $this->assertNotNull($teacher);
        $this->assertSame($teacherUser->id, $teacher->user_id);
        $this->assertSame('+260 97 000 0000', $teacher->phone);

        // Homeroom class assigned
        $class->refresh();
        $this->assertSame($teacher->teacher_id, $class->teacher_id);

        // Subject assignment is independent of the homeroom class assignment.
        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id' => $teacher->teacher_id,
            'subject_id' => $subject->subject_id,
        ]);
        $this->assertDatabaseMissing('class_subjects', [
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id,
        ]);

        $directory = $this->actingAs($admin)->get(route('admin.teachers.index'));
        $directory->assertStatus(200);
        $directory->assertSee('Mathematics');
        $directory->assertSee('10A');
    }

    public function test_store_validates_required_fields_and_unique_email(): void
    {
        $admin = $this->makeAdmin();

        $missing = $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'first_name' => '',
            'email' => 'not-an-email',
        ]);

        $missing->assertSessionHasErrors(['first_name', 'last_name', 'email']);

        // Existing email is rejected by the unique rule
        Role::firstOrCreate(['name' => 'teacher']);
        $existingUser = User::create([
            'name' => 'Existing',
            'email' => 'taken@grail.edu',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        Teacher::create([
            'user_id' => $existingUser->id,
            'first_name' => 'Existing',
            'last_name' => 'Teacher',
            'email' => 'taken@grail.edu',
        ]);

        $duplicate = $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'taken@grail.edu',
            'phone' => '0977777777',
        ]);

        $duplicate->assertSessionHasErrors(['email']);
    }
}