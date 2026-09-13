<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\PromotionMapping;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Phase 6 — year-end promotion.
 *
 * Classes are permanent rows rather than per-year instances (amended from the
 * plan's wording, 2026-09-13), so promotion moves a student's class_id and the
 * year context is carried by the student_promotions record instead.
 *
 * Every run is recorded per student with the class they came from, which is
 * what makes rollback possible.
 */
class PromotionService
{
    /**
     * Reasons a promotion run cannot proceed. Empty means it can.
     *
     * @return array<int, string>
     */
    public function blockers(?AcademicYear $targetYear): array
    {
        $problems = [];

        if (! $targetYear) {
            $problems[] = 'Create the academic year you are promoting into before running a promotion.';
        }

        if (SchoolClass::count() === 0) {
            $problems[] = 'No classes exist to promote between.';
        }

        return $problems;
    }

    /**
     * The default outcome for one class, from the saved mappings.
     * Falls back to the next grade level up when no mapping is configured.
     */
    public function defaultFor(SchoolClass $class, Collection $mappings, Collection $classes): array
    {
        $mapping = $mappings->firstWhere('from_class_id', $class->class_id);

        if ($mapping) {
            return $mapping->graduates
                ? ['outcome' => StudentPromotion::GRADUATED, 'to_class_id' => null]
                : ['outcome' => StudentPromotion::PROMOTED, 'to_class_id' => $mapping->to_class_id];
        }

        $currentOrder = $class->gradeLevel?->order;

        if ($currentOrder === null) {
            return ['outcome' => StudentPromotion::RETAINED, 'to_class_id' => null];
        }

        // The next class up whose grade level is exactly one step higher.
        $next = $classes
            ->filter(fn (SchoolClass $c) => $c->gradeLevel?->order !== null)
            ->filter(fn (SchoolClass $c) => $c->gradeLevel->order > $currentOrder)
            ->sortBy(fn (SchoolClass $c) => $c->gradeLevel->order)
            ->first();

        // Nothing above it: this is the final grade level, so they leave.
        return $next
            ? ['outcome' => StudentPromotion::PROMOTED, 'to_class_id' => $next->class_id]
            : ['outcome' => StudentPromotion::GRADUATED, 'to_class_id' => null];
    }

    /**
     * Run a promotion.
     *
     * @param  array<int, array{outcome: string, to_class_id: ?int}>  $decisions  keyed by student_id
     * @return array{batch_ref: string, promoted: int, retained: int, graduated: int}
     *
     * @throws ValidationException
     */
    public function run(array $decisions, AcademicYear $targetYear, User $admin): array
    {
        if ($decisions === []) {
            throw ValidationException::withMessages([
                'promotion' => 'Nothing was selected to promote.',
            ]);
        }

        $students = Student::with('schoolClass')
            ->whereIn('student_id', array_keys($decisions))
            ->get()
            ->keyBy('student_id');

        $this->assertDecisionsAreSound($decisions, $students);

        $batchRef = (string) Str::ulid();
        $tally = ['promoted' => 0, 'retained' => 0, 'graduated' => 0];

        DB::transaction(function () use ($decisions, $students, $targetYear, $admin, $batchRef, &$tally) {
            foreach ($decisions as $studentId => $decision) {
                $student = $students->get((int) $studentId);

                if (! $student) {
                    continue;
                }

                $outcome = $decision['outcome'];
                $fromClassId = $student->class_id;
                $previousStatus = $student->status ?? Student::STATUS_ENROLLED;

                $toClassId = match ($outcome) {
                    StudentPromotion::PROMOTED  => (int) $decision['to_class_id'],
                    StudentPromotion::RETAINED  => $fromClassId,
                    StudentPromotion::GRADUATED => null,
                };

                StudentPromotion::create([
                    'batch_ref'        => $batchRef,
                    'student_id'       => $student->student_id,
                    'from_class_id'    => $fromClassId,
                    'to_class_id'      => $toClassId,
                    'outcome'          => $outcome,
                    'previous_status'  => $previousStatus,
                    'academic_year_id' => $targetYear->year_id,
                    'promoted_by'      => $admin->id,
                ]);

                $student->audit_reason = "Year-end promotion into {$targetYear->label} (batch {$batchRef})";

                if ($outcome === StudentPromotion::GRADUATED) {
                    // Keep the record, drop the enrolment. Grades, fees and report
                    // cards stay readable; the student simply is not in a class.
                    $student->status = Student::STATUS_GRADUATED;
                    $student->graduated_on = now()->toDateString();
                    $student->class_id = null;
                    $student->save();

                    $this->deactivateLogin($student);
                } else {
                    $student->class_id = $toClassId;
                    $student->save();
                }

                $tally[$outcome]++;
            }
        });

        return ['batch_ref' => $batchRef] + $tally;
    }

    /**
     * Put a batch back the way it was.
     *
     * @throws ValidationException
     */
    public function rollback(string $batchRef, User $admin): int
    {
        $rows = StudentPromotion::active()
            ->where('batch_ref', $batchRef)
            ->with('student')
            ->get();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'rollback' => 'That promotion batch has already been rolled back, or does not exist.',
            ]);
        }

        DB::transaction(function () use ($rows, $batchRef, $admin) {
            foreach ($rows as $row) {
                $student = $row->student;

                if ($student) {
                    $student->audit_reason = "Rolled back promotion batch {$batchRef}";
                    $student->class_id = $row->from_class_id;
                    $student->status = $row->previous_status;
                    $student->graduated_on = $row->outcome === StudentPromotion::GRADUATED ? null : $student->graduated_on;
                    $student->save();

                    if ($row->outcome === StudentPromotion::GRADUATED) {
                        $this->reactivateLogin($student);
                    }
                }

                $row->rolled_back_at = now();
                $row->save();
            }
        });

        return $rows->count();
    }

    /** Batches, newest first, for the history screen. */
    public function batches(int $limit = 20): Collection
    {
        return StudentPromotion::with(['academicYear', 'promotedBy'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('batch_ref')
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return [
                    'batch_ref'   => $first->batch_ref,
                    'ran_at'      => $first->created_at,
                    'year'        => $first->academicYear,
                    'by'          => $first->promotedBy,
                    'promoted'    => $rows->where('outcome', StudentPromotion::PROMOTED)->count(),
                    'retained'    => $rows->where('outcome', StudentPromotion::RETAINED)->count(),
                    'graduated'   => $rows->where('outcome', StudentPromotion::GRADUATED)->count(),
                    'total'       => $rows->count(),
                    'rolled_back' => $rows->every(fn ($r) => $r->rolled_back_at !== null),
                ];
            })
            ->sortByDesc('ran_at')
            ->take($limit)
            ->values();
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * @param  array<int, array{outcome: string, to_class_id: ?int}>  $decisions
     *
     * @throws ValidationException
     */
    private function assertDecisionsAreSound(array $decisions, Collection $students): void
    {
        $valid = [StudentPromotion::PROMOTED, StudentPromotion::RETAINED, StudentPromotion::GRADUATED];
        $classIds = SchoolClass::pluck('class_id');

        foreach ($decisions as $studentId => $decision) {
            $outcome = $decision['outcome'] ?? null;

            if (! in_array($outcome, $valid, true)) {
                throw ValidationException::withMessages([
                    'promotion' => "Unknown outcome \"{$outcome}\" for student {$studentId}.",
                ]);
            }

            if ($outcome !== StudentPromotion::PROMOTED) {
                continue;
            }

            $target = $decision['to_class_id'] ?? null;

            // The guard the plan asks for: never promote into a class that is not there.
            if (! $target || ! $classIds->contains((int) $target)) {
                $name = $students->get((int) $studentId)?->full_name ?? "student {$studentId}";

                throw ValidationException::withMessages([
                    'promotion' => "Choose a destination class for {$name} — promoting into a class that does not exist is not allowed.",
                ]);
            }
        }
    }

    /** A graduate keeps their record but loses portal access. */
    private function deactivateLogin(Student $student): void
    {
        $student->loadMissing('user');

        if ($student->user) {
            $student->user->is_active = false;
            $student->user->save();
        }
    }

    private function reactivateLogin(Student $student): void
    {
        $student->loadMissing('user');

        if ($student->user) {
            $student->user->is_active = true;
            $student->user->save();
        }
    }
}
