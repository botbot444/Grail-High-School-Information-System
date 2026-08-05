<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'System administrator'],
            ['name' => 'teacher', 'description' => 'Teacher or staff member'],
            ['name' => 'parent', 'description' => 'Parent or guardian'],
            ['name' => 'student', 'description' => 'Student account'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], ['description' => $role['description']]);
        }
    }
}
