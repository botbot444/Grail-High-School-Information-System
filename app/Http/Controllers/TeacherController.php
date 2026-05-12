<?php

namespace App\Http\Controllers;

use App\Models\ClassSubject;
use App\Models\Student;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        // For demo: get the first assignment for the logged in teacher
        $assignment = ClassSubject::with(['schoolClass', 'subject'])->first();
        
        // Get students in that class
        $students = Student::where('school_class_id', $assignment->school_class_id)->get();

        return view('teacher.mark-entry', compact('assignment', 'students'));
    }
}