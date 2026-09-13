@extends('layouts.parent')

@section('title', 'Assignments – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Assignments</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    @if ($student)
                        {{ $student->full_name }} · {{ $student->schoolClass?->display_name ?? ($student->schoolClass?->class_name ?? '—') }}
                    @endif
                </p>
            </div>
            <span class="text-xs font-semibold text-on-surface-variant bg-surface-container px-2 py-1 rounded flex items-center gap-1 self-start">
                <span class="material-symbols-outlined" style="font-size:14px">history_edu</span>
                {{ $recent->count() }} recorded assessments
            </span>
        </div>

        {{-- ── Recent assessments ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Recent Assessments</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Latest recorded tests and examinations</p>
            </div>
            <ul class="divide-y divide-outline-variant">
                @forelse ($recent as $item)

                    @php
                        $badge = $item['status'] === 'passed'
                            ? 'bg-green-50 text-green-700'
                            : 'bg-amber-50 text-amber-700';
                        $icon = $item['status'] === 'passed' ? 'check_circle' : 'info';
                    @endphp
                    <li class="px-5 py-3.5 flex items-center gap-3 hover:bg-surface-container transition-colors">
                        <span class="w-9 h-9 shrink-0 {{ $badge }} rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-on-surface text-sm truncate">{{ $item['title'] }}</p>
                                <span class="text-xs font-extrabold text-on-surface shrink-0">
                                    {{ number_format($item['score'], 1) }}/{{ number_format($item['max'], 0) }}
                                </span>
                            </div>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ $item['type'] }} · {{ $item['date'] }}</p>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">assignment</span>
                        <p class="font-label-sm text-label-sm text-on-surface mt-2">No assessments recorded yet</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
