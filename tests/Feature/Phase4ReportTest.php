<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4ReportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
    }

    private function teacher(): User
    {
        return User::create([
            'name'     => 'Teacher User',
            'email'    => 'teacher@example.com',
            'password' => bcrypt('password'),
            'role'     => 'teacher',
        ]);
    }

    private function studentWithClass(): array
    {
        $class = SchoolClass::create([
            'class_name'  => '10A',
            'grade_level' => 'Grade 10',
            'teacher_id'  => null,
        ]);

        $student = Student::create([
            'first_name'     => 'Jane',
            'last_name'      => 'Smith',
            'date_of_birth'  => '2010-01-01',
            'gender'         => 'Female',
            'student_number' => 'ST-1001',
            'class_id'       => $class->class_id,
            'enrolment_date' => '2026-01-01',
        ]);

        return [$class, $student];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.fee-collection'))->assertRedirect(route('login'));
    }

    public function test_non_admin_is_forbidden_from_reports(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('admin.reports.fee-collection'))
            ->assertStatus(403);
    }

    public function test_admin_can_view_fee_collection_report(): void
    {
        [$class, $student] = $this->studentWithClass();

        Fee::factory()->create(['student_id' => $student->student_id, 'term' => 'Term 1']);
        Fee::factory()->overdue()->create(['student_id' => $student->student_id, 'term' => 'Term 2']);

        $this->actingAs($this->admin())
            ->get(route('admin.reports.fee-collection'))
            ->assertStatus(200)
            ->assertSee('Fee Collection Report')
            ->assertSee('ZMW')
            ->assertSee('10A');
    }

    public function test_admin_can_export_fee_collection_report_csv(): void
    {
        [$class, $student] = $this->studentWithClass();
        Fee::factory()->create(['student_id' => $student->student_id]);

        $this->actingAs($this->admin())
            ->get(route('admin.reports.fee-collection.export'))
            ->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertHeader('content-disposition', 'attachment; filename="fee_collection_report.csv"');
    }

    public function test_admin_can_view_student_financial_summary(): void
    {
        [$class, $student] = $this->studentWithClass();
        Fee::factory()->create(['student_id' => $student->student_id]);

        $this->actingAs($this->admin())
            ->get(route('admin.students.financials', $student->student_id))
            ->assertStatus(200)
            ->assertSee('Jane Smith')
            ->assertSee('ZMW');
    }

    public function test_admin_can_view_student_statement(): void
    {
        [$class, $student] = $this->studentWithClass();
        Fee::factory()->create(['student_id' => $student->student_id]);

        $this->actingAs($this->admin())
            ->get(route('admin.students.statement', $student->student_id))
            ->assertStatus(200)
            ->assertSee('Statement of Account')
            ->assertSee('Jane Smith');
    }
}