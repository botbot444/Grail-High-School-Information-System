<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $classes = SchoolClass::all();
        $year    = now()->year;
        $count   = 0;

        // ── Demo student (known credentials) ─────────────────────────────────
        $studentRoleId = Role::where('name', 'student')->value('id');
        $parentRoleId = Role::where('name', 'parent')->value('id');

        $demoStudentUser = User::firstOrCreate(
            ['email' => 'student@grail.school'],
            [
                'name'              => 'Demo Student',
                'email'             => 'student@grail.school',
                'password'          => Hash::make('Student@1234'),
                'role_id'           => $studentRoleId,
                'email_verified_at' => now(),
            ]
        );

        $demoParentUser = User::firstOrCreate(
            ['email' => 'parent@grail.school'],
            [
                'name'              => 'Demo Parent',
                'email'             => 'parent@grail.school',
                'password'          => Hash::make('Parent@1234'),
                'role_id'           => $parentRoleId,
                'email_verified_at' => now(),
            ]
        );

        Student::firstOrCreate(
            ['student_number' => "{$year}/0001"],
            [
                'user_id'        => $demoStudentUser->id,
                'parent_user_id' => $demoParentUser->id,
                'first_name'     => 'Demo',
                'last_name'      => 'Student',
                'date_of_birth'  => '2008-03-15',
                'gender'         => 'Male',
                'student_number' => "{$year}/0001",
                'class_id'       => $classes->first()->class_id,
                'guardian_name'  => 'Demo Parent',
                'guardian_phone' => '+260 97 0000002',
                'enrolment_date' => now()->startOfYear()->format('d-m-Y'),
            ]
        );
        $count++;

        // ── 3 students per class ──────────────────────────────────────────────
        $seq = 2;
        foreach ($classes as $class) {
            for ($i = 0; $i < 3; $i++) {
                $seqStr = str_pad($seq++, 4, '0', STR_PAD_LEFT);
                $sNum   = "{$year}/{$seqStr}";

                $firstName = fake()->firstName();
                $lastName  = fake()->lastName();

                Student::firstOrCreate(
                    ['student_number' => $sNum],
                    [
                        'user_id'        => null,
                        'parent_user_id' => null,
                        'first_name'     => $firstName,
                        'last_name'      => $lastName,
                        'date_of_birth'  => fake()->dateTimeBetween('-18 years', '-13 years')->format('d-m-Y'),
                        'gender'         => fake()->randomElement(['Male', 'Female']),
                        'student_number' => $sNum,
                        'class_id'       => $class->class_id,
                        'guardian_name'  => fake()->name(),
                        'guardian_phone' => fake()->phoneNumber(),
                        'enrolment_date' => now()->startOfYear()->format('d-m-Y'),
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✔ {$count} students seeded.");
        $this->command->info('  Demo accounts: student@grail.school / Student@1234');
        $this->command->info('  Parent:        parent@grail.school  / Parent@1234');
    }
}
