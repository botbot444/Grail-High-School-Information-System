<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('class_subjects')
            ->select('teacher_id', 'subject_id')
            ->distinct()
            ->orderBy('teacher_id')
            ->chunk(500, function ($assignments): void {
                foreach ($assignments as $assignment) {
                    DB::table('teacher_subjects')->insertOrIgnore([
                        'teacher_id' => $assignment->teacher_id,
                        'subject_id' => $assignment->subject_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // The rows are shared with existing class_subjects assignments and must remain intact.
    }
};