@extends('layouts.app')
@section('title', 'Edit Period')
@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">@include('admin.sidebar') @include('admin.header')<div
            class="main-content main-transition pt-[72px]" id="mainContent">
            <h2>Edit Period</h2>
            @include('admin.calendar.periods._form', [
                'action' => route('admin.periods.update', $period),
                'method' => 'PUT',
            ])
        </div>
    </div>
@endsection
