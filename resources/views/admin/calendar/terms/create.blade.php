@extends('layouts.app')

@section('title', 'Create Term')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">Add New Term</h2>
                    <p style="color: gray;">Define a teaching window within an academic year.</p>
                </div>
                <a href="{{ route('admin.terms.index') }}" class="btn"
                    style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Terms
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
                <form method="POST" action="{{ route('admin.terms.store') }}">
                    @csrf

                    <div class="input-group">
                        <label>Academic Year *</label>
                        <select name="academic_year_id" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                            <option value="">— Select year —</option>
                            @foreach ($academicYears as $id => $label)
                                <option value="{{ $id }}" {{ old('academic_year_id') == $id ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Term Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Term 1, Term 2, Term 3"
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

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn save-btn" style="background: #177aa4; color: white; padding: 12px 20px;">
                            <i class="fa-solid fa-plus"></i> Create Term
                        </button>
                        <a href="{{ route('admin.terms.index') }}" class="btn"
                            style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;">
                            <i class="fa-solid fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection