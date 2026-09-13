<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_scoped_class_performance(): void
    {
        [$user, $teacher, $classSubject, $student, $term] = $this->context();
        Grade::create([
            'student_id' => $student->student_id,
            'class_subject_id' => $classSubject->class_subject_id,
            'assessment_type' => 'EXAM',
            'score' => 78,
            'max_score' => 100,
            'term' => $term->name,
            'academic_year' => 2026,
            'term_id' => $term->term_id,
            'academic_year_id' => $term->academic_year_id,
            'recorded_by' => $teacher->teacher_id,
        ]);

        $response = $this->actingAs($user)->get(route('teacher.performance', [
            'assignment_id' => $classSubject->class_subject_id,
            'term_id' => $term->term_id,
        ]));

        $response->assertOk()
            ->assertSee($student->full_name)
            ->assertSee('78.0%')
            ->assertSee('Class Performance');
    }

    public function test_finalization_locks_mark_entry(): void
    {
        [$user, $teacher, $classSubject, $student, $term] = $this->context();
        Grade::create([
            'student_id' => $student->student_id,
            'class_subject_id' => $classSubject->class_subject_id,
            'assessment_type' => 'EXAM',
            'score' => 78,
            'max_score' => 100,
            'term' => $term->name,
            'academic_year' => 2026,
            'term_id' => $term->term_id,
            'academic_year_id' => $term->academic_year_id,
            'recorded_by' => $teacher->teacher_id,
        ]);

        $finalize = $this->actingAs($user)->post(route('teacher.performance.finalize'), [
            'assignment_id' => $classSubject->class_subject_id,
            'term_id' => $term->term_id,
        ]);
        $finalize->assertSessionHasNoErrors();

        $locked = $this->actingAs($user)->post(route('teacher.marks.store'), [
            'assignment_id' => $classSubject->class_subject_id,
            'term_id' => $term->term_id,
            'marks' => [$student->student_id => 90],
        ]);

        $locked->assertSessionHasErrors();
        $this->assertDatabaseHas('grades', ['student_id' => $student->student_id, 'score' => 78]);
    }

    private function context(): array
    {
        $year = AcademicYear::firstOrCreate(['label' => '2026'], ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]);
        $term = Term::firstOrCreate(['academic_year_id' => $year->year_id, 'name' => 'Term 1'], ['start_date' => '2026-01-01', 'end_date' => '2026-03-31']);
        $user = User::create(['name' => 'Performance Teacher', 'email' => 'performance-teacher@test.local', 'password' => 'password', 'role' => 'teacher']);
        $teacher = Teacher::create(['user_id' => $user->id, 'first_name' => 'Performance', 'last_name' => 'Teacher', 'email' => 'performance-profile@test.local']);
        $class = SchoolClass::create(['class_name' => '10A', 'grade_level' => 'Grade 10', 'teacher_id' => $teacher->teacher_id]);
        $subject = Subject::create(['subject_name' => 'Mathematics']);
        $classSubject = ClassSubject::create(['class_id' => $class->class_id, 'subject_id' => $subject->subject_id, 'teacher_id' => $teacher->teacher_id]);
        $studentUser = User::create(['name' => 'Performance Student', 'email' => 'performance-student@test.local', 'password' => 'password', 'role' => 'student']);
        $student = Student::create(['user_id' => $studentUser->id, 'first_name' => 'Performance', 'last_name' => 'Student', 'date_of_birth' => '2010-01-01', 'gender' => 'Female', 'student_number' => 'PERF-1', 'class_id' => $class->class_id, 'enrolment_date' => '2026-01-01']);

        return [$user, $teacher, $classSubject, $student, $term];
    }
}