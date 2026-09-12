@extends('layouts.app')

@section('title', 'Terms')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-people-chord"></i>
                    <h2>{{ $terms->total() }}</h2>
                    <p>Registered Terms</p>
                </div>
            </div>

            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:15px;">
                    <h3 style="margin:0;">Manage Terms</h3>
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
                <a href="{{ route('admin.terms.create') }}" class="btn" style="background: #177aa4; color: white; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Add New Term
                </a>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Academic Year</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>School Days</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($terms as $term)
                            <tr>
                                <td>{{ $term->academicYear->label ?? chr(8212) }}</td>
                                <td>{{ $term->name }}</td>
                                <td>{{ $term->start_date->format('M d, Y') }}</td>
                                <td>{{ $term->end_date->format('M d, Y') }}</td>
                                <td>{{ $term->school_days }}</td>
                                <td>
                                    @if ($term->is_current)
                                        <span style="color:#177aa4; font-weight:bold;">Current</span>
                                    @else
                                        <span style="color:gray;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.terms.edit', $term) }}" style="color: #177aa4; text-decoration: none;">Edit</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <form method="POST" action="{{ route('admin.terms.destroy', $term) }}" style="display:inline; margin:0; padding:0;" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; text-decoration: underline;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align: center; color: gray;">No terms found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    {{ $terms->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
