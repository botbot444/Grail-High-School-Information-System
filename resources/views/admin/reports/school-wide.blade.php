@extends('layouts.app')

@section('title', 'School-Wide Performance')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


        @php
            $exportBase = fn ($section) => route('admin.reports.school-wide.export', [
                'term_id' => $term?->term_id, 'section' => $section,
            ]);
        @endphp

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Reports</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">School-Wide Performance</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">School-Wide Performance</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    @if ($report)
                        {{ $term->name }} {{ $term->academicYear?->label }} ·
                        pass mark {{ $report['pass_mark'] }}% ·
                        generated {{ $report['generated_at']->format('j M Y, H:i') }}
                    @else
                        No academic terms exist yet.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-2 self-start">
                <form method="GET" class="flex items-center gap-2">
                    <label for="term_id" class="text-label-md font-label-md text-on-surface-variant">Term</label>
                    <select name="term_id" id="term_id" onchange="this.form.submit()"
                        class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                        @forelse ($terms as $t)
                            <option value="{{ $t->term_id }}" @selected($term?->term_id === $t->term_id)>
                                {{ $t->name }} ({{ $t->academicYear?->label }})
                            </option>
                        @empty
                            <option value="">None</option>
                        @endforelse
                    </select>
                </form>
                @if ($report)
                    <a href="{{ $exportBase('all') }}"
                        class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        <span>Export all</span>
                    </a>
                @endif
            </div>
        </div>


        @if (! $report)
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest py-16 text-center">
                <span class="material-symbols-outlined text-[40px] text-outline">bar_chart</span>
                <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">Nothing to report on yet</p>
                <p class="mt-1 font-body-md text-body-md text-on-surface-variant">Create an academic year and terms first.</p>
            </div>
        @else

            {{-- 1. Overview --}}
            <section class="mb-6">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Overview</h2>
                    <a href="{{ $exportBase('overview') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">download</span> CSV
                    </a>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                    @foreach ([
                        ['Students', $report['overview']['students']],
                        ['With marks', $report['overview']['marked']],
                        ['Grade levels', $report['overview']['grade_levels']],
                        ['Classes', $report['overview']['classes']],
                        ['Average', $report['overview']['average'] !== null ? $report['overview']['average'] . '%' : '—'],
                        ['Pass rate', $report['overview']['pass_rate'] !== null ? $report['overview']['pass_rate'] . '%' : '—'],
                    ] as [$label, $value])
                        <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $label }}</p>
                            <p class="mt-1 font-headline-md text-headline-md font-extrabold text-on-surface">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                {{-- 2. By grade level --}}
                <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between gap-3">
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">By Grade Level</h2>
                        <a href="{{ $exportBase('grade-level') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">download</span> CSV
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full font-body-md text-body-md">
                            <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-4 py-3">Level</th>
                                    <th class="text-right font-medium px-4 py-3">Students</th>
                                    <th class="text-right font-medium px-4 py-3">Avg</th>
                                    <th class="text-right font-medium px-4 py-3">Pass</th>
                                    <th class="text-left font-medium px-4 py-3">Top / bottom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @forelse ($report['by_grade_level'] as $row)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-on-surface">{{ $row['grade_level'] }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['students'] }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['average'] !== null ? $row['average'] . '%' : '—' }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['pass_rate'] !== null ? $row['pass_rate'] . '%' : '—' }}</td>
                                        <td class="px-4 py-2.5 text-xs text-on-surface-variant">
                                            @if ($row['top'])
                                                <span class="text-green-700">▲ {{ $row['top']['student']->full_name }} ({{ round($row['top']['average'], 1) }}%)</span><br>
                                                <span class="text-error">▼ {{ $row['bottom']['student']->full_name }} ({{ round($row['bottom']['average'], 1) }}%)</span>
                                            @else — @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- 3. By subject --}}
                <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between gap-3">
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">By Subject</h2>
                        <a href="{{ $exportBase('subject') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">download</span> CSV
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full font-body-md text-body-md">
                            <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-4 py-3">Subject</th>
                                    <th class="text-right font-medium px-4 py-3">Students</th>
                                    <th class="text-right font-medium px-4 py-3">Avg</th>
                                    <th class="text-right font-medium px-4 py-3">Pass</th>
                                    <th class="text-left font-medium px-4 py-3">Best / worst class</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @forelse ($report['by_subject'] as $row)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-on-surface">{{ $row['subject'] }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['students'] }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['average'] !== null ? $row['average'] . '%' : '—' }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['pass_rate'] !== null ? $row['pass_rate'] . '%' : '—' }}</td>
                                        <td class="px-4 py-2.5 text-xs text-on-surface-variant">
                                            @if ($row['top_class'])
                                                <span class="text-green-700">▲ {{ $row['top_class'] }} ({{ $row['top_score'] }}%)</span><br>
                                                <span class="text-error">▼ {{ $row['bottom_class'] }} ({{ $row['bottom_score'] }}%)</span>
                                            @else — @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-on-surface-variant">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- 4. Class summary --}}
            <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Class Performance Summary</h2>
                        <p class="text-xs text-on-surface-variant mt-0.5">Ranked by term average</p>
                    </div>
                    <a href="{{ $exportBase('class') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">download</span> CSV
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full font-body-md text-body-md">
                        <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="text-left font-medium px-4 py-3">Rank</th>
                                <th class="text-left font-medium px-4 py-3">Class</th>
                                <th class="text-left font-medium px-4 py-3">Class teacher</th>
                                <th class="text-right font-medium px-4 py-3">Students</th>
                                <th class="text-right font-medium px-4 py-3">Average</th>
                                <th class="text-right font-medium px-4 py-3">Pass rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @forelse ($report['by_class'] as $row)
                                <tr class="hover:bg-surface-container-low/60 transition-colors">
                                    <td class="px-4 py-2.5 font-data-mono text-data-mono font-bold text-on-surface">{{ $row['rank'] ?? '—' }}</td>
                                    <td class="px-4 py-2.5 font-medium text-on-surface">{{ $row['class'] }}</td>
                                    <td class="px-4 py-2.5 text-on-surface-variant">{{ $row['teacher'] ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['students'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-data-mono text-data-mono font-semibold {{ ($row['average'] ?? 0) >= 50 ? 'text-green-700' : 'text-error' }}">
                                        {{ $row['average'] !== null ? $row['average'] . '%' : '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['pass_rate'] !== null ? $row['pass_rate'] . '%' : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-on-surface-variant">No classes with students.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- 5. Low performers --}}
            <section class="rounded-xl border border-error/30 bg-surface-container-lowest shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-error text-[20px]">warning</span>
                            Low-Performing Students
                        </h2>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            {{ $report['low_performers']->count() }} student(s) averaging below {{ $report['threshold'] }}%
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('admin.reports.school-wide.threshold') }}" class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <label for="threshold" class="text-xs font-semibold text-on-surface-variant">Threshold</label>
                            <input type="number" name="threshold" id="threshold" min="1" max="99" step="1"
                                value="{{ $report['threshold'] }}"
                                class="w-20 rounded-lg border border-outline-variant px-2 py-1.5 text-sm font-mono focus:ring-2 focus:ring-primary">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-semibold hover:bg-primary/90 transition-colors">Set</button>
                        </form>
                        <a href="{{ $exportBase('low-performers') }}" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">download</span> CSV
                        </a>
                    </div>
                </div>

                @if ($report['low_performers']->isEmpty())
                    <p class="px-5 py-10 text-center font-body-md text-body-md text-on-surface-variant">
                        No student is averaging below {{ $report['threshold'] }}% this term.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full font-body-md text-body-md">
                            <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-4 py-3">Student</th>
                                    <th class="text-left font-medium px-4 py-3">Class</th>
                                    <th class="text-right font-medium px-4 py-3">Term average</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @foreach ($report['low_performers'] as $row)
                                    <tr class="hover:bg-surface-container-low/60 transition-colors">
                                        <td class="px-4 py-2.5">
                                            <p class="font-medium text-on-surface">{{ $row['student']->full_name }}</p>
                                            <p class="font-data-mono text-code-md text-on-surface-variant">{{ $row['student']->student_number }}</p>
                                        </td>
                                        <td class="px-4 py-2.5 text-on-surface-variant">{{ $row['student']->schoolClass?->class_name ?? '—' }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono font-bold text-error">
                                            {{ round($row['average'], 1) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
    </div>
@endsection
