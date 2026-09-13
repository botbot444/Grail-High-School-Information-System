<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        $gradeLevels = GradeLevel::orderBy('order')->get();
        $gradeLevel = $request->filled('grade_level')
            ? $gradeLevels->firstWhere('grade_level_id', (int) $request->grade_level)
            : $gradeLevels->first();

        $periods = $gradeLevel
            ? $gradeLevel->periods()->orderBy('order')->get()
            : collect();

        return view('admin.calendar.periods.index', compact('gradeLevels', 'gradeLevel', 'periods'));
    }

    public function create(Request $request)
    {
        $gradeLevels = GradeLevel::orderBy('order')->get();
        $selectedGradeLevel = (int) $request->input('grade_level') ?: $gradeLevels->first()?->grade_level_id;

        return view('admin.calendar.periods.create', compact('gradeLevels', 'selectedGradeLevel'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        Period::create($validated);

        return redirect()->route('admin.periods.index', ['grade_level' => $validated['grade_level_id']])
            ->with('notification', 'Period created successfully.');
    }

    public function edit(Period $period)
    {
        $gradeLevels = GradeLevel::orderBy('order')->get();

        return view('admin.calendar.periods.edit', compact('period', 'gradeLevels'));
    }

    public function update(Request $request, Period $period)
    {
        $period->update($this->validated($request));

        return redirect()->route('admin.periods.index', ['grade_level' => $period->grade_level_id])
            ->with('notification', 'Period updated successfully.');
    }

    public function destroy(Period $period)
    {
        if ($period->timetableSlots()->exists()) {
            return back()->withErrors('Cannot delete a period referenced by a timetable.');
        }

        $gradeLevelId = $period->grade_level_id;
        $period->delete();

        return redirect()->route('admin.periods.index', ['grade_level' => $gradeLevelId])
            ->with('notification', 'Period deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,grade_level_id'],
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'order' => ['required', 'integer', 'min:1', Rule::unique('periods', 'order')->where(
                fn ($query) => $query->where('grade_level_id', $request->input('grade_level_id'))
            )->ignore($request->route('period')?->id)],
            'is_break' => ['sometimes', 'boolean'],
        ]);
    }
}