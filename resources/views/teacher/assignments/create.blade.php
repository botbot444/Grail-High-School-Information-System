@extends('layouts.teacher')

@section('title', 'New Assignment – Teacher Portal')

@section('page')
    <div class="max-w-3xl">
        <h1 class="font-headline-md text-headline-md text-primary font-bold mb-6">New Assignment</h1>


        <form method="POST" action="{{ route('teacher.assignments.store') }}"
              class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6">
            @include('teacher.assignments._form', ['mode' => 'create'])
        </form>
    </div>
@endsection
