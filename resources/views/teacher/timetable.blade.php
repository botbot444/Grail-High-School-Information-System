@extends('layouts.teacher')
@section('title', 'My Timetable')
@section('page')
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h1>My Timetable</h1>
                <p>Classes assigned to {{ $teacher?->full_name ?? 'you' }}.</p>
            </div>
            <form method="GET"><select name="term_id" onchange="this.form.submit()">
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>{{ $option->name }}
                            ({{ $option->academicYear?->label }})</option>
                    @endforeach
                </select></form>
        </div>
        @forelse ($classes as $classId => $classSlots)
            @php($class = $classSlots->first()->schoolClass)<section style="margin-bottom:30px;">
                <h2>{{ $class->display_name }}</h2>@include('shared.timetable-grid', [
                    'periods' => $class->gradeLevel?->periods()->orderBy('order')->get() ?? collect(),
                    'slots' => $classSlots,
                ])
            </section>
        @empty <p>No timetable slots are assigned to you for this term.</p>
        @endforelse
    </div>
@endsection
