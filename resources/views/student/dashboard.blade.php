@extends('layouts.student')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . $student->first_name)

@section('content')
    {{-- KPI row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md mb-space-lg">
        @include('student.partials.stat-card', [
            'icon'  => 'grade',
            'label' => 'Term Average',
            'value' => $termAverage !== null ? $termAverage . '%' : '—',
            'meta'  => $termAverage === null ? 'No marks recorded yet' : 'Across all subjects',
            'tone'  => 'primary',
        ])
        @include('student.partials.stat-card', [
            'icon'  => 'how_to_reg',
            'label' => 'Attendance',
            'value' => $attendance['rate'] !== null ? $attendance['rate'] . '%' : '—',
            'meta'  => $attendance['total'] . ' days recorded',
            'tone'  => ($attendance['rate'] ?? 100) >= 90 ? 'success' : 'warning',
        ])
        @include('student.partials.stat-card', [
            'icon'  => 'assignment',
            'label' => 'Assignments Due',
            'value' => $upcoming->whereNull('student_submission')->count() ?: $upcoming->count(),
            'meta'  => 'Next 4 upcoming shown below',
            'tone'  => 'warning',
        ])
        @include('student.partials.stat-card', [
            'icon'  => 'payments',
            'label' => 'Fee Balance',
            'value' => 'K' . number_format($feeBalance, 2),
            'meta'  => $feeStatus,
            'tone'  => $feeBalance > 0 ? 'error' : 'success',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-md">
        {{-- Recent results --}}
        <section class="lg:col-span-2 bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
            <header class="flex items-center justify-between gap-3 px-space-md py-space-sm border-b border-outline-variant/50">
                <h2 class="text-headline-sm font-headline-sm text-on-surface">Recent Results</h2>
                <a href="{{ route('student.results') }}" class="text-label-md font-medium text-primary hover:underline flex items-center gap-1">
                    View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </header>

            @if ($results->isEmpty())
                @include('student.partials.empty-state', [
                    'icon'    => 'grade',
                    'title'   => 'No results yet',
                    'message' => 'Marks appear here as your teachers record them.',
                ])
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-body-md">
                        <thead class="bg-surface-container-low text-label-sm uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="text-left font-medium px-space-md py-2.5">Subject</th>
                                <th class="text-left font-medium px-space-md py-2.5">Type</th>
                                <th class="text-right font-medium px-space-md py-2.5">Score</th>
                                <th class="text-right font-medium px-space-md py-2.5">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach ($results as $grade)
                                <tr class="hover:bg-surface-container-low/60 transition-colors">
                                    <td class="px-space-md py-3 font-medium text-on-surface">
                                        {{ $grade->classSubject?->subject?->subject_name ?? 'Unassigned' }}
                                    </td>
                                    <td class="px-space-md py-3 text-on-surface-variant">{{ $grade->assessment_type }}</td>
                                    <td class="px-space-md py-3 text-right font-data-mono text-data-mono">
                                        {{ rtrim(rtrim(number_format((float) $grade->score, 1), '0'), '.') }}/{{ rtrim(rtrim(number_format((float) $grade->max_score, 1), '0'), '.') }}
                                    </td>
                                    <td class="px-space-md py-3 text-right">
                                        <span class="font-semibold {{ $grade->percentage >= 50 ? 'text-success' : 'text-error' }}">
                                            {{ $grade->letter_grade }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Today's lessons --}}
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
            <header class="flex items-center justify-between gap-3 px-space-md py-space-sm border-b border-outline-variant/50">
                <h2 class="text-headline-sm font-headline-sm text-on-surface">Today</h2>
                <span class="text-label-sm text-on-surface-variant">{{ now()->format('D, j M') }}</span>
            </header>

            @if ($todaySlots->isEmpty())
                @include('student.partials.empty-state', [
                    'icon'    => 'event_available',
                    'title'   => 'Nothing scheduled',
                    'message' => 'No lessons on the timetable for today.',
                ])
            @else
                <ul class="divide-y divide-outline-variant/40">
                    @foreach ($todaySlots as $slot)
                        <li class="px-space-md py-3 flex items-start gap-3">
                            <div class="text-center shrink-0 w-14">
                                <p class="text-code-md font-data-mono font-semibold text-primary">
                                    {{ $slot->period?->start_time?->format('H:i') ?? '—' }}
                                </p>
                                <p class="text-code-sm font-data-mono text-outline">
                                    {{ $slot->period?->end_time?->format('H:i') }}
                                </p>
                            </div>
                            <div class="min-w-0 flex-1">
                                @if ($slot->period?->is_break)
                                    <p class="font-medium text-on-surface-variant">{{ $slot->period->name }}</p>
                                @else
                                    <p class="font-medium text-on-surface truncate">{{ $slot->subject?->subject_name ?? 'Unassigned' }}</p>
                                    <p class="text-body-sm text-on-surface-variant truncate">{{ $slot->teacher?->full_name ?? 'Teacher unassigned' }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- Upcoming assignments --}}
    <section class="mt-space-md bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
        <header class="flex items-center justify-between gap-3 px-space-md py-space-sm border-b border-outline-variant/50">
            <h2 class="text-headline-sm font-headline-sm text-on-surface">Upcoming Assignments</h2>
            <a href="{{ route('student.assignments.index') }}" class="text-label-md font-medium text-primary hover:underline flex items-center gap-1">
                View all <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </header>

        @if ($upcoming->isEmpty())
            @include('student.partials.empty-state', [
                'icon'    => 'assignment_turned_in',
                'title'   => 'Nothing due',
                'message' => 'You have no assignments with an upcoming deadline.',
            ])
        @else
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($upcoming as $assignment)
                    @php $submission = $assignment->submissions->first(); @endphp
                    <li>
                        <a href="{{ route('student.assignments.show', $assignment->assignment_id) }}"
                           class="flex items-center gap-space-md px-space-md py-3 hover:bg-surface-container-low/60 transition-colors">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-xl">assignment</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-on-surface truncate">{{ $assignment->title }}</p>
                                <p class="text-body-sm text-on-surface-variant truncate">
                                    {{ $assignment->classSubject?->subject?->subject_name }} · due {{ $assignment->due_at->format('D, j M H:i') }}
                                </p>
                            </div>
                            @include('student.partials.status-pill', ['status' => $assignment->statusForStudent($submission)])
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
