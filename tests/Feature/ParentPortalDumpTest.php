<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\ParentProfile;
use Tests\TestCase;

class ParentPortalDumpTest extends TestCase
{
    /**
     * Self-seeds a demo parent + linked child so the dump works in the isolated
     * grail_test database (phpunit.xml) as well as against a seeded dev DB.
     */
    private function demoParent(): ?User
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
                'student_number' => 'ST-DUMP-1',
                'class_id'       => $class->class_id,
                'enrolment_date' => '2026-01-01',
                'parent_user_id' => $user->id,
            ]);
        }

        return $user->fresh();
    }

    public function test_dump_parent_dashboard_html(): void
    {
        $user = $this->demoParent();
        if (! $user) {
            $this->markTestSkipped('Demo parent could not be created.');
        }

        $response = $this->actingAs($user)->get(route('parent.dashboard'));
        $response->assertStatus(200);

        file_put_contents(storage_path('app/parent_dashboard_dump.html'), $response->getContent());
        $this->assertTrue(true);
    }
}