@extends('layouts.student')

@section('title', $assignment->title)
@section('page-title', 'Assignment')
@section('page-subtitle', $assignment->classSubject?->subject?->subject_name)

@section('content')
    <a href="{{ route('student.assignments.index') }}"
       class="inline-flex items-center gap-1.5 mb-space-md text-label-md font-medium text-primary hover:underline">
        <span class="material-symbols-outlined text-lg">arrow_back</span>
        All assignments
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-md">
        {{-- Brief --}}
        <section class="lg:col-span-2 bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
            <header class="px-space-md py-space-md border-b border-outline-variant/50">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-headline-lg font-headline-lg text-on-surface">{{ $assignment->title }}</h2>
                        <p class="mt-1 text-body-md text-on-surface-variant">
                            {{ $assignment->classSubject?->subject?->subject_name }}
                            @if ($assignment->classSubject?->teacher)
                                · set by {{ $assignment->classSubject->teacher->full_name }}
                            @endif
                        </p>
                    </div>
                    @include('student.partials.status-pill', ['status' => $status])
                </div>

                <div class="mt-space-md flex flex-wrap gap-space-lg text-body-sm">
                    <div>
                        <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Due</p>
                        <p @class([
                            'font-medium',
                            'text-error' => $assignment->isPastDue() && $status !== 'Graded' && $status !== 'Submitted',
                            'text-on-surface' => ! ($assignment->isPastDue() && $status !== 'Graded' && $status !== 'Submitted'),
                        ])>
                            {{ $assignment->due_at->format('D, j M Y · H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Marks</p>
                        <p class="font-medium text-on-surface">{{ rtrim(rtrim(number_format((float) $assignment->max_score, 1), '0'), '.') }}</p>
                    </div>
                    @if ($assignment->term)
                        <div>
                            <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Term</p>
                            <p class="font-medium text-on-surface">{{ $assignment->term->name }}</p>
                        </div>
                    @endif
                </div>
            </header>

            <div class="px-space-md py-space-md">
                <h3 class="text-label-form uppercase tracking-wider text-on-surface-variant mb-2">Instructions</h3>
                @if (filled($assignment->instructions))
                    <div class="prose-sm text-body-md text-on-surface whitespace-pre-line leading-relaxed">{{ $assignment->instructions }}</div>
                @else
                    <p class="text-body-md text-on-surface-variant italic">No instructions were provided.</p>
                @endif
            </div>
        </section>

        {{-- Submission --}}
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden self-start">
            <header class="px-space-md py-space-sm border-b border-outline-variant/50">
                <h2 class="text-headline-sm font-headline-sm text-on-surface">My Submission</h2>
            </header>

            @if ($submission?->graded_at)
                {{-- Graded: read-only, shows the mark and feedback --}}
                <div class="px-space-md py-space-md space-y-space-md">
                    <div class="rounded-xl bg-success-container px-space-md py-space-sm text-center">
                        <p class="text-label-sm uppercase tracking-wider text-success">Your Mark</p>
                        <p class="text-display-lg font-display-lg text-success">
                            {{ rtrim(rtrim(number_format((float) $submission->score, 1), '0'), '.') }}<span class="text-headline-sm">/{{ rtrim(rtrim(number_format((float) $assignment->max_score, 1), '0'), '.') }}</span>
                        </p>
                        @if ($submission->percentage !== null)
                            <p class="text-body-sm text-success">{{ $submission->percentage }}%</p>
                        @endif
                    </div>

                    @if (filled($submission->feedback))
                        <div>
                            <h3 class="text-label-form uppercase tracking-wider text-on-surface-variant mb-1">Teacher Feedback</h3>
                            <p class="text-body-md text-on-surface whitespace-pre-line">{{ $submission->feedback }}</p>
                        </div>
                    @endif

                    @include('student.assignments._submitted-files', ['submission' => $submission])

                    <p class="text-body-sm text-on-surface-variant border-t border-outline-variant/40 pt-3">
                        Marked {{ $submission->graded_at->diffForHumans() }}@if($submission->gradedByTeacher) by {{ $submission->gradedByTeacher->full_name }}@endif.
                        Graded work can no longer be changed.
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('student.assignments.submit', $assignment->assignment_id) }}"
                      enctype="multipart/form-data" class="px-space-md py-space-md space-y-space-md">
                    @csrf

                    @if ($submission?->submitted_at)
                        <div class="rounded-xl bg-secondary-container/50 px-3 py-2.5 text-body-sm text-on-secondary-container flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm mt-0.5">check_circle</span>
                            <span>
                                Submitted {{ $submission->submitted_at->diffForHumans() }}@if($submission->isLate()) <strong>(late)</strong>@endif.
                                You can replace it until it is marked.
                            </span>
                        </div>

                        @include('student.assignments._submitted-files', ['submission' => $submission])
                    @elseif ($assignment->isPastDue())
                        <div class="rounded-xl bg-error-container/70 px-3 py-2.5 text-body-sm text-on-error-container flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm mt-0.5">warning</span>
                            <span>The deadline has passed. You can still submit, but it will be flagged as late.</span>
                        </div>
                    @endif

                    <div>
                        <label for="notes" class="block text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">
                            Notes {{ $assignment->allows_file_upload ? '(optional)' : '' }}
                        </label>
                        <textarea name="notes" id="notes" rows="5"
                            class="w-full rounded-xl border-outline-variant/60 bg-surface-container-lowest text-body-md focus:border-secondary focus:ring-secondary/40"
                            placeholder="Anything you want your teacher to know…">{{ old('notes', $submission?->notes) }}</textarea>
                    </div>

                    @if ($assignment->allows_file_upload)
                        <div>
                            <label for="file" class="block text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">
                                Attach a file
                            </label>
                            <input type="file" name="file" id="file"
                                class="w-full text-body-sm text-on-surface-variant file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-label-md file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer">
                            <p class="mt-1.5 text-label-sm text-outline">PDF, DOCX, TXT, ZIP, PNG, JPG, PY, CSV, XLSX, PPTX · max 10&nbsp;MB</p>
                        </div>
                    @endif

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-on-primary text-label-md font-semibold hover:bg-on-primary-fixed-variant transition-colors">
                        <span class="material-symbols-outlined text-lg">upload</span>
                        {{ $submission?->submitted_at ? 'Replace submission' : 'Submit assignment' }}
                    </button>
                </form>
            @endif
        </section>
    </div>
@endsection
