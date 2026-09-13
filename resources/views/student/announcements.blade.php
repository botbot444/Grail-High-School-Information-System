@extends('layouts.student')

@section('title', 'Announcements')
@section('page-title', 'School Announcements')
@section('page-subtitle', 'Notices for your class and grade level')

@section('content')
    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow">
        @include('student.partials.empty-state', [
            'icon'    => 'campaign',
            'title'   => 'Announcements are not live yet',
            'message' => 'Once the school can publish notices, the ones targeted at your class and grade level will appear here, newest first.',
            'phase'   => 'Phase 5',
        ])
    </section>
@endsection
