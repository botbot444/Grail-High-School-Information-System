@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Manage Subjects</h3>
                <a href="{{ route('admin.subjects.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Add New Subject
                </a>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            <tr>
                                <td>{{ $subject->subject_name }}</td>
                                <td>
                                    <a href="{{ route('admin.subjects.show', $subject) }}"
                                        style="color: #177aa4; text-decoration: none;">View</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <a href="{{ route('admin.subjects.edit', $subject) }}"
                                        style="color: #177aa4; text-decoration: none;">Edit</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
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
                                <td colspan="2">No subjects</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top:20px;">{{ $subjects->links() }}</div>
            </div>
        </div>
    </div>
@endsection
