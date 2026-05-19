@extends('layouts.app')

@section('title', 'Classes & Subjects')

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
                    <a href="{{ route('admin.classes.index') }}" class="nav-item active">
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
                    <input type="text" placeholder="Search classes, subjects...">
                </div>

                <div class="profile">
                    <div>
                        <h4>{{ Auth::user()->name }}</h4>
                        <small style="color:gray;">System Administrator</small>
                    </div>
                </div>
            </div>

            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-school"></i>
                    <h2>{{ $classes->total() }}</h2>
                    <p>Total Classes</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-book-open"></i>
                    <h2>{{ $classes->sum(fn($class) => $class->subjects->count()) }}</h2>
                    <p>Total Subjects</p>
                </div>
            </div>

            <div class="table-section">
                <h3>Class & Subject Assignments</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Grade Level</th>
                            <th>Homeroom Teacher</th>
                            <th>Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $class)
                            <tr>
                                <td>{{ $class->class_name }}</td>
                                <td>{{ $class->grade_level }}</td>
                                <td>{{ $class->teacher?->full_name ?? 'Unassigned' }}</td>
                                <td>
                                    @if ($class->subjects->isNotEmpty())
                                        <ul style="list-style:none;padding:0;margin:0;">
                                            @foreach ($class->subjects as $subject)
                                                <li>{{ $subject->subject_name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span style="color:gray;">No subjects assigned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: gray;">No classes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    {{ $classes->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
