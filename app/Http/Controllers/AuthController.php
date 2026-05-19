<?php

namespace App\Http\Controllers;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return match(Auth::user()->role) {

                'admin' =>
                    redirect()->route('admin.dashboard'),

                'teacher' =>
                    redirect()->route('teacher.marks'),

                'parent' =>
                    redirect()->route('parent.dashboard'),

                'student' =>
                    redirect()->route('student.dashboard'),

                default =>
                    redirect('/'),
            };
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.'
        ]);
    }
}