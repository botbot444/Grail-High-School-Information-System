<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherEditPageTest extends TestCase
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

    private function makeTeacher(): Teacher
    {
        $role = Role::firstOrCreate(['name' => 'teacher']);
        $user = User::create([
            'name' => 'Sarah Chen',
            'email' => 'sarah.chen@grail.edu',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
        ]);

        return Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Sarah',
            'last_name' => 'Chen',
            'email' => 'sarah.chen@grail.edu',
            'phone' => '+260 97 000 0000',
        ]);
    }

    public function test_admin_can_view_the_edit_teacher_page_with_current_values(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher();
        $class = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => $teacher->teacher_id,
        ]);
        $subject = Subject::create(['subject_name' => 'Mathematics']);
        $teacher->subjects()->attach($subject->subject_id);

        $response = $this->actingAs($admin)->get(route('admin.teachers.edit', $teacher));

        $response->assertStatus(200);
        $response->assertSee('Edit Teacher');
        $response->assertSee('value="Sarah"', false);
        $response->assertSee('value="sarah.chen@grail.edu"', false);
        $response->assertSee('name="class_ids[]" type="checkbox" value="'.$class->class_id.'"', false);
        $response->assertSee('name="subject_ids[]" type="checkbox" value="'.$subject->subject_id.'"', false);
        $response->assertDontSee('Date of Birth');
        $response->assertDontSee('Qualifications &amp; Degrees', false);
    }

    public function test_admin_can_update_teacher_profile_and_assignments(): void
    {
        $admin = $this->makeAdmin();
        $teacher = $this->makeTeacher();
        $oldClass = SchoolClass::create([
            'class_name' => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id' => $teacher->teacher_id,
        ]);
        $newClass = SchoolClass::create([
            'class_name' => '11B',
            'grade_level' => 'Grade 11',
            'teacher_id' => null,
        ]);
        $oldSubject = Subject::create(['subject_name' => 'Mathematics']);
        $newSubject = Subject::create(['subject_name' => 'Physics']);
        $teacher->subjects()->attach($oldSubject->subject_id);

        $response = $this->actingAs($admin)->put(route('admin.teachers.update', $teacher), [
            'first_name' => 'Eleanor',
            'last_name' => 'Vance',
            'email' => 'eleanor.vance@grail.edu',
            'phone' => '+260 97 111 1111',
            'class_ids' => [$newClass->class_id],
            'subject_ids' => [$newSubject->subject_id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.teachers.show', $teacher));

        $teacher->refresh();
        $this->assertSame('Eleanor', $teacher->first_name);
        $this->assertSame('Vance', $teacher->last_name);
        $this->assertSame('eleanor.vance@grail.edu', $teacher->email);
        $this->assertSame('+260 97 111 1111', $teacher->phone);
        $this->assertSame($teacher->teacher_id, $newClass->refresh()->teacher_id);
        $this->assertNull($oldClass->refresh()->teacher_id);
        $this->assertDatabaseHas('teacher_subjects', [
            'teacher_id' => $teacher->teacher_id,
            'subject_id' => $newSubject->subject_id,
        ]);
        $this->assertDatabaseMissing('teacher_subjects', [
            'teacher_id' => $teacher->teacher_id,
            'subject_id' => $oldSubject->subject_id,
        ]);
    }
}
