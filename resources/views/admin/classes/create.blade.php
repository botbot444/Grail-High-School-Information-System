@extends('layouts.app')

@section('title', 'Add Class')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">Add New Class</h2>
                    <p style="color: gray;">Create a class and optionally assign a homeroom teacher.</p>
                </div>
                <a href="{{ route('admin.classes.index') }}" class="btn"
                    style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;"><i
                        class="fa-solid fa-arrow-left"></i> Back to Classes</a>
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
                <form method="POST" action="{{ route('admin.classes.store') }}">
                    @csrf

                    <div class="input-group">
                        <label>Class Name *</label>
                        <input type="text" name="class_name" value="{{ old('class_name') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Grade Level *</label>
                        <input type="text" name="grade_level" value="{{ old('grade_level') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Homeroom Teacher</label>
                        <select name="teacher_id"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="">None</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->teacher_id }}">{{ $teacher->full_name }}
                                    ({{ $teacher->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Assign Subjects</label>
                        <div
                            style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; max-height: 250px; overflow-y: auto;">
                            @forelse ($subjects as $subject)
                                <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->subject_id }}"
                                        style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span>{{ $subject->subject_name }}</span>
                                </label>
                            @empty
                                <p style="color: gray; margin: 0;">No subjects available</p>
                            @endforelse
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn save-btn"
                            style="background: #177aa4; color: white; padding: 12px 20px;"><i class="fa-solid fa-plus"></i>
                            Add Class</button>
                        <a href="{{ route('admin.classes.index') }}" class="btn"
                            style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;"><i
                                class="fa-solid fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
