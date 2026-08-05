@extends('layouts.app')

@section('title', 'Subject Details')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <div style="margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="color: #177aa4;">{{ $subject->subject_name }}</h2>
                    <p style="color: gray;">Subject details and management.</p>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn"
                        style="background: #e2e8f0; color: #177aa4; padding: 12px 20px; text-decoration:none;"><i
                            class="fa-solid fa-pen"></i> Edit</a>
                    <a href="{{ route('admin.subjects.index') }}" class="btn"
                        style="background: #e2e8f0; color: #1e293b; padding: 12px 20px; text-decoration:none;"><i
                            class="fa-solid fa-arrow-left"></i> Back</a>
                </div>
            </div>

            <div class="table-section" style="max-width: 700px;">
                <dl style="display:grid; grid-template-columns:1fr 2fr; gap:16px;">
                    <dt style="font-weight:700; color:#475569;">Subject Name</dt>
                    <dd>{{ $subject->subject_name }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
