@extends('layouts.app')

@section('title', 'Student Details')

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

        <div class="sidebar">

            <div class="logo">
                <h2>GRAIL</h2>
            </div>

            <ul class="menu">

                <li>
                    <a href="{{ route('admin.dashboard') }}" class="nav-item">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.students.index') }}" class="nav-item active">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Manage Students</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.teachers.index') }}" class="nav-item">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Teachers & Staff</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.classes.index') }}" class="nav-item">
                        <i class="fa-solid fa-book"></i>
                        <span>Classes & Subjects</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.classes.index') }}" class="nav-item">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Fee Structures</span>
                    </a>
                </li>

                <li style="margin-top:40px;">

                    <form method="POST" action="{{ route('logout') }}">

                        @csrf

                        <button type="submit" class="nav-item" style="border:none;background:none;width:100%;color:white;">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>

                    </form>

                </li>

            </ul>

        </div>

        <div class="main-content">

            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="color: #177aa4;">{{ $student->full_name }}</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.students.edit', $student->student_id) }}" class="btn"
                        style="background: #61b0e6; color: white; padding: 10px 15px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    <a href="{{ route('admin.students.index') }}" class="btn"
                        style="background: #e2e8f0; color: #1e293b; padding: 10px 15px;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="content-grid">

                <div class="table-section">

                    <h3>Student Information</h3>

                    <table style="width: 100%; margin-top: 15px;">
                        <tr>
                            <th style="text-align: left; padding: 10px; color:#1e293b; background: #f1f5f9;">Student Number
                            </th>
                            <td style="padding: 10px;">{{ $student->student_number }}</td>
                        </tr>
                        <tr style="background: #f9fafb;">
                            <th style="text-align: left; padding: 10px;">Full Name</th>
                            <td style="padding: 10px;">{{ $student->full_name }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding: 10px; color:#1e293b; background: #f1f5f9;">Date of Birth
                            </th>
                            <td style="padding: 10px;">{{ $student->date_of_birth?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr style="background: #f9fafb;">
                            <th style="text-align: left; padding: 10px;">Gender</th>
                            <td style="padding: 10px;">{{ $student->gender }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding: 10px; color:#1e293b; background: #f1f5f9;">Class</th>
                            <td style="padding: 10px;">{{ $student->schoolClass?->class_name ?? 'N/A' }}</td>
                        </tr>
                        <tr style="background: #f9fafb;">
                            <th style="text-align: left; padding: 10px;">Enrollment Date</th>
                            <td style="padding: 10px;">{{ $student->enrolment_date?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding: 10px; color:#1e293b; background: #f1f5f9;">Guardian Name
                            </th>
                            <td style="padding: 10px;">{{ $student->guardian_name ?? 'N/A' }}</td>
                        </tr>
                        <tr style="background: #f9fafb;">
                            <th style="text-align: left; padding: 10px;">Guardian Phone</th>
                            <td style="padding: 10px;">{{ $student->guardian_phone ?? 'N/A' }}</td>
                        </tr>
                    </table>

                </div>

                <div>

                    <div class="widget">
                        <h3 style="color: #177aa4; margin-bottom: 15px;">Academics</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="padding: 10px; background: #f1f5f9; border-radius: 8px;">
                                <strong>Total Grades</strong><br>
                                <span style="font-size: 1.5rem; color: #177aa4;">{{ $student->grades->count() }}</span>
                            </div>
                            <div style="padding: 10px; background: #f1f5f9; border-radius: 8px;">
                                <strong>Attendance Records</strong><br>
                                <span style="font-size: 1.5rem; color: #177aa4;">{{ $student->attendance->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="widget">
                        <h3 style="color: #177aa4; margin-bottom: 15px;">Financials</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div style="padding: 10px; background: #f1f5f9; border-radius: 8px;">
                                <strong>Total Fees Due</strong><br>
                                <span
                                    style="font-size: 1.2rem; color: #177aa4;">K{{ number_format($student->fees()->sum('amount_due')) }}</span>
                            </div>
                            <div style="padding: 10px; background: #f1f5f9; border-radius: 8px;">
                                <strong>Paid</strong><br>
                                <span
                                    style="font-size: 1.2rem; color: #15803d;">K{{ number_format($student->fees()->where('status', 'paid')->sum('amount_paid')) }}</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
