@extends('layouts.app')

@section('title', 'Parent Details')

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <h2 style="color: #177aa4;">{{ $parent->full_name }}</h2>
                <div style="display:flex; gap: 8px;">
                    <a href="{{ route('admin.parents.edit', $parent) }}" class="btn"
                        style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    <a href="{{ route('admin.parents.index') }}" class="btn"
                        style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration:none;">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="table-section" style="max-width: 700px;">
                <dl style="display:grid; grid-template-columns:1fr 2fr; gap:16px;">
                    <dt style="font-weight:700; color:#475569;">Name</dt>
                    <dd>{{ $parent->full_name }}</dd>

                    <dt style="font-weight:700; color:#475569;">Email</dt>
                    <dd>{{ $parent->email }}</dd>

                    <dt style="font-weight:700; color:#475569;">Phone</dt>
                    <dd>{{ $parent->phone ?? 'N/A' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Address</dt>
                    <dd>{{ $parent->address ?? 'N/A' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Occupation</dt>
                    <dd>{{ $parent->occupation ?? 'N/A' }}</dd>

                    <dt style="font-weight:700; color:#475569;">National ID</dt>
                    <dd>{{ $parent->national_id ?? 'N/A' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Linked Account</dt>
                    <dd>{{ $parent->user?->email ?? 'No linked user' }}</dd>

                    <dt style="font-weight:700; color:#475569;">Children</dt>
                    <dd>
                        @php
                            $children = \App\Models\Student::where('parent_user_id', $parent->user_id)->get();
                        @endphp
                        @if ($children->isNotEmpty())
                            <ul style="list-style:none;padding:0;margin:0;">
                                @foreach ($children as $c)
                                    <li>{{ $c->full_name }} — {{ $c->schoolClass?->class_name ?? 'No Class' }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:gray;">No linked students</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
