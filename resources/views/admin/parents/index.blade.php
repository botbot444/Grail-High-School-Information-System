@extends('layouts.app')

@section('title', 'Parents')

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            

            <div style="margin-bottom: 25px; display:flex; justify-content:flex-end;">
                <a href="{{ route('admin.parents.create') }}" class="btn"
                    style="background: #177aa4; color: white; padding: 12px 20px;">
                    <i class="fa-solid fa-user-plus"></i> Add New Parent
                </a>
            </div>

            <div class="table-section">
                <h3>Parent Directory</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Occupation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($parents as $parent)
                            <tr>
                                <td>{{ $parent->full_name }}</td>
                                <td>{{ $parent->email }}</td>
                                <td>{{ $parent->phone ?? 'N/A' }}</td>
                                <td>{{ $parent->occupation ?? 'N/A' }}</td>
                                <td style="display: flex; gap: 8px;">
                                    <a href="{{ route('admin.parents.show', $parent->parent_id) }}" class="btn"
                                        style="background: #61b0e6; color: white; padding: 5px 10px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.parents.edit', $parent->parent_id) }}" class="btn"
                                        style="background: #e2e8f0; color: #177aa4; padding: 5px 10px; font-size: 0.85rem;">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.parents.destroy', $parent->parent_id) }}"
                                        style="display: inline;" onsubmit="return confirm('Delete this parent?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn"
                                            style="background: #fee2e2; color: #b91c1c; padding: 5px 10px; font-size: 0.85rem; border: none;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: gray;">No parents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div style="margin-top: 20px;">
                    {{ $parents->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
