@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        <div class="sidebar">

            <div class="logo">
                <h2>GRAIL</h2>
            </div>

            <ul class="menu">

                <li>
                    <a href="{{ route('admin.dashboard') }}" class="nav-item active">
                        <i class="fa-solid fa-house"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.students.index') }}" class="nav-item">
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
                    <a href="#" class="nav-item">
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

            <div class="topbar">

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" placeholder="Search students, staff...">
                </div>

                <div class="profile">
                    <div>
                        <h4>{{ Auth::user()->name }}</h4>
                        <small style="color:gray;">
                            System Administrator
                        </small>
                    </div>
                </div>

            </div>

            <div class="cards">

                <div class="card">
                    <i class="fa-solid fa-user-graduate"></i>
                    <h2>{{ $totalStudents }}</h2>
                    <p>Total Students</p>
                </div>

                <div class="card">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <h2>{{ $totalStaff }}</h2>
                    <p>Active Teachers & Staff</p>
                </div>

                <div class="card">
                    <i class="fa-solid fa-money-check-dollar"></i>
                    <h2>K{{ number_format($feesCollected) }}</h2>
                    <p>Fees Collected</p>
                </div>

            </div>

            <div class="content-grid">

                <div class="table-section">

                    <h3>Student Roster & Payments</h3>

                    <table>

                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Fee Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($students as $student)
                                <tr>

                                    <td>{{ $student['name'] }}</td>

                                    <td>{{ $student['class'] }}</td>

                                    <td>
                                        @if ($student['fee_status'] === 'cleared')
                                            <span class="status paid">
                                                Cleared
                                            </span>
                                        @else
                                            <span class="status pending">
                                                K{{ number_format($student['balance']) }} Due
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.students.show', $student['id']) }}" class="btn"
                                            style="background: #e2e8f0; color: #177aa4; padding: 5px 10px;">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: gray;">No students found.</td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div>

                    <div class="widget">

                        <h3 style="color: #177aa4; margin-bottom: 15px">Quick Actions</h3>

                        <div style="display: flex; flex-direction: column; gap: 10px">

                            <a href="{{ route('admin.students.create') }}" class="btn"
                                style="background: #177aa4; color: white; text-align: left; text-decoration: none;">
                                <i class="fa-solid fa-user-plus"></i> Add New Student
                            </a>

                            <a href="{{ route('admin.teachers.index') }}" class="btn"
                                style="background: #177aa4; color: white; text-align: left; text-decoration: none;">
                                <i class="fa-solid fa-chalkboard-user"></i> Teachers & Staff
                            </a>

                            <a href="{{ route('admin.classes.index') }}" class="btn"
                                style="background: #61b0e6; color: white; text-align: left; text-decoration: none;">
                                <i class="fa-solid fa-book"></i> Classes & Subjects
                            </a>
                            <div class="widget">

                                <h3 style="color: #177aa4">Announcements</h3>

                                <div class="announcement" style="margin-top: 15px">
                                    <strong>System Notice</strong>
                                    <p style="color: gray; font-size: 0.9rem; margin-top: 5px;">Latest system updates are
                                        now
                                        available.</p>
                                </div>

                                <div class="announcement">
                                    <strong>Academic Calendar</strong>
                                    <p style="color: gray; font-size: 0.9rem; margin-top: 5px;">Term 2 exams begin next
                                        month.</p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        @endsection
