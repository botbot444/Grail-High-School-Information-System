@extends('layouts.app')
@section('title', 'My Timetable')
@section('content')
    <div class="portal-container" style="padding:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h1>My Timetable</h1>
                <p>{{ $student->schoolClass?->display_name ?? 'No class assigned' }}</p>
            </div>
            <form method="GET"><select name="term_id" onchange="this.form.submit()">
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>{{ $option->name }}
                            ({{ $option->academicYear?->label }})</option>
                    @endforeach
                </select></form>
        </div>
        @if ($student->schoolClass)
            @include('shared.timetable-grid', [
                'periods' => $student->schoolClass->gradeLevel?->periods()->orderBy('order')->get() ?? collect(),
                'slots' => $slots,
            ])
        @else
            <p>This student is not assigned to a class.</p>
        @endif
    </div>
@endsection
