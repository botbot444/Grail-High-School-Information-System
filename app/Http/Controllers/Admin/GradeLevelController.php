<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeLevelController extends Controller
{
    public function index()
    {
        $gradeLevels = GradeLevel::orderBy('order')->paginate(15);
        return view('admin.calendar.grade-levels.index', compact('gradeLevels'));
    }

    public function create()
    {
        return view('admin.calendar.grade-levels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255|unique:grade_levels,name',
            'order' => 'required|integer|min:1|unique:grade_levels,order',
        ]);

        GradeLevel::create($validated);

        return redirect()->route('admin.grade-levels.index')
            ->with('notification', 'Grade level created successfully.');
    }

    public function edit(GradeLevel $gradeLevel)
    {
        return view('admin.calendar.grade-levels.edit', compact('gradeLevel'));
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255',
                Rule::unique('grade_levels', 'name')->ignore($gradeLevel->grade_level_id, 'grade_level_id')],
            'order' => ['required', 'integer', 'min:1',
                Rule::unique('grade_levels', 'order')->ignore($gradeLevel->grade_level_id, 'grade_level_id')],
        ]);

        $gradeLevel->update($validated);

        return redirect()->route('admin.grade-levels.index')
            ->with('notification', 'Grade level updated successfully.');
    }

    public function destroy(GradeLevel $gradeLevel)
    {
        if ($gradeLevel->classes()->exists()) {
            return back()->withErrors('Cannot delete a grade level that has classes assigned.');
        }

        $gradeLevel->delete();

        return redirect()->route('admin.grade-levels.index')
            ->with('notification', 'Grade level deleted successfully.');
    }
}