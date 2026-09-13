@extends('layouts.student')

@section('title', 'Assignments')
@section('page-title', 'My Assignments')
@section('page-subtitle', $student->schoolClass?->class_name ? 'Class ' . $student->schoolClass->class_name : 'No class assigned')

@section('content')
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-space-sm mb-space-lg">
        @include('student.partials.stat-card', ['icon' => 'pending_actions', 'label' => 'Pending',   'value' => $counts['pending'],   'tone' => 'warning'])
        @include('student.partials.stat-card', ['icon' => 'assignment_late', 'label' => 'Overdue',   'value' => $counts['overdue'],   'tone' => 'error'])
        @include('student.partials.stat-card', ['icon' => 'upload_file',     'label' => 'Submitted', 'value' => $counts['submitted'], 'tone' => 'primary'])
        @include('student.partials.stat-card', ['icon' => 'task_alt',        'label' => 'Graded',    'value' => $counts['graded'],    'tone' => 'success'])
    </div>

    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
        <header class="flex flex-wrap items-center justify-between gap-3 px-space-md py-space-sm border-b border-outline-variant/50">
            <h2 class="text-headline-sm font-headline-sm text-on-surface">All Assignments</h2>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <select name="term_id" onchange="this.form.submit()"
                    class="rounded-xl border-outline-variant/60 bg-surface-container-lowest text-body-md focus:border-secondary focus:ring-secondary/40">
                    <option value="">All terms</option>
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                            {{ $option->name }}
                        </option>
                    @endforeach
                </select>

                <select name="subject_id" onchange="this.form.submit()"
                    class="rounded-xl border-outline-variant/60 bg-surface-container-lowest text-body-md focus:border-secondary focus:ring-secondary/40">
                    <option value="">All subjects</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->subject_id }}" @selected(request('subject_id') == $subject->subject_id)>
                            {{ $subject->subject_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </header>

        @if ($assignments->isEmpty())
            @include('student.partials.empty-state', [
                'icon'    => 'assignment',
                'title'   => 'No assignments match the selected filters',
                'message' => 'Try clearing the term or subject filter, or check back after your teachers set work.',
            ])
        @else
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($assignments as $assignment)
                    @php
                        $status = $assignment->student_status;
                        $dueSoon = $status === 'Pending' && $assignment->due_at->diffInDays(now()) <= 2;
                    @endphp
                    <li>
                        <a href="{{ route('student.assignments.show', $assignment->assignment_id) }}"
                           class="flex items-start gap-space-md px-space-md py-space-sm hover:bg-surface-container-low/60 transition-colors">
                            <div @class([
                                'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                                'bg-error-container text-error' => $status === 'Overdue',
                                'bg-success-container text-success' => $status === 'Graded',
                                'bg-primary/10 text-primary' => ! in_array($status, ['Overdue', 'Graded']),
                            ])>
                                <span class="material-symbols-outlined text-xl">
                                    {{ $status === 'Graded' ? 'task_alt' : ($status === 'Overdue' ? 'assignment_late' : 'assignment') }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-on-surface truncate">{{ $assignment->title }}</p>
                                <p class="text-body-sm text-on-surface-variant truncate">
                                    {{ $assignment->classSubject?->subject?->subject_name ?? 'Subject' }}
                                    @if ($assignment->classSubject?->teacher)
                                        · {{ $assignment->classSubject->teacher->full_name }}
                                    @endif
                                </p>
                                <p @class([
                                    'mt-1 text-body-sm flex items-center gap-1.5',
                                    'text-error font-medium' => $status === 'Overdue' || $dueSoon,
                                    'text-on-surface-variant' => ! ($status === 'Overdue' || $dueSoon),
                                ])>
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    Due {{ $assignment->due_at->format('D, j M Y · H:i') }}
                                    <span class="text-outline">({{ $assignment->due_at->diffForHumans() }})</span>
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-2 shrink-0">
                                @include('student.partials.status-pill', ['status' => $status])
                                @if ($assignment->student_submission?->score !== null)
                                    <span class="font-data-mono text-data-mono font-semibold text-on-surface">
                                        {{ rtrim(rtrim(number_format((float) $assignment->student_submission->score, 1), '0'), '.') }}/{{ rtrim(rtrim(number_format((float) $assignment->max_score, 1), '0'), '.') }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
