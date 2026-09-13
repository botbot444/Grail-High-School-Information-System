<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\ParentProfile;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableRoleScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_and_parent_only_see_their_owned_class_timetable(): void
    {
        [$year, $term, $class, $otherClass, $period, $subject, $otherSubject] = $this->scheduleContext();
        $studentUser = User::create(['name' => 'Student', 'email' => 'student-scope@test.local', 'password' => 'password', 'role' => 'student']);
        $parentUser = User::create(['name' => 'Parent', 'email' => 'parent-scope@test.local', 'password' => 'password', 'role' => 'parent']);
        ParentProfile::create(['user_id' => $parentUser->id, 'first_name' => 'Parent', 'last_name' => 'One', 'email' => 'parent-profile@test.local']);
        $student = Student::create([
            'user_id' => $studentUser->id, 'parent_user_id' => $parentUser->id, 'first_name' => 'Student', 'last_name' => 'One',
            'date_of_birth' => '2010-01-01', 'gender' => 'Female', 'student_number' => 'SCOPE-1', 'class_id' => $class->class_id, 'enrolment_date' => '2026-01-01',
        ]);
        TimetableSlot::create(['school_class_id' => $class->class_id, 'subject_id' => $subject->subject_id, 'period_id' => $period->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);
        TimetableSlot::create(['school_class_id' => $otherClass->class_id, 'subject_id' => $otherSubject->subject_id, 'period_id' => Period::create(['grade_level_id' => $otherClass->grade_level_id, 'name' => 'P1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1])->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);

        $studentResponse = $this->actingAs($studentUser)->get(route('student.timetable', ['term_id' => $term->term_id]));
        $studentResponse->assertOk()->assertSee($subject->subject_name)->assertDontSee($otherSubject->subject_name);
        $parentResponse = $this->actingAs($parentUser)->get(route('parent.timetable', ['term_id' => $term->term_id, 'child_id' => $student->student_id]));
        $parentResponse->assertOk()->assertSee($subject->subject_name)->assertDontSee($otherSubject->subject_name);
    }

    public function test_referenced_subject_cannot_be_hard_deleted(): void
    {
        [$year, $term, $class, $otherClass, $period, $subject] = $this->scheduleContext();
        TimetableSlot::create(['school_class_id' => $class->class_id, 'subject_id' => $subject->subject_id, 'period_id' => $period->id, 'day_of_week' => 'Monday', 'term_id' => $term->term_id]);

        $this->expectException(QueryException::class);
        $subject->delete();
    }

    private function scheduleContext(): array
    {
        $year = AcademicYear::firstOrCreate(['label' => '2026'], ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]);
        $term = Term::firstOrCreate(['academic_year_id' => $year->year_id, 'name' => 'Term 1'], ['start_date' => '2026-01-01', 'end_date' => '2026-03-31']);
        $teacher = Teacher::create(['user_id' => User::create(['name' => 'Teacher', 'email' => 'scope-teacher@test.local', 'password' => 'password', 'role' => 'teacher'])->id, 'first_name' => 'T', 'last_name' => 'One', 'email' => 'scope-teacher-profile@test.local']);
        $grade = GradeLevel::create(['name' => 'Grade 8', 'order' => 8]);
        $otherGrade = GradeLevel::create(['name' => 'Grade 9', 'order' => 9]);
        $class = SchoolClass::create(['class_name' => '8A', 'grade_level' => 'Grade 8', 'grade_level_id' => $grade->grade_level_id, 'teacher_id' => $teacher->teacher_id]);
        $otherClass = SchoolClass::create(['class_name' => '9A', 'grade_level' => 'Grade 9', 'grade_level_id' => $otherGrade->grade_level_id, 'teacher_id' => $teacher->teacher_id]);
        $period = Period::create(['grade_level_id' => $grade->grade_level_id, 'name' => 'P1', 'start_time' => '08:00', 'end_time' => '09:00', 'order' => 1]);
        $subject = Subject::create(['subject_name' => 'Owned Mathematics']);
        $otherSubject = Subject::create(['subject_name' => 'Other History']);
        return [$year, $term, $class, $otherClass, $period, $subject, $otherSubject];
    }
}