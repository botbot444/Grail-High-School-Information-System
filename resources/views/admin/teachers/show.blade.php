@extends('layouts.app')

@section('title', 'Teacher Details')

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <h2 style="color: #177aa4;">{{ $teacher->full_name }}</h2>
                <div style="display:flex; gap: 8px;">
                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn"
                        style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    <a href="{{ route('admin.teachers.index') }}" class="btn"
                        style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration:none;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="table-section" style="max-width: 700px;">
                <dl style="display:grid; grid-template-columns:1fr 2fr; gap:16px;">
                    <dt style="font-weight:700; color:#475569;">Name</dt>
                    <dd>{{ $teacher->full_name }}</dd>

                    <dt style="font-weight:700; color:#475569;">Email</dt>
                    <dd>{{ $teacher->email }}</dd>

                    <dt style="font-weight:700; color:#475569;">Phone</dt>
                    <dd>{{ $teacher->phone ?? 'N/A' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Linked Account</dt>
                    <dd>{{ $teacher->user?->email ?? 'No linked user' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Homeroom Classes</dt>
                    <dd>
                        @php
                            $homerooms = \App\Models\SchoolClass::where('teacher_id', $teacher->teacher_id)->get();
                        @endphp
                        @if ($homerooms->isNotEmpty())
                            <ul style="list-style:none;padding:0;margin:0;">
                                @foreach ($homerooms as $c)
                                    <li>{{ $c->display_name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:gray;">No homeroom classes assigned</span>
                        @endif
                    </dd>

                    <dt style="font-weight:700; color:#475569;">Subjects</dt>
                    <dd>
                        @if ($teacher->classSubjects->isNotEmpty())
                            <ul style="list-style:none;padding:0;margin:0;">
                                @foreach ($teacher->classSubjects as $assignment)
                                    <li>{{ $assignment->subject?->subject_name ?? 'N/A' }} —
                                        {{ $assignment->schoolClass?->class_name ?? 'N/A' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:gray;">No assignments yet.</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
