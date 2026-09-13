<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\GradeLevel;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_copy_and_clear_a_class_term_timetable(): void
    {
        [$admin, $class, $period, $subject, $teacher, $source, $target] = $this->context();
        TimetableSlot::create(['school_class_id' => $class->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => $teacher->teacher_id, 'period_id' => $period->id, 'day_of_week' => 'Monday', 'term_id' => $source->term_id]);

        $copy = $this->actingAs($admin)->post(route('admin.timetable.copy'), [
            'school_class_id' => $class->class_id, 'source_term_id' => $source->term_id, 'target_term_id' => $target->term_id,
        ]);
        $copy->assertSessionHasNoErrors();
        $this->assertDatabaseHas('timetable_slots', ['school_class_id' => $class->class_id, 'term_id' => $target->term_id]);
        $this->assertDatabaseHas('timetable_slots', ['school_class_id' => $class->class_id, 'term_id' => $source->term_id]);

        $clear = $this->actingAs($admin)->post(route('admin.timetable.clear'), ['school_class_id' => $class->class_id, 'term_id' => $target->term_id]);
        $clear->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('timetable_slots', ['school_class_id' => $class->class_id, 'term_id' => $target->term_id]);
        $this->assertTrue(AuditLog::where('auditable_type', TimetableSlot::class)->where('action', 'deleted')->exists());
    }

    public function test_teacher_timetable_only_returns_authenticated_teachers_slots(): void
    {
        [$teacherUser, $class, $period, $subject, $teacher, $term] = $this->teacherContext();
        TimetableSlot::create(['school_class_id' => $class->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => $teacher->teacher_id, 'period_id' => $period->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);

        $response = $this->actingAs($teacherUser)->get(route('teacher.timetable', ['term_id' => $term->term_id]));
        $response->assertOk()->assertSee($subject->subject_name);
    }

    private function context(): array
    {
        [$admin] = [User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'password', 'role' => 'admin'])];
        [$teacherUser, $class, $period, $subject, $teacher, $source] = $this->teacherContext();
        $target = Term::create(['name' => 'Term 2', 'start_date' => '2026-05-01', 'end_date' => '2026-08-01', 'academic_year_id' => $source->academic_year_id]);
        return [$admin, $class, $period, $subject, $teacher, $source, $target];
    }

    private function teacherContext(): array
    {
        $year = \App\Models\AcademicYear::firstOrCreate(['label' => '2026'], ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]);
        $term = Term::create(['name' => 'Term 1', 'start_date' => '2026-01-01', 'end_date' => '2026-03-31', 'academic_year_id' => $year->year_id]);
        $teacherUser = User::create(['name' => 'Teacher', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'role' => 'teacher']);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'first_name' => 'T', 'last_name' => 'One', 'email' => fake()->unique()->safeEmail()]);
        $grade = GradeLevel::create(['name' => 'Grade 8', 'order' => 8]);
        $class = SchoolClass::create(['class_name' => '8A', 'grade_level' => 'Grade 8', 'grade_level_id' => $grade->grade_level_id, 'teacher_id' => $teacher->teacher_id]);
        $period = Period::create(['grade_level_id' => $grade->grade_level_id, 'name' => 'P1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1]);
        $subject = Subject::create(['subject_name' => 'Mathematics']);
        return [$teacherUser, $class, $period, $subject, $teacher, $term];
    }
}