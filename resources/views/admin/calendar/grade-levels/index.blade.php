@extends('layouts.app')

@section('title', 'Grade Levels')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[72px]" id="mainContent">
            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-layer-group"></i>
                    <h2>{{ $gradeLevels->total() }}</h2>
                    <p>Grade Levels</p>
                </div>
            </div>

            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Manage Grade Levels</h3>
                <a href="{{ route('admin.grade-levels.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px; text-decoration:none;">
                    <i class="fa-solid fa-plus"></i> Add New Grade Level
                </a>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gradeLevels as $gradeLevel)
                            <tr>
                                <td>{{ $gradeLevel->name }}</td>
                                <td>{{ $gradeLevel->order }}</td>
                                <td>
                                    <a href="{{ route('admin.grade-levels.edit', $gradeLevel) }}" style="color: #177aa4; text-decoration: none;">Edit</a>
                                    <span style="color: #cbd5e1;"> | </span>
                                    <form method="POST" action="{{ route('admin.grade-levels.destroy', $gradeLevel) }}" style="display:inline; margin:0; padding:0;" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; text-decoration: underline;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align: center; color: gray;">No grade levels found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 20px;">
                    {{ $gradeLevels->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection