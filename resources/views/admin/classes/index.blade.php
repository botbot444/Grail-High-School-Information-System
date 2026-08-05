@extends('layouts.app')

@section('title', 'Classes & Subjects')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            

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

            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Manage Classes</h3>
                <a href="{{ route('admin.classes.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Add New Class
                </a>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Grade Level</th>
                            <th>Homeroom Teacher</th>
                            <th>Subjects</th>
                            <th>Actions</th>
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
                                <td>
                                    <a href="{{ route('admin.classes.show', $class) }}"
                                        style="color: #177aa4; text-decoration: none;">View</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <a href="{{ route('admin.classes.edit', $class) }}"
                                        style="color: #177aa4; text-decoration: none;">Edit</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <form method="POST" action="{{ route('admin.classes.destroy', $class) }}"
                                        style="display:inline; margin:0; padding:0;"
                                        onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; text-decoration: underline;">Delete</button>
                                    </form>
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
