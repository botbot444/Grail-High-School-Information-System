@extends('layouts.app')
@section('title', 'Create Period')
@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">@include('admin.sidebar') @include('admin.header')<div
            class="main-content main-transition pt-[72px]" id="mainContent">
            <h2>Create Period</h2>
            @include('admin.calendar.periods._form', [
                'period' => null,
                'action' => route('admin.periods.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
@endsection
