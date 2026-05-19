@extends('layouts.app')

@section('title', 'Add New Student')

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

            <div style="margin-bottom: 25px;">
                <h2 style="color: #177aa4;">Add New Student</h2>
            </div>

            @if ($errors->any())
                <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <strong>Errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="table-section" style="max-width: 600px;">

                <form method="POST" action="{{ route('admin.students.store') }}">

                    @csrf

                    <div class="input-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Gender *</label>
                        <select name="gender" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Student Number *</label>
                        <input type="text" name="student_number" value="{{ old('student_number') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Class *</label>
                        <select name="class_id" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->class_id }}"
                                    {{ old('class_id') == $class->class_id ? 'selected' : '' }}>
                                    {{ $class->class_name }} – {{ $class->grade_level }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Guardian Name</label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Guardian Phone</label>
                        <input type="tel" name="guardian_phone" value="{{ old('guardian_phone') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Enrollment Date</label>
                        <input type="date" name="enrolment_date" value="{{ old('enrolment_date') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn save-btn"
                            style="background: #177aa4; color: white; padding: 12px 20px;">
                            <i class="fa-solid fa-plus"></i> Add Student
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="btn"
                            style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;">
                            <i class="fa-solid fa-times"></i> Cancel
                        </a>
                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection
