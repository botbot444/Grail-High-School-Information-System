<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $gradeLevels = [
            ['name' => 'Grade 8',  'order' => 8],
            ['name' => 'Grade 9',  'order' => 9],
            ['name' => 'Grade 10', 'order' => 10],
            ['name' => 'Grade 11', 'order' => 11],
            ['name' => 'Grade 12', 'order' => 12],
        ];

        foreach ($gradeLevels as $grade) {
            $gradeLevel = GradeLevel::firstOrCreate(
                ['name' => $grade['name']],
                ['order' => $grade['order']]
            );

            SchoolClass::where('grade_level', $grade['name'])
                ->whereNull('grade_level_id')
                ->update(['grade_level_id' => $gradeLevel->grade_level_id]);
        }

        $this->command->info('✔ Grade levels seeded (Grades 8-12).');
    }
}