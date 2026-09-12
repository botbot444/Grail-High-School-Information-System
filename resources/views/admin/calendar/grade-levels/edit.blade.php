@extends('layouts.app')

@section('title', 'Edit Grade Level')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">Edit Grade Level</h2>
                    <p style="color: gray;">Update {{ $gradeLevel->name }}.</p>
                </div>
                <a href="{{ route('admin.grade-levels.index') }}" class="btn"
                    style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Grade Levels
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
                <form method="POST" action="{{ route('admin.grade-levels.update', $gradeLevel) }}">
                    @csrf
                    @method('PUT')

                    <div class="input-group">
                        <label>Name *</label>
                        <input type="text" name="name" value="{{ old('name', $gradeLevel->name) }}" required
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div class="input-group">
                        <label>Order *</label>
                        <input type="number" name="order" value="{{ old('order', $gradeLevel->order) }}" required min="1"
                            style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 25px;">
                        <button type="submit" class="btn save-btn" style="background: #177aa4; color: white; padding: 12px 20px;">
                            <i class="fa-solid fa-check"></i> Update Grade Level
                        </button>
                        <a href="{{ route('admin.grade-levels.index') }}" class="btn"
                            style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration: none;">
                            <i class="fa-solid fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection