@extends('layouts.parent')

@section('title', 'Dashboard – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Welcome header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    Welcome back, {{ explode(' ', trim($parentProfile->user->name ?? 'Parent'))[0] }}!
                </h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    @if ($currentTerm)
                        {{ $currentTerm->name }} @if ($currentTerm->academicYear)· {{ $currentTerm->academicYear->label ?? '' }} @endif
                    @else
                        Academic year in progress
                    @endif
                    · {{ $children->count() }} enrolled {{ $children->count() === 1 ? 'child' : 'children' }}
                </p>
            </div>
            <a href="{{ route('parent.reports') }}"
                class="text-xs font-bold bg-primary text-on-primary rounded-lg px-3 py-2 hover:bg-primary/90 transition-colors flex items-center gap-1.5 self-start">
                <span class="material-symbols-outlined" style="font-size:14px">description</span> View Report Cards
            </a>
        </div>

        {{-- ── Overdue fee notice (Phase 7) ── --}}
        @include('parent.partials.overdue-banner')

        {{-- ── Children summary cards ── --}}
        <h2 class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Your Children</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($children as $child)
                @php $s = $child['student']; @endphp
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm hover:border-primary transition-colors overflow-hidden">
                    <div class="p-5 flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="text-primary text-lg font-extrabold">{{ strtoupper(substr($s->full_name ?? 'S', 0, 2)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface truncate">{{ $s->full_name }}</h3>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                {{ $s->schoolClass?->display_name ?? ($s->schoolClass?->class_name ?? '—') }}
                            </p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ ($child['fee_status'] ?? 'Cleared') === 'Cleared' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                    Fees {{ $child['fee_status'] ?? 'Cleared' }}
                                </span>
                                @if (($child['assessments'] ?? 0) > 0)
                                    <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded bg-primary/10 text-primary">
                                        {{ $child['assessments'] }} assessments
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 border-t border-outline-variant divide-x divide-outline-variant">
                        <div class="p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Attendance</p>
                            <p class="text-lg font-extrabold text-on-surface mt-0.5">{{ $child['attendance_rate'] }}%</p>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">GPA</p>
                            <p class="text-lg font-extrabold text-on-surface mt-0.5">{{ number_format($child['gpa'], 2) }}</p>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Balance</p>
                            <p class="text-lg font-extrabold {{ ($child['fee_balance'] ?? 0) > 0 ? 'text-error' : 'text-green-700' }} mt-0.5">
                                ZMW {{ number_format($child['fee_balance'] ?? 0, 0) }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 px-5 py-3 bg-surface-container border-t border-outline-variant">
                        <a href="{{ route('parent.attendance', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">how_to_reg</span> Attendance
                        </a>
                        <a href="{{ route('parent.performance', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">assignment_turned_in</span> Performance
                        </a>
                        <a href="{{ route('parent.reports', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">description</span> Reports
                        </a>
                        <a href="{{ route('parent.fees', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">payments</span> Fees
                        </a>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 bg-white border border-outline-variant rounded-xl p-10 text-center">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant">family_restroom</span>
                    <p class="font-label-sm text-label-sm text-on-surface mt-2">No children linked to your account yet</p>
                    <p class="text-sm text-on-surface-variant mt-1">Please contact the school office to link your children.</p>
                </div>
            @endforelse
        </div>

        {{-- ── Selected-child overview ── --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Overview</h2>
            @if ($children->count() > 1)
                <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary text-xs font-bold px-2.5 py-1 rounded-full">
                    <span class="material-symbols-outlined text-[14px]">person</span>
                    Viewing {{ $selectedChild?->full_name ?? 'no child selected' }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Attendance</span>
                    <span class="w-9 h-9 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-on-surface">{{ $attendanceRate }}%</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Assessments Recorded</span>
                    <span class="w-9 h-9 bg-amber-50 text-amber-700 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">fact_check</span>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-on-surface">{{ $upcomingAssessments->count() }}</p>
                <p class="text-xs text-on-surface-variant mt-1">recently recorded</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Enrolled Children</span>
                    <span class="w-9 h-9 bg-green-50 text-green-700 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">family_restroom</span>
                    </span>
                </div>
                <p class="text-3xl font-extrabold text-on-surface">{{ $children->count() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- ── Performance trend chart (CSS bars) ── --}}
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Performance Trend</h2>
                <p class="text-xs text-on-surface-variant mt-0.5 mb-4">Average % by month</p>
                @if ($performanceTrend->isNotEmpty())
                    <div class="flex items-end gap-2 h-40">
                        @foreach ($performanceTrend as $point)
                            @php $h = max(4, min(100, (int) round($point['value']))); @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <span class="text-[10px] font-bold text-on-surface">{{ $point['value'] }}%</span>
                                <div class="w-full bg-primary rounded-t transition-all" style="height: {{ $h }}%"></div>
                                <span class="text-[10px] font-semibold text-on-surface-variant">{{ $point['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-on-surface-variant">No grades recorded yet.</p>
                @endif
            </div>

            {{-- ── Recent results ── --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Recent Results</h2>
                        <p class="text-xs text-on-surface-variant mt-0.5">Latest graded work</p>
                    </div>
                    <a href="{{ route('parent.performance') }}" class="text-xs font-semibold text-primary hover:underline">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-surface-container text-on-surface-variant">
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Subject</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Assessment</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Score</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($recentResults as $grade)
                                <tr class="hover:bg-surface-container transition-colors">
                                    <td class="px-5 py-3 font-semibold text-on-surface">{{ $grade->classSubject?->subject?->subject_name ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ strtoupper((string) $grade->assessment_type) === 'EXAM' ? 'bg-primary/10 text-primary' : 'bg-surface-container text-on-surface-variant' }}">
                                            {{ $grade->assessment_type ?? 'CA' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 font-bold text-on-surface">{{ number_format($grade->score, 1) }}<span class="text-xs font-normal text-on-surface-variant">/{{ number_format($grade->max_score, 0) }}</span></td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex w-8 h-8 items-center justify-center rounded-full text-xs font-extrabold {{ in_array($grade->letter_grade, ['A+', 'A']) ? 'bg-green-50 text-green-700' : 'bg-primary/10 text-primary' }}">
                                            {{ $grade->letter_grade }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center">
                                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">assignment_late</span>
                                        <p class="font-label-sm text-label-sm text-on-surface mt-2">No results recorded yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Latest assessments for selected child ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between">
                <div>
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Latest Assessments</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Recently recorded</p>
                </div>
                <a href="{{ route('parent.assignments') }}" class="text-xs font-semibold text-primary hover:underline">View all</a>
            </div>
            <ul class="divide-y divide-outline-variant">
                @forelse ($upcomingAssessments as $grade)
                    <li class="px-5 py-3.5 flex items-center gap-3 hover:bg-surface-container transition-colors">
                        <span class="w-9 h-9 shrink-0 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">fact_check</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-on-surface text-sm truncate">
                                {{ $grade->classSubject?->subject?->subject_name ?? 'Assessment' }} · {{ $grade->assessment_type ?? 'CA' }}
                            </p>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                {{ number_format($grade->score, 1) }} / {{ number_format($grade->max_score, 0) }} · {{ optional($grade->created_at)->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ $grade->percentage >= 60 ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $grade->percentage >= 60 ? 'Passed' : 'Needs review' }}
                        </span>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">fact_check</span>
                        <p class="font-label-sm text-label-sm text-on-surface mt-2">No assessments recorded yet</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

