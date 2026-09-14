@extends('layouts.teacher')

@section('title', 'Submissions – Teacher Portal')

@section('page')
    <a href="{{ route('teacher.assignments.index') }}"
       class="inline-flex items-center gap-1.5 mb-4 font-label-md text-label-md text-primary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> All assignments
    </a>

    <div class="mb-6">
        <h1 class="font-headline-md text-headline-md text-primary font-bold">{{ $assignment->title }}</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            {{ $assignment->classSubject?->schoolClass?->class_name }} ·
            {{ $assignment->classSubject?->subject?->subject_name }} ·
            due {{ $assignment->due_at->format('j M Y, H:i') }} ·
            out of {{ rtrim(rtrim(number_format((float) $assignment->max_score, 1), '0'), '.') }}
        </p>
    </div>



    {{-- Submitted --}}
    <section class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden mb-6">
        <header class="px-4 py-3 border-b border-outline-variant/50">
            <h2 class="font-headline-sm text-headline-sm text-on-surface">
                Handed in ({{ $assignment->submissions->count() }})
            </h2>
        </header>

        @if ($assignment->submissions->isEmpty())
            <p class="px-4 py-8 text-center font-body-md text-body-md text-on-surface-variant">Nothing handed in yet.</p>
        @else
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($assignment->submissions->sortBy(fn ($s) => $s->student?->last_name) as $submission)
                    <li class="px-4 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-title-sm text-title-sm text-on-surface">
                                    {{ $submission->student?->full_name ?? 'Unknown student' }}
                                    @if ($submission->isLate())
                                        <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-lg bg-error-container text-error font-label-sm text-label-sm">Late</span>
                                    @endif
                                </p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                    Submitted {{ $submission->submitted_at?->format('j M Y, H:i') ?? '—' }}
                                    @if ($submission->graded_at)
                                        · marked {{ $submission->graded_at->format('j M Y') }}
                                    @endif
                                </p>

                                @if ($submission->file_path)
                                    <a href="{{ Storage::disk('public')->url($submission->file_path) }}" target="_blank" rel="noopener"
                                       class="mt-2 inline-flex items-center gap-2 font-body-sm text-body-sm text-primary hover:underline">
                                        <span class="material-symbols-outlined text-[18px]">draft</span>
                                        {{ $submission->original_filename ?? basename($submission->file_path) }}
                                    </a>
                                @endif

                                @if (filled($submission->notes))
                                    <p class="mt-2 rounded-lg bg-surface-container-low px-3 py-2 font-body-sm text-body-sm text-on-surface whitespace-pre-line max-w-2xl">{{ $submission->notes }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('teacher.assignments.grade', [$assignment->assignment_id, $submission->submission_id]) }}"
                                  class="flex flex-wrap items-end gap-2 shrink-0">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1">Score</label>
                                    <input type="number" name="score" step="0.5" min="0" max="{{ $assignment->max_score }}" required
                                        value="{{ old('score', $submission->score) }}"
                                        class="w-24 rounded-lg border-outline-variant/60 bg-surface-container-lowest font-data-mono text-data-mono focus:border-secondary focus:ring-secondary/40">
                                </div>
                                <div>
                                    <label class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1">Feedback</label>
                                    <input type="text" name="feedback" value="{{ old('feedback', $submission->feedback) }}"
                                        placeholder="Optional comment"
                                        class="w-56 rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                                </div>
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm transition-all">
                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                    {{ $submission->graded_at ? 'Update' : 'Mark' }}
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Not handed in --}}
    <section class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
        <header class="px-4 py-3 border-b border-outline-variant/50">
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Not handed in ({{ $missing->count() }})</h2>
        </header>

        @if ($missing->isEmpty())
            <p class="px-4 py-6 text-center font-body-md text-body-md text-on-surface-variant">Everyone in the class has submitted.</p>
        @else
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($missing as $student)
                    <li class="px-4 py-3 flex items-center justify-between gap-3">
                        <span class="font-body-md text-body-md text-on-surface">{{ $student->full_name }}</span>
                        <span class="font-data-mono text-code-md text-outline">{{ $student->student_number }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
