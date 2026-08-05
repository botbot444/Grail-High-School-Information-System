<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin — change password immediately after first login
        $roleId = Role::where('name', 'admin')->value('id');

        User::firstOrCreate(
            ['email' => 'admin@grail.school'],
            [
                'name'              => 'System Administrator',
                'email'             => 'admin@grail.school',
                'password'          => Hash::make('Admin@1234'),
                'role_id'           => $roleId,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✔ Admin account seeded (admin@grail.school / Admin@1234)');
    }
}
