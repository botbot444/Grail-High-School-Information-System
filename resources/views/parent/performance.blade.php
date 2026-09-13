@extends('layouts.parent')

@section('title', 'Performance – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Performance</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    @if ($student)
                        {{ $student->full_name }} · {{ $student->schoolClass?->display_name ?? ($student->schoolClass?->class_name ?? '—') }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3 self-start">
                <div class="bg-white border border-outline-variant rounded-lg px-3 py-1.5 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">GPA</p>
                    <p class="text-sm font-extrabold text-primary leading-tight">{{ number_format($gpa, 2) }}</p>
                </div>
                <div class="bg-white border border-outline-variant rounded-lg px-3 py-1.5 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Class Rank</p>
                    <p class="text-sm font-extrabold text-primary leading-tight">#{{ $rank }}</p>
                </div>
                <div class="bg-white border border-outline-variant rounded-lg px-3 py-1.5 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Assessments</p>
                    <p class="text-sm font-extrabold text-primary leading-tight">{{ $results->count() }}</p>
                </div>
            </div>
        </div>

        {{-- ── Subject mastery ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5">
            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Subject Mastery</h2>
            <p class="text-xs text-on-surface-variant mt-0.5 mb-4">Average percentage per subject across all assessments</p>
            @if ($subjects->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                    @foreach ($subjects as $mastery)

                        @php
                            $pct = min(100, max(0, (int) round($mastery['avg'])));
                            $barColor = $pct >= 75 ? 'bg-green-600' : ($pct >= 50 ? 'bg-primary' : 'bg-red-500');
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-on-surface">{{ $mastery['subject'] }}</span>
                                <span class="font-bold text-on-surface-variant">{{ number_format($mastery['avg'], 1) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-surface-container rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $barColor }} transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-on-surface-variant">No assessments recorded yet.</p>
            @endif

        {{-- ── Assessment records table ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Assessment Records</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Continuous assessment and examinations</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-container text-on-surface-variant">
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Subject</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Assessment</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Term</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Score</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">

                        @forelse ($results as $grade)
                            @php
                                $type = $grade->assessment_type;
                                $typeBadge = strtoupper((string) $type) === 'EXAM'
                                    ? 'bg-primary/10 text-primary'
                                    : 'bg-surface-container text-on-surface-variant';
                                $letter = $grade->letter_grade ?? '—';
                                $letterColor = in_array($letter, ['A+', 'A'])
                                    ? 'text-green-700 bg-green-50'
                                    : (in_array($letter, ['B+', 'B', 'C+', 'C'])
                                        ? 'text-primary bg-primary/10'
                                        : 'text-red-600 bg-red-50');
                            @endphp
                            <tr class="hover:bg-surface-container transition-colors">
                                <td class="px-5 py-3 font-semibold text-on-surface">{{ $grade->classSubject?->subject?->subject_name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ $typeBadge }}">{{ $type ?? 'CA' }}</span>
                                </td>
                                <td class="px-5 py-3 text-on-surface-variant">{{ $grade->term?->name ?? ($grade->term ?? '—') }}</td>
                                <td class="px-5 py-3 font-bold text-on-surface">
                                    {{ number_format($grade->score, 1) }}
                                    <span class="text-xs font-normal text-on-surface-variant">/ {{ number_format($grade->max_score, 0) }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-full text-xs font-extrabold {{ $letterColor }}">{{ $letter }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <span class="material-symbols-outlined text-3xl text-on-surface-variant">assignment_late</span>
                                    <p class="font-label-sm text-label-sm text-on-surface mt-2">No grades recorded yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

        </div>
