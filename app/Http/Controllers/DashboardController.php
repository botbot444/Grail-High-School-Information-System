<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.marks');
        }
        if ($user->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        }
        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        $teacher = $user->teacher;
        $assignments = $teacher 
            ? ClassSubject::with(['schoolClass.students', 'subject'])
                ->where('teacher_id', $teacher->teacher_id)
                ->get()
            : collect();

        return view('dashboard', compact('assignments'));
    }
}