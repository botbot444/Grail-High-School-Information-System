@extends('layouts.teacher')

<<<<<<< HEAD
@section('title', 'Class Performance')

@section('page')
    <div class="flex flex-col gap-space-lg">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-space-md">
            <div>
                <p class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">Teacher Portal /
                    Performance</p>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Class Performance</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ $selectedAssignment?->schoolClass?->display_name ?? 'Select an assigned class' }}
                    @if ($term)
                        · {{ $term->name }} {{ $term->academicYear?->label ? '· ' . $term->academicYear->label : '' }}
                    @endif
                </p>
            </div>
            <form method="GET" action="{{ route('teacher.performance') }}" class="flex flex-wrap gap-2">
                <select name="assignment_id" onchange="this.form.submit()"
                    class="h-10 rounded-lg border-0 bg-surface-container-low px-3 text-label-md">
                    @forelse ($assignments as $assignment)
                        <option value="{{ $assignment->class_subject_id }}" @selected($selectedAssignment?->class_subject_id === $assignment->class_subject_id)>
                            {{ $assignment->schoolClass?->display_name }} · {{ $assignment->subject?->subject_name }}
                        </option>
                    @empty
                        <option>No assigned subjects</option>
                    @endforelse
                </select>
                <select name="term_id" onchange="this.form.submit()"
                    class="h-10 rounded-lg border-0 bg-surface-container-low px-3 text-label-md">
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>{{ $option->name }}
                            ({{ $option->academicYear?->label }})</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-error-container px-4 py-3 text-error">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!$selectedAssignment || !$term)
            <div class="rounded-xl bg-surface-container-lowest p-space-xl text-center shadow-sm">
                <span class="material-symbols-outlined text-[44px] text-outline">query_stats</span>
                <h2 class="font-title-lg text-title-lg text-on-surface">No performance data available</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Assign a class subject and create a term to
                    view performance.</p>
            </div>
        @else
            <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-space-md">
                <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span
                        class="font-label-sm text-label-sm uppercase text-outline">Class Average</span><strong
                        class="mt-2 block font-headline-md text-headline-md text-on-surface">{{ number_format($kpis['average'], 1) }}%</strong><span
                        class="font-body-sm text-body-sm text-on-surface-variant">{{ $subjects->count() }} subjects</span>
                </div>
                <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span
                        class="font-label-sm text-label-sm uppercase text-outline">Pass Rate</span><strong
                        class="mt-2 block font-headline-md text-headline-md text-on-surface">{{ $kpis['graded'] ? number_format(($kpis['passed'] / $kpis['graded']) * 100, 1) : '0.0' }}%</strong><span
                        class="font-body-sm text-body-sm text-on-surface-variant">{{ $kpis['passed'] }}/{{ $kpis['graded'] }}
                        students</span></div>
                <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span
                        class="font-label-sm text-label-sm uppercase text-outline">Top Student</span><strong
                        class="mt-2 block truncate font-title-md text-title-md text-on-surface">{{ $kpis['top']['student']->full_name ?? '—' }}</strong><span
                        class="font-body-sm text-body-sm text-on-surface-variant">{{ $kpis['top']['average'] ?? '—' }}%</span>
                </div>
                <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span
                        class="font-label-sm text-label-sm uppercase text-outline">Support Needed</span><strong
                        class="mt-2 block truncate font-title-md text-title-md text-on-surface">{{ $kpis['lowest']['student']->full_name ?? '—' }}</strong><span
                        class="font-body-sm text-body-sm text-on-surface-variant">{{ $kpis['lowest']['average'] ?? '—' }}%</span>
                </div>
                <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span
                        class="font-label-sm text-label-sm uppercase text-outline">Attendance</span><strong
                        class="mt-2 block font-headline-md text-headline-md text-on-surface">{{ number_format($attendanceRate ?? 0, 1) }}%</strong><span
                        class="font-body-sm text-body-sm text-on-surface-variant">Excused marks excluded</span></div>
            </section>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-space-md">
                <div class="rounded-xl bg-surface-container-lowest p-space-lg shadow-sm">
                    <h2 class="font-title-md text-title-md text-on-surface">Subject Averages</h2>
                    <div class="mt-5 flex flex-col gap-4">
                        @forelse ($subjects as $subject)
                            <div>
                                <div class="mb-1 flex justify-between font-label-md text-label-md">
                                    <span>{{ $subject['name'] }}</span><span>{{ number_format($subject['average'], 1) }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-surface-container-high">
                                    <div class="h-2 rounded-full bg-secondary"
                                        style="width: {{ min(100, max(0, $subject['average'])) }}%"></div>
                                </div>
                            </div>
                        @empty <p class="text-on-surface-variant">No exam grades recorded for this term.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-xl bg-surface-container-lowest p-space-lg shadow-sm">
                    <h2 class="font-title-md text-title-md text-on-surface">Grade Distribution</h2>
                    <div class="mt-5 grid grid-cols-5 gap-2 text-center">
                        @foreach ($distribution as $letter => $count)
                            <div>
                                <div class="mx-auto flex h-20 items-end justify-center">
                                    <div class="w-10 rounded-t bg-primary-container"
                                        style="height: {{ $kpis['graded'] ? max(4, ($count / $kpis['graded']) * 80) : 4 }}px">
                                    </div>
                                </div><strong class="font-title-md text-title-md">{{ $count }}</strong>
                                <div class="font-label-sm text-label-sm text-outline">{{ $letter }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-surface-container-lowest shadow-sm">
                <div
                    class="flex flex-col gap-3 border-b border-outline-variant p-space-lg lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="font-title-md text-title-md text-on-surface">Performance Roster</h2>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Exam percentages for subjects taught in
                            this class.</p>
                    </div><input id="performanceSearch" type="search" placeholder="Search students"
                        class="h-9 rounded-lg border-0 bg-surface-container-low px-3 text-label-md">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-outline-variant font-label-sm text-label-sm uppercase text-outline">
                                <th class="px-4 py-3">Rank</th>
                                <th class="px-4 py-3">Student</th>
                                @foreach ($subjects as $subject)
                                    <th class="px-4 py-3">{{ $subject['name'] }}</th>
                                @endforeach
                                <th class="px-4 py-3">
                                    Average</th>
                                <th class="px-4 py-3">Grade</th>
                            </tr>
                        </thead>
                        <tbody id="performanceRows">
                            @forelse ($rankings as $row)
                                <tr class="performance-row border-b border-outline-variant last:border-0"
                                    data-name="{{ strtolower($row['student']->full_name) }}">
                                    <td class="px-4 py-3">{{ $row['rank'] ?? '—' }}</td>
                                    <td class="px-4 py-3 font-title-sm text-title-sm">{{ $row['student']->full_name }}</td>
                                    @foreach ($subjects as $subject)
                                        <td class="px-4 py-3">
                                            {{ $row['scores'][$subject['name']] !== null ? number_format($row['scores'][$subject['name']], 1) . '%' : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 font-semibold">
                                        {{ $row['average'] !== null ? number_format($row['average'], 1) . '%' : '—' }}</td>
                                    <td class="px-4 py-3">{{ $row['letter'] ?? '—' }}</td>
                            </tr>@empty<tr>
                                    <td colspan="{{ 5 + $subjects->count() }}"
                                        class="px-4 py-8 text-center text-on-surface-variant">No students or grades found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section
                class="flex flex-col gap-3 rounded-xl bg-primary-container p-space-lg text-on-primary lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="font-title-md text-title-md">
                        {{ $isFinalized ? 'Grades finalized' : 'Grades awaiting finalization' }}</h2>
                    <p class="font-body-sm text-body-sm text-on-primary-container">
                        {{ $isFinalized ? 'Marks are locked for this class and term.' : 'Finalization calculates report-card averages and ranks.' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($canFinalize && !$isFinalized)
                        <form method="POST" action="{{ route('teacher.performance.finalize') }}"
                            onsubmit="return confirm('Finalize all grades for this class and term?')">@csrf<input
                                type="hidden" name="assignment_id"
                                value="{{ $selectedAssignment->class_subject_id }}"><input type="hidden" name="term_id"
                                value="{{ $term->term_id }}"><button
                                class="rounded-lg bg-secondary px-4 py-2 font-title-sm text-title-sm text-on-primary">Finalize
                                Grades</button></form>
                    @elseif ($isFinalized)
                        <form method="POST" action="{{ route('teacher.performance.unfinalize-request') }}"
                            onsubmit="return requestUnfinalize(this)">@csrf<input type="hidden" name="assignment_id"
                                value="{{ $selectedAssignment->class_subject_id }}"><input type="hidden" name="term_id"
                                value="{{ $term->term_id }}"><input type="hidden" name="reason"
                                id="unfinalizeReason"><button
                                class="rounded-lg bg-surface-container-lowest px-4 py-2 font-title-sm text-on-surface">Request
                                Unlock</button></form>
                    @endif
                </div>
            </section>
        @endif
    </div>
    <script>
        const search = document.getElementById('performanceSearch');
        search?.addEventListener('input', event => document.querySelectorAll('.performance-row').forEach(row => row
            .classList.toggle('hidden', !row.dataset.name.includes(event.target.value.toLowerCase()))));

        function requestUnfinalize(form) {
            const reason = window.prompt('Why should an administrator unlock these grades?');
            if (!reason || reason.length < 5) return false;
            form.querySelector('#unfinalizeReason').value = reason;
            return true;
        }
    </script>
=======
@section('title', 'Class Performance – Teacher Portal')

@section('page')
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-md text-headline-md text-primary font-bold">Class Performance</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                How a class is doing this term, by subject and over time.
            </p>
        </div>

        @if ($classes->isNotEmpty())
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <select name="class_id" onchange="this.form.submit()"
                    class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                    @foreach ($classes as $option)
                        <option value="{{ $option->class_id }}" @selected($schoolClass?->class_id === $option->class_id)>
                            {{ $option->class_name }}@if ($option->gradeLevel) — {{ $option->gradeLevel->name }}@endif
                        </option>
                    @endforeach
                </select>
                <select name="term_id" onchange="this.form.submit()"
                    class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                            {{ $option->name }} ({{ $option->academicYear?->label }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if ($classes->isEmpty())
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-16 text-center">
            <span class="material-symbols-outlined text-[40px] text-outline">query_stats</span>
            <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">No classes assigned</p>
            <p class="mt-1 font-body-md text-body-md text-on-surface-variant max-w-md mx-auto">
                You are not the class teacher for any class, and no subjects are assigned to you yet.
            </p>
        </div>
    @elseif (! $report)
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-16 text-center">
            <span class="material-symbols-outlined text-[40px] text-outline">calendar_month</span>
            <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">No term selected</p>
            <p class="mt-1 font-body-md text-body-md text-on-surface-variant">Create an academic term to see performance.</p>
        </div>
    @else
        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            @foreach ([
                ['Class average', $report['summary']['average'] !== null ? $report['summary']['average'] . '%' : '—'],
                ['Pass rate', $report['summary']['pass_rate'] !== null ? $report['summary']['pass_rate'] . '%' : '—'],
                ['Highest', $report['summary']['highest'] !== null ? $report['summary']['highest'] . '%' : '—'],
                ['Lowest', $report['summary']['lowest'] !== null ? $report['summary']['lowest'] . '%' : '—'],
                ['Marked', $report['summary']['marked'] . ' of ' . $report['summary']['students']],
            ] as [$label, $value])
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest p-4">
                    <p class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">{{ $label }}</p>
                    <p class="mt-1 font-headline-md text-headline-md font-extrabold text-on-surface">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            {{-- Distribution --}}
            <section class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant/50">
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Score Distribution</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Students per band, this term</p>
                </div>
                <div class="p-5 space-y-3">
                    @php $maxBand = max(1, $report['distribution']->max()); @endphp
                    @foreach ($report['distribution'] as $band => $count)
                        <div>
                            <div class="flex items-baseline justify-between mb-1">
                                <span class="font-body-md text-body-md text-on-surface">{{ $band }}%</span>
                                <span class="font-data-mono text-data-mono text-on-surface-variant">{{ $count }}</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-surface-container overflow-hidden">
                                <div class="h-full rounded-full {{ $band === 'Under 40' ? 'bg-error' : ($band === '40–49' ? 'bg-tertiary' : 'bg-secondary') }}"
                                     style="width: {{ round(($count / $maxBand) * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Trend --}}
            <section class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant/50">
                    <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Trend Across Terms</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Class average per term</p>
                </div>
                <div class="p-5">
                    @if ($report['trend']->count() < 2)
                        <p class="font-body-md text-body-md text-on-surface-variant py-6 text-center">
                            Needs marks in at least two terms before a trend can be drawn.
                        </p>
                    @else
                        <div class="flex items-end gap-3 h-40">
                            @foreach ($report['trend'] as $point)
                                <div class="flex-1 flex flex-col items-center justify-end h-full">
                                    <span class="font-data-mono text-code-md text-on-surface mb-1">{{ $point['average'] }}%</span>
                                    <div class="w-full rounded-t-lg bg-secondary" style="height: {{ max(4, round($point['average'])) }}%"></div>
                                    <span class="mt-2 font-label-sm text-label-sm text-on-surface-variant text-center">{{ $point['term'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- By subject --}}
        <section class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden mb-5">
            <div class="px-5 py-4 border-b border-outline-variant/50">
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">By Subject</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full font-body-md text-body-md">
                    <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">Subject</th>
                            <th class="text-left font-medium px-4 py-3">Teacher</th>
                            <th class="text-right font-medium px-4 py-3">Entries</th>
                            <th class="text-right font-medium px-4 py-3">Average</th>
                            <th class="text-right font-medium px-4 py-3">Pass rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @forelse ($report['by_subject'] as $row)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-4 py-3 font-medium text-on-surface">{{ $row['subject'] }}</td>
                                <td class="px-4 py-3 text-on-surface-variant">{{ $row['teacher'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-data-mono text-data-mono">{{ $row['entries'] }}</td>
                                <td class="px-4 py-3 text-right font-data-mono text-data-mono {{ ($row['average'] ?? 0) >= 50 ? 'text-green-700' : 'text-error' }}">
                                    {{ $row['average'] !== null ? $row['average'] . '%' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-data-mono text-data-mono">{{ $row['pass_rate'] !== null ? $row['pass_rate'] . '%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">No subjects assigned to this class.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Students --}}
        <section class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant/50">
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Students</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Highest first</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full font-body-md text-body-md">
                    <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">#</th>
                            <th class="text-left font-medium px-4 py-3">Student</th>
                            <th class="text-right font-medium px-4 py-3">Term average</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @forelse ($report['students'] as $index => $row)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-4 py-3 font-data-mono text-data-mono text-on-surface-variant">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-on-surface">{{ $row['student']->full_name }}</p>
                                    <p class="font-data-mono text-code-md text-on-surface-variant">{{ $row['student']->student_number }}</p>
                                </td>
                                <td class="px-4 py-3 text-right font-data-mono text-data-mono font-semibold {{ ($row['average'] ?? 0) >= 50 ? 'text-green-700' : 'text-error' }}">
                                    {{ $row['average'] !== null ? round($row['average'], 1) . '%' : 'No marks' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">No students in this class.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
>>>>>>> ae247bb (Progress upto Phase 12)
@endsection
