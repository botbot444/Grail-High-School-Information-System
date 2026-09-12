@extends('layouts.app')

@section('title', 'Holidays')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-calendar-day"></i>
                    <h2>{{ $holidays->total() }}</h2>
                    <p>Registered Holidays</p>
                </div>
            </div>

            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:15px;">
                    <h3 style="margin:0;">Manage Holidays</h3>
                    <form method="GET" style="display:flex; align-items:center; gap:10px;">
                        <label for="academic_year_id" style="margin:0; color:gray; font-size:13px;">Filter by year:</label>
                        <select name="academic_year_id" id="academic_year_id" onchange="this.form.submit()" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:8px;">
                            <option value="">All years</option>
                            @foreach ($academicYears as $id => $label)
                                <option value="{{ $id }}" {{ $id == $academicYearId ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <a href="{{ route('admin.holidays.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Add New Holiday
                </a>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Academic Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($holidays as $holiday)
                            <tr>
                                <td>{{ $holiday->date->format('M d, Y') }}</td>
                                <td>{{ $holiday->description }}</td>
                                <td>{{ $holiday->academicYear->label ?? chr(8212) }}</td>
                                <td>
                                    <a href="{{ route('admin.holidays.edit', $holiday) }}" style="color: #177aa4; text-decoration: none;">Edit</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}" style="display:inline; margin:0; padding:0;" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; text-decoration: underline;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align: center; color: gray;">No holidays found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    {{ $holidays->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection