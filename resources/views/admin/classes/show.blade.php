@extends('layouts.app')

@section('title', 'Class Details')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">{{ $class->display_name }}</h2>
                    <p style="color: gray;">Class details, homeroom teacher, and assigned subjects.</p>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('admin.classes.edit', $class) }}" class="btn"
                        style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;"><i
                            class="fa-solid fa-pen"></i> Edit</a>
                    <a href="{{ route('admin.classes.index') }}" class="btn"
                        style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration:none;"><i
                            class="fa-solid fa-arrow-left"></i> Back</a>
                </div>
            </div>

            <div class="table-section" style="max-width: 700px;">
                <dl style="display:grid; grid-template-columns:1fr 2fr; gap:16px;">
                    <dt style="font-weight:700; color:#475569;">Class Name</dt>
                    <dd>{{ $class->class_name }}</dd>

                    <dt style="font-weight:700; color:#475569;">Grade Level</dt>
                    <dd>{{ $class->grade_level }}</dd>

                    <dt style="font-weight:700; color:#475569;">Homeroom Teacher</dt>
                    <dd>{{ $class->teacher?->full_name ?? 'Unassigned' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Assigned Subjects</dt>
                    <dd>
                        @if ($class->subjects->isNotEmpty())
                            <ul style="list-style:none;padding:0;margin:0;">
                                @foreach ($class->subjects as $subject)
                                    <li>{{ $subject->subject_name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:gray;">No assigned subjects</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
