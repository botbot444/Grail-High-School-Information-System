<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $academicYearId = $request->get('academic_year_id') ?? AcademicYear::current()?->year_id;

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('label', 'year_id');

        $holidays = Holiday::when($academicYearId, function ($query) use ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        })->orderBy('date')->paginate(15);

        return view('admin.calendar.holidays.index', compact('holidays', 'academicYears', 'academicYearId'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('label', 'year_id');
        return view('admin.calendar.holidays.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,year_id',
            'date'         => 'required|date',
            'description'  => 'required|string|max:255',
        ]);

        Holiday::create($validated);

        return redirect()->route('admin.holidays.index')
            ->with('notification', 'Holiday added successfully.');
    }

    public function edit(Holiday $holiday)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('label', 'year_id');
        return view('admin.calendar.holidays.edit', compact('holiday', 'academicYears'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,year_id',
            'date'         => 'required|date',
            'description'  => 'required|string|max:255',
        ]);

        $holiday->update($validated);

        return redirect()->route('admin.holidays.index')
            ->with('notification', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('notification', 'Holiday deleted successfully.');
    }
}