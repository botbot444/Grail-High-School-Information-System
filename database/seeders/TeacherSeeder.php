<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * One teacher per subject, named after the subject they teach.
     *
     * These used to be faker names, which meant every re-seed produced a
     * different staff room: any note, screenshot or test that named a teacher
     * was wrong the moment someone ran the seeders again. Naming a teacher
     * after their subject makes the whole demo readable — "who teaches
     * Mathematics in 9B" answers itself — and, more importantly, makes it
     * reproducible.
     *
     * Login for all of them is the subject in lower case, dots for spaces:
     *   Mathematics        -> mathematics@grail.school
     *   English Language   -> english.language@grail.school
     *   Integrated Science -> integrated.science@grail.school
     *
     * Password for every seeded account is 12345678.
     */
    public function run(): void
    {
        $roleId = Role::where('name', 'teacher')->value('id');

        if (! $roleId) {
            $this->command->warn('✖ Teacher role missing — run RoleSeeder first.');

            return;
        }

        $subjects = SubjectSeeder::subjects();

        foreach ($subjects as $index => $subjectName) {
            $email = self::emailFor($subjectName);
            $phone = '+260 97 ' . str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => "{$subjectName} Teacher",
                    'password'          => Hash::make('12345678'),
                    'role'              => 'teacher',
                    'role_id'           => $roleId,
                    'email_verified_at' => now(),
                    'is_active'         => true,
                    // Seeded demo accounts are meant to be logged straight into,
                    // so they skip the forced password change a real new account
                    // would get from the admin portal.
                    'must_change_password' => false,
                ]
            );

            Teacher::updateOrCreate(
                ['email' => $email],
                [
                    'user_id'    => $user->id,
                    'first_name' => $subjectName,
                    'last_name'  => 'Teacher',
                    'phone'      => $phone,
                ]
            );
        }

        $this->command->info('✔ ' . count($subjects) . ' teachers seeded, one per subject.');
    }

    /**
     * The login for a subject's teacher. Shared with the class and
     * class-subject seeders so the three cannot drift apart.
     */
    public static function emailFor(string $subjectName): string
    {
        return str_replace(' ', '.', strtolower($subjectName)) . '@grail.school';
    }

    /** The Teacher record that owns a subject, or null if not seeded yet. */
    public static function forSubject(string $subjectName): ?Teacher
    {
        return Teacher::where('email', self::emailFor($subjectName))->first();
    }
}
