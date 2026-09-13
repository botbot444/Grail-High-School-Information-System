@extends('layouts.teacher')

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
@endsection
