<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isTeacher()) {
            return redirect()->route('teacher.marks');
        }
        if ($user->isParent()) {
            return redirect()->route('parent.dashboard');
        }
        if ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        $assignments = ClassSubject::with(['schoolClass.students', 'subject'])
            ->where('teacher_id', $user->id)
            ->get();

        return view('dashboard', compact('assignments'));
    }
}