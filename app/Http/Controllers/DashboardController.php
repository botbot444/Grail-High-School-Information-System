<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isTeacher()) {
            // Get teacher's assignments with class, subject, and students
            $assignments = ClassSubject::with(['schoolClass.students', 'subject'])
                ->where('teacher_id', $user->id)
                ->get();

            return view('dashboard', compact('assignments'));
        }

        // For other roles, just show default dashboard
        return view('dashboard');
    }
}