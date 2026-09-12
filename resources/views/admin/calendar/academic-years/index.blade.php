@extends('layouts.app')

@section('title', 'Academic Years')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-calendar"></i>
                    <h2>{{ $academicYears->total() }}</h2>
                    <p>Academic Years</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <h2>{{ $academicYears->sum('terms_count') }}</h2>
                    <p>Total Terms</p>
                </div>
            </div>

            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Manage Academic Years</h3>
                <a href="{{ route('admin.academic-years.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Add New Academic Year
                </a>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Terms</th>
                            <th>Holidays</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($academicYears as $year)
                            <tr>
                                <td>{{ $year->label }}</td>
                                <td>{{ $year->start_date->format('M d, Y') }}</td>
                                <td>{{ $year->end_date->format('M d, Y') }}</td>
                                <td>{{ $year->terms_count }}</td>
                                <td>{{ $year->holidays_count }}</td>
                                <td>
                                    @if ($year->is_current)
                                        <span style="color:#177aa4; font-weight:bold;">Current</span>
                                    @else
                                        <span style="color:gray;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.academic-years.edit', $year) }}" style="color: #177aa4; text-decoration: none;">Edit</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" style="display:inline; margin:0; padding:0;" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; text-decoration: underline;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: gray;">No academic years found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    {{ $academicYears->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
