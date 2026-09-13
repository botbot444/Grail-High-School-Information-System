@extends('layouts.app')
@section('title', 'Periods')
@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')
        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <h2>Periods</h2>
                    <p>Define the daily structure for one grade level.</p>
                </div>
                <a class="btn"
                    href="{{ route('admin.periods.create', ['grade_level' => $gradeLevel?->grade_level_id]) }}">Add
                    Period</a>
            </div>
            <form method="GET" action="{{ route('admin.periods.index') }}" style="margin-bottom:20px;">
                <label for="grade_level">Grade level</label>
                <select id="grade_level" name="grade_level" onchange="this.form.submit()">
                    @foreach ($gradeLevels as $level)
                        <option value="{{ $level->grade_level_id }}" @selected($gradeLevel?->grade_level_id === $level->grade_level_id)>{{ $level->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                            <tr>
                                <td>{{ $period->order }}</td>
                                <td>{{ $period->name }}</td>
                                <td>{{ $period->start_time?->format('H:i') }} - {{ $period->end_time?->format('H:i') }}</td>
                                <td>{{ $period->is_break ? 'Break' : 'Teaching' }}</td>
                                <td>
                                    <a href="{{ route('admin.periods.edit', $period) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.periods.destroy', $period) }}"
                                        style="display:inline" onsubmit="return confirm('Delete this period?')">@csrf
                                        @method('DELETE') <button type="submit">Delete</button></form>
                                </td>
                            </tr>
                        @empty <tr>
                                <td colspan="5">No periods defined for this grade level.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
