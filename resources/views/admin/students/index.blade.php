@extends('layouts.app')

@section('title', 'Manage Students')

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

            <div class="topbar">

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search students...">
                </div>

                <div class="profile">
                    <div>
                        <h4>{{ Auth::user()->name }}</h4>
                        <small style="color:gray;">System Administrator</small>
                    </div>
                </div>

            </div>

            <div style="margin-bottom: 25px;">
                <a href="{{ route('admin.students.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px;">
                    <i class="fa-solid fa-user-plus"></i> Add New Student
                </a>
            </div>

            <div class="table-section">

                <h3>All Students</h3>

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Date of Birth</th>
                            <th>Class</th>
                            <th>Enrollment Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($students as $student)
                            <tr>

                                <td>{{ $student->student_number }}</td>

                                <td>{{ $student->full_name }}</td>

                                <td>{{ $student->date_of_birth?->format('M d, Y') ?? 'N/A' }}</td>

                                <td>{{ $student->schoolClass?->class_name ?? 'N/A' }}</td>

                                <td>{{ $student->enrolment_date?->format('M d, Y') ?? 'N/A' }}</td>

                                <td style="display: flex; gap: 10px;">
                                    <a href="{{ route('admin.students.show', $student->student_id) }}" class="btn"
                                        style="background: #61b0e6; color: white; padding: 5px 10px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student->student_id) }}" class="btn"
                                        style="background: #e2e8f0; color: #177aa4; padding: 5px 10px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.students.destroy', $student->student_id) }}"
                                        style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn"
                                            style="background: #fee2e2; color: #b91c1c; padding: 5px 10px; font-size: 0.85rem; border: none;">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: gray;">No students found.</td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

                <div style="margin-top: 20px;">
                    {{ $students->links() }}
                </div>

            </div>

        </div>

    </div>

@endsection
