<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\ParentProfile;
use Tests\TestCase;

class ParentPortalRenderTest extends TestCase
{
    /**
     * Self-seeds a demo parent + linked child so this works in the isolated
     * grail_test database (phpunit.xml) as well as against a seeded dev DB.
     */
    private function demoParent(): User
    {
        $user = User::where('email', 'parent@grail.school')->first();

        if (! $user) {
            $roleId = Role::where('name', 'parent')->value('id') ?? Role::create(['name' => 'parent'])->id;
            $user = User::create([
                'name'     => 'Demo Parent',
                'email'    => 'parent@grail.school',
                'password' => bcrypt('12345678'),
                'role_id'  => $roleId,
            ]);
        }

        $parent = ParentProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => 'Demo',
                'last_name'  => 'Parent',
                'email'      => 'parent@grail.school',
            ]
        );

        if (! $parent->students()->exists()) {
            $class = SchoolClass::create([
                'class_name'  => '10A',
                'grade_level' => 'Grade 10',
                'teacher_id'  => null,
            ]);

            Student::create([
                'first_name'     => 'Jamie',
                'last_name'      => 'Parent',
                'date_of_birth'  => '2010-05-10',
                'gender'         => 'Male',
                'student_number' => 'ST-RENDER-1',
                'class_id'       => $class->class_id,
                'enrolment_date' => '2026-01-01',
                'parent_user_id' => $user->id,
            ]);
        }

        return $user->fresh();
    }

    public function test_parent_dashboard_renders(): void
    {
        $user = $this->demoParent();
        $student = ParentProfile::where('user_id', $user->id)->first()->students()->first();

        $this->actingAs($user)
            ->get(route('parent.dashboard'))
            ->assertStatus(200)
            ->assertSee('Parent Portal')
            ->assertSee('My Children')
            ->assertSee($student->full_name)
            ->assertSee('w-sidebar-width')
            ->assertSee('Parent Account');
    }

    public function test_parent_cannot_access_admin_management_routes(): void
    {
        $user = $this->demoParent();

        $this->actingAs($user)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }
}