<?php

namespace App\Http\Controllers;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {   // Checks if the data has a valid email and password is not blank
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        // Attempt to log in the user with the provided credentials // Fails if the email and password are not in the database
        if(Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return match(Auth::user()->role_name) {

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
        // If log in attempt fails, go back to the login page with an error message
        return back()->withErrors([
            'email' => 'Invalid credentials.'
        ]);
    }
}