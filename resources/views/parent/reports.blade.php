@extends('layouts.parent')

@section('title', 'Report Cards – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Report Cards</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    @if ($selectedChild)
                        {{ $selectedChild->full_name }} · {{ $selectedChild->schoolClass?->display_name ?? ($selectedChild->schoolClass?->class_name ?? '—') }}
                    @endif
                </p>
            </div>
            <form method="GET" action="{{ route('parent.reports') }}" class="flex items-center gap-2 self-start">
                @if (request('child_id'))
                    <input type="hidden" name="child_id" value="{{ request('child_id') }}">
                @endif
                <select name="academic_year_id"
                    class="text-xs font-semibold bg-white border border-outline-variant rounded-lg px-3 py-2 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <option value="">All years</option>
                    @foreach ($years as $year)
                        <option value="{{ $year->year_id }}" {{ request('academic_year_id') == $year->year_id ? 'selected' : '' }}>
                            {{ $year->label ?? $year->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="text-xs font-bold bg-primary text-on-primary rounded-lg px-3 py-2 hover:bg-primary/90 transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:14px">filter_alt</span> Filter
                </button>
            </form>
        </div>

        {{-- ── Report cards ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @forelse ($reportCards as $card)
                @php
                    $avg = $card['average'];
                    $gradeLetter = $avg >= 90 ? 'A+' : ($avg >= 80 ? 'A' : ($avg >= 70 ? 'B' : ($avg >= 60 ? 'C' : ($avg >= 50 ? 'D' : 'F'))));
                    $gradeColor = $avg >= 80 ? 'bg-green-50 text-green-700' : ($avg >= 50 ? 'bg-primary/10 text-primary' : 'bg-red-50 text-red-600');
                @endphp
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">
                                {{ $card['term']->name ?? 'Term' }}
                            </h2>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ $card['year']->label ?? $card['year']->name ?? '' }}</p>
                        </div>
                        <span class="inline-flex w-10 h-10 items-center justify-center rounded-full text-sm font-extrabold {{ $gradeColor }}">{{ $gradeLetter }}</span>
                    </div>
                    <div class="p-5 grid grid-cols-3 gap-3">
                        <div class="bg-surface-container rounded-lg p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Average</p>
                            <p class="text-xl font-extrabold text-on-surface mt-1">{{ number_format($avg, 1) }}%</p>
                        </div>
                        <div class="bg-surface-container rounded-lg p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Subjects</p>
                            <p class="text-xl font-extrabold text-on-surface mt-1">{{ $card['subjects'] }}</p>
                        </div>
                        <div class="bg-surface-container rounded-lg p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Attendance</p>
                            <p class="text-xl font-extrabold text-on-surface mt-1">{{ $card['attendance'] }}%</p>
                        </div>
                    </div>
                    {{-- Phase 11: rank + the actual report card, once finalized. --}}
                    @if ($card['card'])
                        <div class="px-5 pb-4 -mt-1 flex flex-wrap items-center gap-2">
                            @if ($card['card']->rank_label)
                                <span class="text-xs font-semibold bg-primary/10 text-primary px-2.5 py-1 rounded-lg flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px">military_tech</span>
                                    Position {{ $card['card']->rank_label }}
                                </span>
                            @endif
                            <a href="{{ route('parent.report-card', [$student->student_id, $card['term']->term_id]) }}"
                               target="_blank" rel="noopener"
                               class="text-xs font-semibold border border-outline-variant text-on-surface-variant hover:bg-surface-container px-2.5 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined" style="font-size:14px">visibility</span> View
                            </a>
                            <a href="{{ route('parent.report-card', [$student->student_id, $card['term']->term_id]) }}?download=1"
                               class="text-xs font-bold bg-primary text-on-primary hover:bg-primary/90 px-2.5 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
                                <span class="material-symbols-outlined" style="font-size:14px">download</span> PDF
                            </a>
                        </div>
                    @endif

                    <div class="px-5 py-3 bg-surface-container border-t border-outline-variant flex items-center justify-between">
                        <span class="text-xs text-on-surface-variant">
                            {{ $card['class']?->display_name ?? ($card['class']?->class_name ?? '—') }}
                        </span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide {{ $card['card'] ? 'text-green-700' : 'text-on-surface-variant' }}">
                            {{ $card['card'] ? 'Finalized' : ($card['term']->is_current ? 'Current term' : 'Not finalized') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant shadow-sm p-10 text-center">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant">description</span>
                    <p class="font-label-sm text-label-sm text-on-surface mt-2">No report cards available yet</p>
                    <p class="text-sm text-on-surface-variant mt-1">Report cards appear once grades are recorded for a term.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
