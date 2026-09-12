<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::withCount('terms', 'holidays')->orderBy('start_date', 'desc')->paginate(15);
        return view('admin.calendar.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.calendar.academic-years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:255|unique:academic_years,label',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean',
        ]);

        $academicYear = AcademicYear::create($validated);

        if ($request->boolean('is_current')) {
            $academicYear->setAsCurrent();
        }

        return redirect()->route('admin.academic-years.index')
            ->with('notification', 'Academic year created successfully.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.calendar.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'label'      => ['required', 'string', 'max:255',
                Rule::unique('academic_years', 'label')->ignore($academicYear->year_id, 'year_id')],
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean',
        ]);

        $academicYear->update($validated);

        if ($request->boolean('is_current')) {
            $academicYear->setAsCurrent();
        } elseif (! $academicYear->is_current && ! $request->has('is_current')) {
            // keep as-is
        }

        return redirect()->route('admin.academic-years.index')
            ->with('notification', 'Academic year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->grades()->exists() || $academicYear->fees()->exists()) {
            return back()->withErrors('Cannot delete an academic year that has grades or fees.');
        }
        if ($academicYear->is_current) {
            return back()->withErrors('You cannot delete the current academic year.');
        }

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('notification', 'Academic year deleted successfully.');
    }
}