<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

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

    /**
     * Subjects every class studies, and therefore the ones that must have a
     * teacher standing in front of them before a report card can be finalized.
     */
    private const CORE_SUBJECTS = [
        'English Language',
        'Mathematics',
        'Integrated Science',
        'Geography',
        'History',
        'Civic Education',
        'Computer Studies',
    ];

    public function run(): void
    {
        foreach (self::SUBJECTS as $name) {
            Subject::firstOrCreate(['subject_name' => $name]);
        }

        $this->command->info('✔ ' . count(self::SUBJECTS) . ' subjects seeded.');
    }

    /** @return array<int, string> Every subject on offer. */
    public static function subjects(): array
    {
        return self::SUBJECTS;
    }

    /** @return array<int, string> The subjects taught in every class. */
    public static function coreSubjects(): array
    {
        return self::CORE_SUBJECTS;
    }
}
