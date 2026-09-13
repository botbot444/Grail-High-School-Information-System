@extends('layouts.teacher')

@section('title', 'Edit Assignment – Teacher Portal')

@section('page')
    <div class="max-w-3xl">
        <h1 class="font-headline-md text-headline-md text-primary font-bold mb-6">Edit Assignment</h1>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3">
                <ul class="list-disc ml-5 font-body-sm text-body-sm text-on-error-container space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.assignments.update', $assignment->assignment_id) }}"
              class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-6">
            @include('teacher.assignments._form', ['mode' => 'edit'])
        </form>
    </div>
@endsection
