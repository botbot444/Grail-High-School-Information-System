@extends('layouts.app')

@section('title', 'Add Parent')

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">Add New Parent</h2>
                    <p style="color: gray;">A login account will be created with a default password.</p>
                </div>
                <a href="{{ route('admin.parents.index') }}" class="btn"
                    style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Parents
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
                <form method="POST" action="{{ route('admin.parents.store') }}">
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
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" value="{{ old('occupation') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>National ID</label>
                        <input type="text" name="national_id" value="{{ old('national_id') }}"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Link Students</label>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            @forelse($students as $s)
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="student_ids[]" value="{{ $s->student_id }}">
                                    <small>{{ $s->full_name }} — {{ $s->schoolClass?->class_name ?? 'No Class' }}</small>
                                </label>
                            @empty
                                <small style="color:gray;">No unlinked students available</small>
                            @endforelse
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn save-btn"
                            style="background: #177aa4; color: white; padding: 12px 20px;">
                            <i class="fa-solid fa-plus"></i> Add Parent
                        </button>
                        <a href="{{ route('admin.parents.index') }}" class="btn"
                            style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;">
                            <i class="fa-solid fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
