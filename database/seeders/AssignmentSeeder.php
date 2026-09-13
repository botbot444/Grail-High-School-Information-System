<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Database\Seeder;

/**
 * Demo assignments so the student portal has something to show: a mix of
 * upcoming, overdue, submitted and already-marked work.
 */
class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $term = Term::current() ?? Term::orderByDesc('start_date')->first();

        $templates = [
            ['Chapter 4 Problem Set',      'Complete questions 1–20 from chapter 4. Show all working.',            -14, 50,  'graded'],
            ['Laboratory Report',          'Write up the experiment: aim, method, results, discussion.',           -5,  100, 'submitted'],
            ['Essay: Causes and Effects',  'Write 800–1000 words. Cite at least three sources.',                   3,   100, 'open'],
            ['Weekly Exercises',           'Hand in the exercises covered in class this week.',                    7,   20,  'open'],
            ['Revision Worksheet',         'Worksheet distributed in class — attach a scan or photo of your work.', -2,  30,  'overdue'],
        ];

        ClassSubject::with('schoolClass')->get()->each(function (ClassSubject $classSubject, int $index) use ($templates, $term) {
            // Two assignments per class-subject, rotating through the templates.
            foreach ([0, 1] as $offset) {
                [$title, $instructions, $dueOffsetDays, $maxScore, $mode] = $templates[($index + $offset) % count($templates)];

                $assignment = Assignment::create([
                    'class_subject_id'   => $classSubject->class_subject_id,
                    'term_id'            => $term?->term_id,
                    'title'              => $title,
                    'instructions'       => $instructions,
                    'status'             => Assignment::STATUS_PUBLISHED,
                    'published_at'       => now()->subDays(max(1, abs($dueOffsetDays) + 7)),
                    'due_at'             => now()->addDays($dueOffsetDays)->setTime(23, 59),
                    'max_score'          => $maxScore,
                    'allows_file_upload' => true,
                    'created_by'         => $classSubject->teacher_id,
                ]);

                if ($mode === 'open') {
                    continue;
                }

                $students = Student::where('class_id', $classSubject->class_id)->get();

                foreach ($students as $position => $student) {
                    // Leave roughly a third unsubmitted so "not handed in" is populated.
                    if ($mode === 'overdue' && $position % 3 === 0) {
                        continue;
                    }

                    $submission = AssignmentSubmission::create([
                        'assignment_id' => $assignment->assignment_id,
                        'student_id'    => $student->student_id,
                        'notes'         => 'Submitted through the student portal.',
                        'submitted_at'  => $assignment->due_at->copy()->subHours(rand(1, 48)),
                    ]);

                    if ($mode === 'graded') {
                        $submission->update([
                            'score'     => round($maxScore * (rand(45, 98) / 100), 1),
                            'feedback'  => 'Good effort — check your working on the last section.',
                            'graded_by' => $classSubject->teacher_id,
                            'graded_at' => $assignment->due_at->copy()->addDays(3),
                        ]);
                    }
                }
            }
        });
    }
}
