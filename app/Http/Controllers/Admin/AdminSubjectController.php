<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminSubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::withCount(['classes', 'teachers'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('subject_name', 'like', '%' . $request->search . '%');
            })
            ->when($request->status === 'offered', fn ($query) => $query->has('classes'))
            ->when($request->status === 'unassigned', fn ($query) => $query->doesntHave('classes'))
            ->orderBy('subject_name')
            ->paginate(20)
            ->withQueryString();

        $totalSubjects = Subject::count();
        $offeredCount = Subject::has('classes')->count();
        $unassignedCount = $totalSubjects - $offeredCount;
        $facultyCovered = DB::table('teacher_subjects')->distinct('teacher_id')->count('teacher_id');

        return view('admin.subjects.index', compact(
            'subjects', 'totalSubjects', 'offeredCount', 'unassignedCount', 'facultyCovered'
        ));
    }

    public function create()
    {
        $recentSubjects = Subject::latest()->take(3)->get();

        return view('admin.subjects.create', compact('recentSubjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_name' => 'required|string|max:255|unique:subjects,subject_name',
        ]);

        Subject::create($validated);
        return redirect()->route('admin.subjects.index')->with('notification', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        $subject->loadCount(['classes', 'teachers'])
            ->load(['classes', 'teachers']);

        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $subject->loadCount(['classes', 'teachers']);

        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_name' => ['required', 'string', 'max:255',
                Rule::unique('subjects', 'subject_name')->ignore($subject->subject_id, 'subject_id')],
        ]);

        $subject->update($validated);
        return redirect()->route('admin.subjects.show', $subject)->with('notification', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('notification', 'Subject deleted.');
    }
}
