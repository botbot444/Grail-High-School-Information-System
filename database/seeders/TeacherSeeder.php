<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Creates 10 teacher accounts with linked User records.
     * One demo teacher has predictable credentials for testing.
     */
    public function run(): void
    {
        // ── Demo teacher (known credentials for testing) ──────────────────────
        $demoUser = User::firstOrCreate(
            ['email' => 'teacher@grail.school'],
            [
                'name'              => 'Demo Teacher',
                'email'             => 'teacher@grail.school',
                'password'          => Hash::make('Teacher@1234'),
                'role'              => 'teacher',
                'email_verified_at' => now(),
            ]
        );

        Teacher::firstOrCreate(
            ['email' => 'teacher@grail.school'],
            [
                'user_id'    => $demoUser->id,
                'first_name' => 'Demo',
                'last_name'  => 'Teacher',
                'email'      => 'teacher@grail.school',
                'phone'      => '+260 97 0000001',
            ]
        );

        // ── Additional random teachers ────────────────────────────────────────
        for ($i = 0; $i < 9; $i++) {
            $firstName = fake()->firstName();
            $lastName  = fake()->lastName();
            $email     = strtolower("{$firstName}.{$lastName}@grail.school");

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => "{$firstName} {$lastName}",
                    'email'             => $email,
                    'password'          => Hash::make('Teacher@1234'),
                    'role'              => 'teacher',
                    'email_verified_at' => now(),
                ]
            );

            Teacher::firstOrCreate(
                ['email' => $email],
                [
                    'user_id'    => $user->id,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'phone'      => fake()->phoneNumber(),
                ]
            );
        }

        $this->command->info('✔ 10 teachers seeded.');
    }
}
