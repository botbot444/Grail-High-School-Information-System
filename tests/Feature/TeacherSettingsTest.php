<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_settings_without_account_deletion_controls(): void
    {
        $user = User::create(['name' => 'Settings Teacher', 'email' => 'settings-teacher@test.local', 'password' => 'password', 'role' => 'teacher']);
        Teacher::create(['user_id' => $user->id, 'first_name' => 'Settings', 'last_name' => 'Teacher', 'email' => 'settings-profile@test.local']);

        $response = $this->actingAs($user)->get(route('teacher.settings'));

        $response->assertOk()
            ->assertSee('Profile Information')
            ->assertSee('Update Password')
            ->assertDontSee('Delete Account');
    }
}