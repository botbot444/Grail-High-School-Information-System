@extends('layouts.teacher')

@section('title', $schoolClass->class_name . ' Report Cards – Teacher Portal')

@section('page')
    <a href="{{ route('teacher.report-cards.index') }}?term_id={{ $term->term_id }}"
       class="inline-flex items-center gap-1.5 mb-4 font-label-md text-label-md text-primary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> All classes
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-md text-headline-md text-primary font-bold">{{ $schoolClass->class_name }}</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                {{ $term->name }} @if ($term->academicYear) · {{ $term->academicYear->label }} @endif ·
                {{ $rows->count() }} student{{ $rows->count() === 1 ? '' : 's' }}
                @if ($isFinalized)
                    · <span class="text-green-700 font-semibold">Finalized</span>
                @endif
            </p>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <select name="term_id" onchange="this.form.submit()"
                class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                @foreach ($terms as $option)
                    <option value="{{ $option->term_id }}" @selected($term->term_id === $option->term_id)>
                        {{ $option->name }} ({{ $option->academicYear?->label }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>



    {{-- Finalize control --}}
    <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-5 mb-6">
        @if ($isFinalized)
            <div class="flex flex-wrap items-center gap-3">
                <span class="material-symbols-outlined text-green-700">lock</span>
                <div class="min-w-0 flex-1">
                    <p class="font-title-sm text-title-sm text-on-surface">This term is finalized</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        Positions are assigned and marks are locked. Ask an administrator to unfinalize
                        if a correction is needed. Comments can still be edited.
                    </p>
                </div>
            </div>
        @elseif ($missing->isNotEmpty())
            <div class="flex flex-wrap items-start gap-3">
                <span class="material-symbols-outlined text-error">error</span>
                <div class="min-w-0 flex-1">
                    <p class="font-title-sm text-title-sm text-on-surface">
                        {{ $missing->count() }} student(s) are missing marks
                    </p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        Every student needs a mark in every subject their class takes before the term can
                        be finalized — otherwise positions would be computed from incomplete work.
                    </p>
                    <ul class="mt-2 space-y-0.5">
                        @foreach ($missing as $miss)
                            <li class="font-body-sm text-body-sm text-on-surface-variant">
                                <strong>{{ $miss['student']->full_name }}</strong> — {{ implode(', ', $miss['missing']) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('teacher.report-cards.finalize', $schoolClass->class_id) }}"
                  onsubmit="return confirm('Finalize {{ $schoolClass->class_name }} for {{ $term->name }}? Positions will be assigned and marks locked.');"
                  class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="hidden" name="term_id" value="{{ $term->term_id }}">
                <span class="material-symbols-outlined text-secondary">task_alt</span>
                <div class="min-w-0 flex-1">
                    <p class="font-title-sm text-title-sm text-on-surface">All marks are in</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        Finalizing computes each student's term average, assigns class positions
                        (ties share a position), and locks the marks.
                    </p>
                </div>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[18px]">lock</span>
                    <span>Finalize class grades</span>
                </button>
            </form>
        @endif
    </div>

    {{-- Students --}}
    <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant/50">
            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Students</h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                Write the overall class-teacher comment here; subject remarks come from each subject teacher.
            </p>
        </div>

        <ul class="divide-y divide-outline-variant/40">
            @foreach ($rows as $row)
                <li class="px-5 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-title-sm text-title-sm text-on-surface">{{ $row['student']->full_name }}</p>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                {{ $row['student']->student_number }}
                                · average {{ $row['average'] !== null ? round($row['average'], 1) . '%' : '—' }}
                                @if ($row['card']?->rank_label)
                                    · position <strong>{{ $row['card']->rank_label }}</strong>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('teacher.report-cards.preview', [$schoolClass->class_id, $row['student']->student_id]) }}?term_id={{ $term->term_id }}"
                               target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container hover:text-primary font-label-md text-label-md transition-colors">
                                <span class="material-symbols-outlined text-[18px]">visibility</span> Preview
                            </a>
                            <a href="{{ route('teacher.report-cards.preview', [$schoolClass->class_id, $row['student']->student_id]) }}?term_id={{ $term->term_id }}&download=1"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-primary text-on-primary hover:bg-primary/90 font-label-md text-label-md font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[18px]">download</span> PDF
                            </a>
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ route('teacher.report-cards.comment', [$schoolClass->class_id, $row['student']->student_id]) }}"
                          class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $term->term_id }}">
                        <div class="flex-1 min-w-[260px]">
                            <label class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1">
                                Class teacher's comment
                            </label>
                            <textarea name="comment" rows="2"
                                class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40"
                                placeholder="Overall remark for the term…">{{ $row['card']?->class_teacher_comment }}</textarea>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm transition-all">
                            <span class="material-symbols-outlined text-[18px]">save</span> Save
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
