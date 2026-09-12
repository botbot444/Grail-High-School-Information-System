<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index(Request $request)
    {
        $academicYearId = $request->get('academic_year_id') ?? AcademicYear::current()?->year_id;
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('label', 'year_id');

        $terms = Term::when($academicYearId, function ($query) use ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        })->orderBy('start_date')->paginate(15);

        return view('admin.calendar.terms.index', compact('terms', 'academicYears', 'academicYearId'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('label', 'year_id');
        return view('admin.calendar.terms.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTerm($request);

        if ($this->overlaps($request)) {
            return back()->withErrors(['start_date' => 'Term overlaps with an existing term.'])->withInput();
        }

        Term::create($validated);

        return redirect()->route('admin.terms.index')
            ->with('notification', 'Term created successfully.');
    }

    public function edit(Term $term)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('label', 'year_id');
        return view('admin.calendar.terms.edit', compact('term', 'academicYears'));
    }

    public function update(Request $request, Term $term)
    {
        $validated = $this->validateTerm($request);

        if ($this->overlaps($request, $term->term_id)) {
            return back()->withErrors(['start_date' => 'Term overlaps with an existing term.'])->withInput();
        }

        $term->update($validated);

        return redirect()->route('admin.terms.index')
            ->with('notification', 'Term updated successfully.');
    }

    public function destroy(Term $term)
    {
        if ($term->grades()->exists() || $term->fees()->exists()) {
            return back()->withErrors('Cannot delete a term that has grades or fees.');
        }

        $term->delete();

        return redirect()->route('admin.terms.index')
            ->with('notification', 'Term deleted successfully.');
    }

    protected function validateTerm(Request $request): array
    {
        return $request->validate([
            'academic_year_id' => 'required|exists:academic_years,year_id',
            'name'             => 'required|string|max:255',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
        ]);
    }

    /**
     * Detect overlapping terms within the same academic year.
     * $ignoreId excludes the term currently being edited.
     */
    protected function overlaps(Request $request, int $ignoreId = null): bool
    {
        return Term::where('academic_year_id', $request->academic_year_id)
            ->when($ignoreId, fn ($q) => $q->where('term_id', '!=', $ignoreId))
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($qq) use ($request) {
                        $qq->where('start_date', '<=', $request->start_date)
                           ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();
    }
}