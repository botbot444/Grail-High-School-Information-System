@extends('layouts.parent')

@section('title', 'Assignments – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Assignments</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    {{ $student->full_name }} · {{ $student->schoolClass?->display_name ?? ($student->schoolClass?->class_name ?? '—') }}
                </p>
            </div>
            @if ($counts['overdue'] > 0)
                <span class="text-xs font-bold text-red-700 bg-red-50 px-2 py-1 rounded flex items-center gap-1 self-start">
                    <span class="material-symbols-outlined" style="font-size:14px">warning</span>
                    {{ $counts['overdue'] }} overdue
                </span>
            @endif
        </div>

        {{-- ── Summary ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['Pending', $counts['pending'], 'pending_actions', 'text-amber-700', 'bg-amber-50'],
                ['Overdue', $counts['overdue'], 'assignment_late', 'text-red-700', 'bg-red-50'],
                ['Submitted', $counts['submitted'], 'upload_file', 'text-blue-700', 'bg-blue-50'],
                ['Graded', $counts['graded'], 'task_alt', 'text-green-700', 'bg-green-50'],
            ] as [$label, $value, $icon, $fg, $bg])
                <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $label }}</span>
                        <span class="w-8 h-8 {{ $bg }} {{ $fg }} rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
                        </span>
                    </div>
                    <p class="font-headline-md text-headline-md font-extrabold text-on-surface">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- ── List ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Set Work</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">
                    Most recent first · you can see what has been handed in, but only {{ $student->first_name }} can submit
                </p>
            </div>

            <ul class="divide-y divide-outline-variant">
                @forelse ($assignments as $assignment)
                    @php
                        $status = $assignment->child_status;
                        $submission = $assignment->child_submission;
                        $tones = [
                            'Graded'    => ['bg-green-50', 'text-green-700'],
                            'Submitted' => ['bg-blue-50', 'text-blue-700'],
                            'Pending'   => ['bg-amber-50', 'text-amber-700'],
                            'Overdue'   => ['bg-red-50', 'text-red-700'],
                        ];
                        [$tbg, $tfg] = $tones[$status] ?? ['bg-surface-container', 'text-on-surface-variant'];
                    @endphp
                    <li class="px-5 py-4 flex flex-wrap items-start gap-4 hover:bg-surface-container transition-colors">
                        <span class="w-9 h-9 shrink-0 {{ $tbg }} {{ $tfg }} rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">
                                {{ $status === 'Graded' ? 'task_alt' : ($status === 'Overdue' ? 'assignment_late' : 'assignment') }}
                            </span>
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-on-surface text-sm">{{ $assignment->title }}</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                {{ $assignment->classSubject?->subject?->subject_name ?? 'Subject' }}
                                @if ($assignment->classSubject?->teacher)
                                    · {{ $assignment->classSubject->teacher->full_name }}
                                @endif
                            </p>
                            <p class="text-xs mt-1 flex items-center gap-1 {{ $status === 'Overdue' ? 'text-red-700 font-medium' : 'text-on-surface-variant' }}">
                                <span class="material-symbols-outlined" style="font-size:13px">schedule</span>
                                Due {{ $assignment->due_at->format('M d, Y · H:i') }}
                                @if ($submission?->submitted_at)
                                    · handed in {{ $submission->submitted_at->format('M d') }}@if ($submission->isLate()) <span class="text-red-700 font-medium">(late)</span>@endif
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <span class="text-[11px] font-bold px-2 py-1 rounded {{ $tbg }} {{ $tfg }}">{{ $status }}</span>
                            @if ($submission?->score !== null)
                                <span class="text-xs font-mono font-semibold text-on-surface">
                                    {{ rtrim(rtrim(number_format((float) $submission->score, 1), '0'), '.') }}/{{ rtrim(rtrim(number_format((float) $assignment->max_score, 1), '0'), '.') }}
                                </span>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant">assignment</span>
                        <p class="font-headline-sm text-headline-sm font-bold text-on-surface mt-3">No assignments set</p>
                        <p class="text-sm text-on-surface-variant mt-1 max-w-md mx-auto">
                            Work set by {{ $student->first_name }}'s teachers will appear here, with whether it has been handed in.
                        </p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
