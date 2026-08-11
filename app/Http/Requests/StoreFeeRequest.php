<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'       => ['required', 'exists:students,student_id'],
            'description'      => ['nullable', 'string', 'max:500'],
            'term'             => ['nullable', 'string', 'max:50'],
            'academic_year'    => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'academic_year_id' => ['nullable', 'exists:academic_years,year_id'],
            'term_id'          => ['nullable', 'exists:terms,term_id'],
            'due_date'         => ['required', 'date'],

            // Fee items — at least one line is required.
            'fee_items'             => ['required', 'array', 'min:1'],
            'fee_items.*.item_name' => ['required', 'string', 'max:255'],
            'fee_items.*.category'  => ['required', 'string', 'max:100'],
            'fee_items.*.amount'    => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    /**
     * Reject a second fee for the same student / term / academic year so we never
     * hit the DB unique constraint (unique_fee_per_term) that would otherwise
     * surface as a 500. Works for both store() and update() (edit self-excluded).
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $studentId = (int) $this->input('student_id');
            $termId    = (int) $this->input('term_id');
            $yearId    = (int) $this->input('academic_year_id');

            if ($studentId <= 0 || $termId <= 0 || $yearId <= 0) {
                return; // The FK/exists rules already flag incomplete selections.
            }

            // The unique key is built from the resolved term/year names.
            $term = optional(Term::find($termId))->name;
            $year = optional(AcademicYear::find($yearId))->label;

            if ($term === null || $year === null) {
                return;
            }

            $query = Fee::where('student_id', $studentId)
                ->where('term', $term)
                ->where('academic_year', $year);

            if ($fee = $this->route('fee')) {
                $query->whereKeyNot($fee->getKey());
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    'term_id',
                    "A fee already exists for this student for {$term}, {$year}."
                );
            }
        });
    }
}
