@extends('layouts.student')

@section('title', 'Attendance')
@section('page-title', 'My Attendance')
@section('page-subtitle', $term?->name ? $term->name . ' · ' . ($term->academicYear?->label ?? '') : 'Attendance history')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-space-md mb-space-lg">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-space-sm flex-1 min-w-[280px]">
            @include('student.partials.stat-card', [
                'icon' => 'percent', 'label' => 'Rate',
                'value' => $summary['rate'] !== null ? $summary['rate'] . '%' : '—',
                'tone' => ($summary['rate'] ?? 100) >= 90 ? 'success' : 'warning',
            ])
            @include('student.partials.stat-card', ['icon' => 'check_circle', 'label' => 'Present', 'value' => $summary['present'], 'tone' => 'success'])
            @include('student.partials.stat-card', ['icon' => 'cancel', 'label' => 'Absent', 'value' => $summary['absent'], 'tone' => 'error'])
            @include('student.partials.stat-card', ['icon' => 'schedule', 'label' => 'Late', 'value' => $summary['late'], 'tone' => 'warning'])
        </div>
    </div>

    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
        <header class="flex flex-wrap items-center justify-between gap-3 px-space-md py-space-sm border-b border-outline-variant/50">
            <h2 class="text-headline-sm font-headline-sm text-on-surface">Daily Record</h2>
            <form method="GET" class="flex items-center gap-2">
                <label for="term_id" class="text-label-md text-on-surface-variant">Term</label>
                <select name="term_id" id="term_id" onchange="this.form.submit()"
                    class="rounded-xl border-outline-variant/60 bg-surface-container-lowest text-body-md focus:border-secondary focus:ring-secondary/40">
                    <option value="">All time</option>
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                            {{ $option->name }} ({{ $option->academicYear?->label }})
                        </option>
                    @endforeach
                </select>
            </form>
        </header>

        @if ($records->isEmpty())
            @include('student.partials.empty-state', [
                'icon'    => 'how_to_reg',
                'title'   => 'No attendance recorded',
                'message' => 'Your daily attendance will show here once teachers start marking the register.',
            ])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-body-md">
                    <thead class="bg-surface-container-low text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-space-md py-2.5">Date</th>
                            <th class="text-left font-medium px-space-md py-2.5">Subject</th>
                            <th class="text-right font-medium px-space-md py-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @foreach ($records as $record)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-space-md py-2.5 font-data-mono text-data-mono text-on-surface">
                                    {{ $record->date?->format('D, j M Y') ?? '—' }}
                                </td>
                                <td class="px-space-md py-2.5 text-on-surface-variant">
                                    {{ $record->classSubject?->subject?->subject_name ?? 'General' }}
                                </td>
                                <td class="px-space-md py-2.5 text-right">
                                    @include('student.partials.status-pill', ['status' => $record->status])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($summary['total'] > $records->count())
                <p class="px-space-md py-3 text-body-sm text-on-surface-variant border-t border-outline-variant/40">
                    Showing the most recent {{ $records->count() }} of {{ $summary['total'] }} records.
                </p>
            @endif
        @endif
    </section>
@endsection
