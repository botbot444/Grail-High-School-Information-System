@extends('layouts.app')

@section('title', 'Edit Subject')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <h2>Edit Subject</h2>
            <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                @csrf
                @method('PUT')
                <div class="input-group">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" value="{{ old('subject_name', $subject->subject_name) }}"
                        required>
                </div>
                <button class="btn" type="submit">Save</button>
            </form>
        </div>
    </div>
@endsection
