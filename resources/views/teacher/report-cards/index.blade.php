@extends('layouts.teacher')

@section('title', 'Report Cards – Teacher Portal')

@section('page')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-md text-headline-md text-primary font-bold">Report Cards</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Finalize a term to assign class positions and lock its marks.
            </p>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <label for="term_id" class="font-label-md text-label-md text-on-surface-variant">Term</label>
            <select name="term_id" id="term_id" onchange="this.form.submit()"
                class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                @foreach ($terms as $option)
                    <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                        {{ $option->name }} ({{ $option->academicYear?->label }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if (session('notification'))
        <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
            {{ session('notification') }}
        </div>
    @endif

    @if ($classes->isEmpty())
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-16 text-center">
            <span class="material-symbols-outlined text-[40px] text-outline">school</span>
            <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">You are not a class teacher</p>
            <p class="mt-1 font-body-md text-body-md text-on-surface-variant max-w-md mx-auto">
                Only the homeroom teacher of a class can finalize its report cards. You can still
                write subject remarks from Enter Marks.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($classes as $row)
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant/50 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">
                                {{ $row['class']->class_name }}
                            </h2>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                {{ $row['class']->gradeLevel?->name ?? '—' }} ·
                                {{ $row['class']->students_count }} student{{ $row['class']->students_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                        <span @class([
                            'px-2.5 py-1 rounded-lg font-label-sm text-label-sm font-semibold shrink-0',
                            'bg-green-50 text-green-700' => $row['is_finalized'],
                            'bg-tertiary-fixed text-on-surface-variant' => ! $row['is_finalized'],
                        ])>{{ $row['is_finalized'] ? 'Finalized' : 'Draft' }}</span>
                    </div>

                    <div class="px-5 py-4">
                        @if ($row['missing_count'] > 0)
                            <div class="rounded-lg bg-error-container px-3 py-2.5 mb-3">
                                <p class="font-body-sm text-body-sm text-on-error-container font-semibold">
                                    {{ $row['missing_count'] }} student(s) missing marks
                                </p>
                                <ul class="mt-1.5 space-y-0.5">
                                    @foreach ($row['missing']->take(3) as $miss)
                                        <li class="font-body-sm text-body-sm text-on-error-container">
                                            {{ $miss['student']->full_name }} — {{ implode(', ', array_slice($miss['missing'], 0, 3)) }}@if (count($miss['missing']) > 3) +{{ count($miss['missing']) - 3 }} more @endif
                                        </li>
                                    @endforeach
                                    @if ($row['missing']->count() > 3)
                                        <li class="font-body-sm text-body-sm text-on-error-container">
                                            and {{ $row['missing']->count() - 3 }} more…
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @elseif (! $row['is_finalized'])
                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">
                                All marks are in. Finalizing assigns class positions and locks this term.
                            </p>
                        @else
                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">
                                {{ $row['finalized'] }} report card(s) finalized. An administrator can unfinalize
                                if a correction is needed.
                            </p>
                        @endif

                        <a href="{{ route('teacher.report-cards.show', $row['class']->class_id) }}?term_id={{ $term?->term_id }}"
                            class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                            <span class="material-symbols-outlined text-[18px]">fact_check</span>
                            <span>Open class</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
