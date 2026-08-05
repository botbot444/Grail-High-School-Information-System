@extends('layouts.app')

@section('title', 'Add Subject')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
                @include('admin.sidebar')
        @include('admin.header')

<div class="main-content main-transition pt-[72px]" id="mainContent">
            <h2>Add Subject</h2>
            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @csrf
                <div class="input-group">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" required>
                </div>
                <button class="btn" type="submit">Create</button>
            </form>
        </div>
    </div>
@endsection
