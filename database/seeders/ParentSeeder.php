<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\ParentProfile;
use App\Models\Student;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        // ── Demo parent (known credentials, linked to demo student) ───────────
        $roleId = Role::where('name', 'parent')->value('id');

        $demoUser = User::firstOrCreate(
            ['email' => 'parent@grail.school'],
            [
                'name'              => 'Demo Parent',
                'email'             => 'parent@grail.school',
                'password'          => Hash::make('Parent@1234'),
                'role_id'           => $roleId,
                'email_verified_at' => now(),
            ]
        );

        ParentProfile::firstOrCreate(
            ['email' => 'parent@grail.school'],
            [
                'user_id'     => $demoUser->id,
                'first_name'  => 'Demo',
                'last_name'   => 'Parent',
                'email'       => 'parent@grail.school',
                'phone'       => '+260 97 0000002',
                'address'     => '123 Demo Street, Lusaka',
                'occupation'  => 'Civil Servant',
                'national_id' => '123456/78/9/0000',
            ]
        );
        $count++;

        // ── Create a parent for every student that doesn't have one yet ───────
        $studentsWithoutParent = Student::whereNull('parent_user_id')->get();

        foreach ($studentsWithoutParent as $student) {
            $firstName = fake()->firstName();
            $lastName  = $student->last_name; // same surname as the student
            $email     = strtolower("{$firstName}.{$lastName}" . fake()->numerify('##') . '@example.com');

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => "{$firstName} {$lastName}",
                    'email'             => $email,
                    'password'          => Hash::make('Parent@1234'),
                    'role_id'           => $roleId,
                    'email_verified_at' => now(),
                ]
            );

            ParentProfile::firstOrCreate(
                ['email' => $email],
                [
                    'user_id'     => $user->id,
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'email'       => $email,
                    'phone'       => fake()->phoneNumber(),
                    'address'     => fake()->address(),
                    'occupation'  => fake()->jobTitle(),
                    'national_id' => strtoupper(fake()->bothify('######/##/##/####')),
                ]
            );

            // Link the student back to this parent
            $student->update(['parent_user_id' => $user->id]);

            $count++;
        }

        $this->command->info("✔ {$count} parent profiles seeded.");
        $this->command->info('  Demo: parent@grail.school / Parent@1234');
    }
}
