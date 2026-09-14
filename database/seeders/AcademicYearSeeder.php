<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y');

        $academicYear = AcademicYear::firstOrCreate(
            ['label' => (string) $year],
            [
                'start_date' => $year.'-01-01',
                'end_date'   => $year.'-12-31',
                'is_current' => true,
            ]
        );

        // If another year is currently flagged, step down so only one is current.
        if ($academicYear->exists) {
            AcademicYear::where('year_id', '!=', $academicYear->year_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
            $academicYear->forceFill(['is_current' => true])->save();
        }

        $terms = [
            ['name' => 'Term 1', 'start' => $year.'-01-15', 'end' => $year.'-03-28'],
            ['name' => 'Term 2', 'start' => $year.'-05-05', 'end' => $year.'-08-15'],
            ['name' => 'Term 3', 'start' => $year.'-09-01', 'end' => $year.'-12-15'],
        ];

        foreach ($terms as $term) {
            Term::firstOrCreate(
                [
                    'academic_year_id' => $academicYear->year_id,
                    'name'             => $term['name'],
                ],
                [
                    'start_date' => $term['start'],
                    'end_date'   => $term['end'],
                ]
            );
        }

        // Phase 6 needs a year to promote into. Seed the next one (not current)
        // so the promotion screen has a valid target out of the box.
        $nextYear = (string) ($year + 1);

        AcademicYear::firstOrCreate(
            ['label' => $nextYear],
            [
                'start_date' => $nextYear.'-01-01',
                'end_date'   => $nextYear.'-12-31',
                'is_current' => false,
            ]
        );

        $this->command->info('✔ Academic year '.$year.' seeded with 3 terms, plus '.$nextYear.' for promotion.');
    
        // Keep is_current on the term that contains today, so the stored label
        // agrees with what Term::current() computes from the dates.
        \App\Models\Term::query()->update(['is_current' => false]);
        \App\Models\Term::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->limit(1)
            ->update(['is_current' => true]);
}
}