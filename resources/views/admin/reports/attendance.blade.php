@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Reports</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Attendance</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Attendance Report</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    By student, class and subject. Rates count one mark per day, not per lesson.
                </p>
            </div>
            <a href="{{ route('admin.reports.attendance.export', request()->query()) }}"
                class="self-start inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Export CSV</span>
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Term</label>
                    <select name="term_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All time</option>
                        @foreach ($terms as $t)
                            <option value="{{ $t->term_id }}" @selected(($filters['term_id'] ?? null) == $t->term_id)>
                                {{ $t->name }} ({{ $t->academicYear?->label }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Class</label>
                    <select name="class_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All classes</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->class_id }}" @selected(($filters['class_id'] ?? null) == $c->class_id)>{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Subject</label>
                    <select name="class_subject_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All subjects</option>
                        @foreach ($classSubjects as $cs)
                            <option value="{{ $cs->class_subject_id }}" @selected(($filters['class_subject_id'] ?? null) == $cs->class_subject_id)>
                                {{ $cs->subject?->subject_name }} — {{ $cs->schoolClass?->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">From</label>
                    <input type="date" name="from" value="{{ $filters['from'] }}"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">To</label>
                    <input type="date" name="to" value="{{ $filters['to'] }}"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span> Apply
                </button>
                <a href="{{ route('admin.reports.attendance') }}" class="px-3 py-2 text-sm font-medium text-on-surface-variant hover:text-primary">Clear</a>
                @if ($report['school_days'])
                    <span class="ml-auto text-xs text-on-surface-variant">
                        {{ $report['school_days'] }} school days in term (weekends and holidays excluded)
                    </span>
                @endif
            </div>
        </form>

        {{-- Summary --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            @foreach ([
                ['Students', $report['summary']['students']],
                ['Present', $report['summary']['present']],
                ['Absent', $report['summary']['absent']],
                ['Late', $report['summary']['late']],
                ['Average rate', $report['summary']['rate'] !== null ? $report['summary']['rate'] . '%' : '—'],
            ] as [$label, $value])
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $label }}</p>
                    <p class="mt-1 font-headline-md text-headline-md font-extrabold text-on-surface">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            @foreach ([['By Class', $report['by_class'], 'class', 'students'], ['By Subject', $report['by_subject'], 'subject', 'records']] as [$title, $rows, $keyField, $countField])
                <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant">
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $title }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full font-body-md text-body-md">
                            <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-4 py-3">{{ ucfirst($keyField) }}</th>
                                    <th class="text-right font-medium px-4 py-3">{{ ucfirst($countField) }}</th>
                                    <th class="text-right font-medium px-4 py-3">P / A / L</th>
                                    <th class="text-right font-medium px-4 py-3">Rate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-on-surface">{{ $row[$keyField] }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row[$countField] }}</td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono text-on-surface-variant">
                                            {{ $row['present'] }} / {{ $row['absent'] }} / {{ $row['late'] }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-data-mono text-data-mono font-semibold {{ ($row['rate'] ?? 100) >= 90 ? 'text-green-700' : 'text-amber-700' }}">
                                            {{ $row['rate'] !== null ? $row['rate'] . '%' : '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center font-body-md text-body-md text-on-surface-variant">No records for this selection.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Per student --}}
        <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">By Student</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Lowest attendance first, so the students needing attention are at the top</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full font-body-md text-body-md">
                    <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">Student</th>
                            <th class="text-left font-medium px-4 py-3">Class</th>
                            <th class="text-right font-medium px-4 py-3">Present</th>
                            <th class="text-right font-medium px-4 py-3">Absent</th>
                            <th class="text-right font-medium px-4 py-3">Late</th>
                            <th class="text-right font-medium px-4 py-3">Days</th>
                            <th class="text-right font-medium px-4 py-3">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @forelse ($report['per_student'] as $row)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-on-surface">{{ $row['student']->full_name }}</p>
                                    <p class="font-data-mono text-code-md text-on-surface-variant">{{ $row['student']->student_number }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-on-surface-variant">{{ $row['class'] ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['present'] }}</td>
                                <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['absent'] }}</td>
                                <td class="px-4 py-2.5 text-right font-data-mono text-data-mono">{{ $row['late'] }}</td>
                                <td class="px-4 py-2.5 text-right font-data-mono text-data-mono text-on-surface-variant">{{ $row['recorded'] }}</td>
                                <td class="px-4 py-2.5 text-right font-data-mono text-data-mono font-semibold {{ ($row['rate'] ?? 100) >= 90 ? 'text-green-700' : (($row['rate'] ?? 100) >= 75 ? 'text-amber-700' : 'text-error') }}">
                                    {{ $row['rate'] !== null ? $row['rate'] . '%' : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">
                                No attendance records match these filters.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
