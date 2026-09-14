@extends('layouts.parent')

@section('title', 'Attendance – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Attendance</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    @if ($student)
                        {{ $student->full_name }} · {{ $student->schoolClass?->display_name ?? ($student->schoolClass?->class_name ?? '—') }}
                    @endif
                </p>
            </div>
            <span class="text-xs font-semibold text-on-surface-variant bg-surface-container px-2 py-1 rounded flex items-center gap-1 self-start">
                <span class="material-symbols-outlined" style="font-size:14px">calendar_month</span>
                {{ $totalDays }} records
            </span>
        </div>

        @if ($student)
            {{-- ── Stats grid ── --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Attendance Rate</span>
                        <span class="w-9 h-9 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-on-surface">{{ $attendanceRate }}%</p>
                    <p class="text-xs text-on-surface-variant mt-1">{{ $daysPresent }} of {{ $totalDays }} days punctual</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Days Punctual</span>
                        <span class="w-9 h-9 bg-green-50 text-green-700 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-on-surface">{{ $daysPresent }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">Arrived on time</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Absent / Late</span>
                        <span class="w-9 h-9 bg-red-50 text-red-600 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">event_busy</span>
                        </span>
                    </div>
                    <p class="text-3xl font-extrabold text-on-surface">{{ $absentDays }} / {{ $lateDays ?? 0 }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">Days missed / arrived late</p>
                </div>
                <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Class</span>
                        <span class="w-9 h-9 bg-surface-container text-on-surface-variant rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">school</span>
                        </span>
                    </div>
                    <p class="text-lg font-extrabold text-on-surface leading-tight mt-2">{{ $student->schoolClass?->display_name ?? ($student->schoolClass?->class_name ?? '—') }}</p>
                    <p class="text-xs text-on-surface-variant mt-1">{{ $student->schoolClass?->teacher?->full_name ?? 'No class teacher' }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- ── Recent trend (bar chart) ── --}}
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Recent Trend</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5 mb-4">Present vs absent, most recent records</p>
                    @if ($recentTrend->isNotEmpty())
                        <div class="flex items-end gap-3 h-40">
                            @foreach ($recentTrend as $month)
                                @php
                                    $total = max(1, $month['present'] + $month['absent']);
                                    $pHeight = max(4, (int) round($month['present'] / $total * 100));
                                    $aHeight = max(0, 100 - $pHeight);
                                @endphp
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <div class="w-full h-32 flex flex-col justify-end rounded overflow-hidden">
                                        @if ($aHeight > 0)
                                            <div class="w-full bg-red-200" style="height: {{ $aHeight }}%"></div>
                                        @endif
                                        <div class="w-full bg-primary transition-all" style="height: {{ $pHeight }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-semibold text-on-surface-variant">{{ $month['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-on-surface-variant">No attendance data yet.</p>
                    @endif
                </div>

                {{-- ── History table ── --}}
                <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between">
                        <div>
                            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Attendance History</h2>
                            <p class="text-xs text-on-surface-variant mt-0.5">Most recent first</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-surface-container text-on-surface-variant">
                                    <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Date</th>
                                    <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Status</th>
                                    <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @forelse ($records as $record)
                                    <tr class="hover:bg-surface-container transition-colors">
                                        <td class="px-5 py-3 text-on-surface">
                                            {{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}
                                            <span class="text-xs text-on-surface-variant ml-1">{{ \Carbon\Carbon::parse($record->date)->format('D') }}</span>
                                        </td>
                                        <td class="px-5 py-3">
                                            @php
                                                $status = $record->status;
                                                $badge = match ($status) {
                                                    'Present' => 'bg-green-50 text-green-700',
                                                    'Absent'  => 'bg-red-50 text-red-600',
                                                    'Late'    => 'bg-amber-50 text-amber-700',
                                                    default   => 'bg-surface-container text-on-surface-variant',
                                                };
                                            @endphp
                                            <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ $badge }}">{{ $status }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-on-surface-variant">{{ $record->remarks ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-10 text-center">
                                            <span class="material-symbols-outlined text-3xl text-on-surface-variant">event_busy</span>
                                            <p class="font-label-sm text-label-sm text-on-surface mt-2">No attendance records yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($records->hasPages())
                        <div class="px-5 py-3 border-t border-outline-variant bg-surface-container">
                            {{ $records->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

