@extends('layouts.teacher')

@section('title', 'Edit Assignment – Teacher Portal')

@section('page')
    <div class="max-w-3xl">
        <h1 class="font-headline-md text-headline-md text-primary font-bold mb-6">Edit Assignment</h1>


        <form method="POST" action="{{ route('teacher.assignments.update', $assignment->assignment_id) }}"
              class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6">
            @include('teacher.assignments._form', ['mode' => 'edit'])
        </form>
    </div>
@endsection
