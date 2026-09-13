@extends('layouts.app')
@section('title', 'Timetable Builder')
@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar') @include('admin.header')<div class="main-content main-transition pt-[72px]"
            id="mainContent">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2>Timetable Builder</h2>
                    <p>{{ $schoolClass?->display_name ?? 'Select a class' }} · {{ $term?->name ?? 'No term' }}</p>
                </div>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="GET" action="{{ route('admin.timetable.index') }}" style="display:flex; gap:12px; margin:20px 0;">
                <select name="class_id" onchange="this.form.submit()">
                    @foreach ($classes as $class)
                        <option value="{{ $class->class_id }}" @selected($schoolClass?->class_id === $class->class_id)>{{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
                <select name="term_id" onchange="this.form.submit()">
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>{{ $option->name }}
                            ({{ $option->academicYear?->label }})</option>
                    @endforeach
                </select>
            </form>
            @if ($schoolClass && $term)
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                @foreach ($periods as $period)
                                    <th>{{ $period->name }}<br><small>{{ $period->start_time?->format('H:i') }}-{{ $period->end_time?->format('H:i') }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($days as $day)
                                <tr>
                                    <th>{{ $day }}</th>
                                    @foreach ($periods as $period)
                                        @php($slot = $slots->get($day . '-' . $period->id))
                                        <td style="min-width:190px; vertical-align:top;">
                                            @if ($period->is_break)
                                                <strong>Break</strong>
                                            @else
                                                <form method="POST" action="{{ route('admin.timetable.slots.store') }}">
                                                    @csrf
                                                    <input type="hidden" name="school_class_id"
                                                        value="{{ $schoolClass->class_id }}"><input type="hidden"
                                                        name="period_id" value="{{ $period->id }}"><input type="hidden"
                                                        name="day_of_week" value="{{ $day }}"><input
                                                        type="hidden" name="term_id" value="{{ $term->term_id }}">
                                                    <select name="subject_id">
                                                        <option value="">Free</option>
                                                        @foreach ($subjects as $subject)
                                                            <option value="{{ $subject->subject_id }}"
                                                                @selected($slot?->subject_id === $subject->subject_id)>{{ $subject->subject_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <select name="teacher_id">
                                                        <option value="">Unassigned</option>
                                                        @foreach ($teachers as $teacher)
                                                            <option value="{{ $teacher->teacher_id }}"
                                                                @selected($slot?->teacher_id === $teacher->teacher_id)>{{ $teacher->full_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit">Save</button>
                                                </form>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; gap:20px; margin-top:24px;">
                    <form method="POST" action="{{ route('admin.timetable.clear') }}"
                        onsubmit="return confirm('Clear this timetable?')">@csrf<input type="hidden" name="school_class_id"
                            value="{{ $schoolClass->class_id }}"><input type="hidden" name="term_id"
                            value="{{ $term->term_id }}"><button type="submit">Clear timetable</button></form>
                    <form method="POST" action="{{ route('admin.timetable.copy') }}">@csrf<input type="hidden"
                            name="school_class_id" value="{{ $schoolClass->class_id }}"><input type="hidden"
                            name="source_term_id" value="{{ $term->term_id }}"><select name="target_term_id" required>
                            <option value="">Copy to term...</option>
                            @foreach ($terms as $option)
                                @if ($option->term_id !== $term->term_id)
                                    <option value="{{ $option->term_id }}">{{ $option->name }}</option>
                                @endif
                            @endforeach
                        </select><button type="submit">Copy</button></form>
                </div>
            @endif
        </div>
    </div>
@endsection
