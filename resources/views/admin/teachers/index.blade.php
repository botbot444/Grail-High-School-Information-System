@extends('layouts.app')

@section('title', 'Teachers & Staff')

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
                    <a href="{{ route('admin.teachers.index') }}" class="nav-item active">
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
                    <input type="text" placeholder="Search teachers, staff...">
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
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <h2>{{ $teachers->total() }}</h2>
                    <p>Total Teachers</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-user-plus"></i>
                    <h2>{{ $teachers->count() }}</h2>
                    <p>Showing This Page</p>
                </div>
            </div>

            <div class="table-section">
                <h3>Teachers & Staff Directory</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subjects</th>
                            <th>Classes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teachers as $teacher)
                            <tr>
                                <td>{{ $teacher->full_name }}</td>
                                <td>{{ $teacher->email }}</td>
                                <td>{{ $teacher->phone }}</td>
                                <td>
                                    @if ($teacher->classSubjects->isNotEmpty())
                                        <ul style="list-style:none;padding:0;margin:0;">
                                            @foreach ($teacher->classSubjects as $assignment)
                                                <li>{{ $assignment->subject?->subject_name ?? 'N/A' }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span style="color:gray;">No subjects assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($teacher->classSubjects->isNotEmpty())
                                        <ul style="list-style:none;padding:0;margin:0;">
                                            @foreach ($teacher->classSubjects as $assignment)
                                                <li>{{ $assignment->schoolClass?->class_name ?? 'N/A' }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span style="color:gray;">No classes assigned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: gray;">No teachers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    {{ $teachers->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
