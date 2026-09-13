<?php

namespace Tests\Unit;

use App\Models\GradeLevel;
use App\Models\AcademicYear;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TimetablePeriodValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_periods_are_rejected_only_within_the_same_grade_level(): void
    {
        $gradeOne = GradeLevel::create(['name' => 'Grade 8', 'order' => 8]);
        $gradeTwo = GradeLevel::create(['name' => 'Grade 9', 'order' => 9]);

        Period::create([
            'grade_level_id' => $gradeOne->grade_level_id,
            'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1,
        ]);
        Period::create([
            'grade_level_id' => $gradeTwo->grade_level_id,
            'name' => 'Period 1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1,
        ]);

        $this->expectException(ValidationException::class);
        Period::create([
            'grade_level_id' => $gradeOne->grade_level_id,
            'name' => 'Overlap', 'start_time' => '08:30', 'end_time' => '09:30', 'order' => 2,
        ]);
    }

    public function test_break_period_cannot_have_a_subject_or_teacher(): void
    {
        [$grade, $class, $term, $teacher] = $this->context();
        $period = Period::create([
            'grade_level_id' => $grade->grade_level_id,
            'name' => 'Break', 'start_time' => '10:00', 'end_time' => '10:15', 'order' => 2, 'is_break' => true,
        ]);
        $subject = Subject::create(['subject_name' => 'Mathematics']);

        $this->expectException(ValidationException::class);
        TimetableSlot::create([
            'school_class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->teacher_id,
            'period_id' => $period->id,
            'day_of_week' => 'Monday',
            'term_id' => $term->term_id,
        ]);
    }

    public function test_wrong_grade_period_is_rejected_and_null_teacher_is_allowed(): void
    {
        [$gradeOne, $classOne, $term, $teacher] = $this->context();
        $gradeTwo = GradeLevel::create(['name' => 'Grade 10', 'order' => 10]);
        $classTwo = SchoolClass::create(['class_name' => '10A', 'grade_level' => 'Grade 10', 'grade_level_id' => $gradeTwo->grade_level_id]);
        $periodOne = Period::create(['grade_level_id' => $gradeOne->grade_level_id, 'name' => 'P1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1]);
        $periodTwo = Period::create(['grade_level_id' => $gradeTwo->grade_level_id, 'name' => 'P1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1]);
        $subject = Subject::create(['subject_name' => 'English Language']);

        TimetableSlot::create(['school_class_id' => $classOne->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => null, 'period_id' => $periodOne->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);

        $this->expectException(ValidationException::class);
        TimetableSlot::create(['school_class_id' => $classTwo->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => $teacher->teacher_id, 'period_id' => $periodOne->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);
    }

    public function test_same_slot_teacher_conflict_is_rejected(): void
    {
        [$grade, $class, $term, $teacher] = $this->context();
        $otherClass = SchoolClass::create(['class_name' => '8B', 'grade_level' => 'Grade 8', 'grade_level_id' => $grade->grade_level_id]);
        $period = Period::create(['grade_level_id' => $grade->grade_level_id, 'name' => 'P1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1]);
        $subject = Subject::create(['subject_name' => 'Science']);
        TimetableSlot::create(['school_class_id' => $class->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => $teacher->teacher_id, 'period_id' => $period->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);

        $this->expectException(ValidationException::class);
        TimetableSlot::create(['school_class_id' => $otherClass->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => $teacher->teacher_id, 'period_id' => $period->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);
    }

    private function context(): array
    {
        $user = User::create(['name' => 'Teacher', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'role' => 'teacher']);
        $teacher = Teacher::create(['user_id' => $user->id, 'first_name' => 'T', 'last_name' => 'One', 'email' => fake()->unique()->safeEmail()]);
        $grade = GradeLevel::create(['name' => 'Grade 8', 'order' => 8]);
        $class = SchoolClass::create(['class_name' => '8A', 'grade_level' => 'Grade 8', 'grade_level_id' => $grade->grade_level_id, 'teacher_id' => $teacher->teacher_id]);
        $academicYear = AcademicYear::firstOrCreate(
            ['label' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );
        $term = Term::create(['name' => 'Term 1', 'start_date' => '2026-01-01', 'end_date' => '2026-03-31', 'academic_year_id' => $academicYear->year_id]);

        return [$grade, $class, $term, $teacher];
    }
}