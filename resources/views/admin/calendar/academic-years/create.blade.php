@extends('layouts.app')

@section('title', 'Create Academic Year')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">Add New Academic Year</h2>
                    <p style="color: gray;">Define the date window that organizes terms, holidays, grades and fees.</p>
                </div>
                <a href="{{ route('admin.academic-years.index') }}" class="btn"
                    style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Academic Years
                </a>
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
                <form method="POST" action="{{ route('admin.academic-years.store') }}">
                    @csrf

                    <div class="input-group">
                        <label>Label *</label>
                        <input type="text" name="label" value="{{ old('label') }}" required
                            placeholder="e.g. 2026 or 2026-2027"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>End Date *</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}
                                style="width: 18px; height: 18px; cursor:pointer;">
                            <span style="font-weight:600;">Set as current academic year</span>
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn save-btn" style="background: #177aa4; color: white; padding: 12px 20px;">
                            <i class="fa-solid fa-plus"></i> Create Academic Year
                        </button>
                        <a href="{{ route('admin.academic-years.index') }}" class="btn"
                            style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;">
                            <i class="fa-solid fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection