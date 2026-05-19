<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Core subjects offered at Zambian secondary schools.
     * Matches the ECZ (Examinations Council of Zambia) curriculum.
     */
    private const SUBJECTS = [
        'English Language',
        'Mathematics',
        'Integrated Science',
        'Biology',
        'Chemistry',
        'Physics',
        'Geography',
        'History',
        'Civic Education',
        'Religious Education',
        'Computer Studies',
        'Business Studies',
        'Home Economics',
        'Physical Education',
        'French',
        'Zambian Languages',
    ];

    public function run(): void
    {
        foreach (self::SUBJECTS as $name) {
            Subject::firstOrCreate(['subject_name' => $name]);
        }

        $this->command->info('✔ ' . count(self::SUBJECTS) . ' subjects seeded.');
    }
}
