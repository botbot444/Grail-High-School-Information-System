@extends('layouts.teacher')

@section('title', 'Enter Marks - Teacher Portal')

@section('page')
@php
    $graded = $students->filter(fn ($s) => $s['mark'] !== null);
    $classAverage = $graded->isNotEmpty() ? round($graded->avg('mark'), 1) : null;
    $badgeStyles = [
        'A+' => 'bg-[#dcfce7] text-[#166534]', 'A' => 'bg-[#dcfce7] text-[#166534]',
        'B+' => 'bg-[#dbeafe] text-[#1e40af]', 'B' => 'bg-[#dbeafe] text-[#1e40af]',
        'C+' => 'bg-[#fef3c7] text-[#92400e]', 'C' => 'bg-[#fef3c7] text-[#92400e]',
        'D'  => 'bg-[#fef3c7] text-[#92400e]', 'F' => 'bg-[#fee2e2] text-[#ba1a1a]',
    ];
@endphp
<div class="flex flex-col w-full gap-space-lg pb-24">
    @if ($assignments->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-xl flex flex-col items-center text-center gap-2">
            <span class="material-symbols-outlined text-[48px] text-outline">edit_note</span>
            <h2 class="font-title-md text-title-md text-on-surface">No Classes Assigned</h2>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md">You have no class subject assignments yet. Once the administrator assigns you to a class section, its roster will appear here for mark entry.</p>
        </div>
    @else
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md">
            <div class="flex flex-col gap-1">
                <span class="inline-flex items-center w-fit px-2 py-0.5 rounded bg-secondary/10 text-secondary font-label-sm text-label-sm uppercase tracking-wider">Assessment Module</span>
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Enter Marks</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Record exam scores for your class and see each student's grade update as you type.</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <section class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
            <form method="GET" action="{{ route('teacher.marks') }}">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end">
                    <div class="md:col-span-5 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider" for="assignmentSelect">Class &amp; Subject</label>
                        <div class="relative">
                            <select class="w-full h-10 pl-3.5 pr-10 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface appearance-none focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_2px_#085bbd] transition-all cursor-pointer" id="assignmentSelect" name="assignment_id" onchange="this.form.submit()">
                                @foreach ($assignments as $a)
                                    <option value="{{ $a->class_subject_id }}" {{ $assignment && $a->class_subject_id === $assignment->class_subject_id ? 'selected' : '' }}>{{ $a->schoolClass->class_name }} ({{ $a->subject->subject_name }})</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-on-surface-variant text-[20px]">expand_more</span>
                        </div>
                    </div>
                    <div class="md:col-span-4 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider" for="termSelect">Term</label>
                        <div class="relative">
                            <select class="w-full h-10 pl-3.5 pr-10 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface appearance-none focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_2px_#085bbd] transition-all cursor-pointer" id="termSelect" name="term_id" onchange="this.form.submit()" @if ($terms->isEmpty()) disabled @endif>
                                @forelse ($terms as $t)
                                    <option value="{{ $t->term_id }}" {{ $term && $t->term_id === $term->term_id ? 'selected' : '' }}>{{ $t->name }} — {{ $t->academicYear->label ?? '' }}</option>
                                @empty
                                    <option>Term 1 (default)</option>
                                @endforelse
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-on-surface-variant text-[20px]">expand_more</span>
                        </div>
                    </div>
                    <div class="md:col-span-3 flex">
                        <button type="submit" class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-md transition-all">
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                            <span>Load Class</span>
                        </button>
                    </div>
                </div>
            </form>
        </section>

        @if ($isLocked)
            <section class="bg-error-container/40 border border-error/30 rounded-xl p-space-md flex items-start gap-3">
                <span class="material-symbols-outlined text-error">lock</span>
                <div>
                    <h2 class="font-title-sm text-title-sm text-on-surface">Grades finalized for {{ $term->name }}</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">This class's report cards for this term are locked. Ask an administrator to unfinalize before making changes, or head to
                        <a href="{{ route('teacher.performance', ['assignment_id' => $assignment->class_subject_id, 'term_id' => $term->term_id]) }}" class="text-primary font-semibold hover:underline">Class Performance</a> to review the finalized results.
                    </p>
                </div>
            </section>
        @endif

        {{-- Live Stats Strip --}}
        @if ($assignment)
            <section class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md bg-surface-container-low p-space-md rounded-xl">
                <div class="flex items-center gap-2">
                    <span class="font-data-sm text-data-sm text-on-surface-variant">{{ $assignment->schoolClass->class_name }} · {{ $assignment->subject->subject_name }} · {{ $term->name ?? 'Term 1' }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-space-sm bg-surface-container-lowest px-4 py-2 rounded-lg shadow-sm">
                    <div class="flex items-center gap-1.5 pr-2">
                        <span class="w-2 h-2 rounded-full bg-secondary"></span>
                        <span class="font-label-md text-label-md text-on-surface font-semibold" id="countTotal">{{ $students->count() }}</span>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">students</span>
                    </div>
                    <span class="text-outline-variant">•</span>
                    <span class="font-label-md text-label-md text-on-surface" id="countGraded">{{ $graded->count() }}</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">graded</span>
                    <span class="text-outline-variant">•</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Class average:</span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold" id="classAverage">{{ $classAverage !== null ? $classAverage.'%' : '—' }}</span>
                </div>
            </section>
        @endif

        {{-- Marks Entry Table --}}
        <form method="POST" action="{{ route('teacher.marks.store') }}" id="marksForm">
            @csrf
            <input type="hidden" name="assignment_id" value="{{ $assignment?->class_subject_id }}"/>
            <input type="hidden" name="term_id" value="{{ $term?->term_id }}"/>
            <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="marksTable">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant">
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider w-12 text-center">#</th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider min-w-[240px]">Student</th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider w-40">Exam Score <span class="font-normal lowercase text-outline">/ 100</span></th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider w-28 text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container-low" id="marksBody">
                            @forelse ($students as $student)
                                <tr class="hover:bg-surface-container-lowest/70 transition-colors student-row" data-id="{{ $student['id'] }}">
                                    <td class="py-3.5 px-4 font-data-sm text-data-sm text-on-surface-variant text-center">{{ str_pad($student['index'], 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center font-title-sm text-title-sm">{{ $student['initials'] }}</div>
                                            <span class="font-title-sm text-title-sm text-on-surface">{{ $student['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <input type="number" min="0" max="100" step="1"
                                            class="marks-input w-24 h-10 px-3 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_2px_#085bbd] transition-all"
                                            name="marks[{{ $student['id'] }}]"
                                            value="{{ $student['mark'] !== null ? (int) $student['mark'] : '' }}"
                                            placeholder="—"
                                            @if ($isLocked) disabled @endif>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="grade-badge inline-flex px-2.5 py-1 rounded font-label-sm text-label-sm font-bold {{ $student['letter'] ? ($badgeStyles[$student['letter']] ?? '') : 'bg-surface-container text-on-surface-variant' }}">{{ $student['letter'] ?? '—' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-4 text-center text-on-surface-variant">No students in this class.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($students->isNotEmpty())
                    <div class="flex items-center justify-end gap-space-sm p-space-md border-t border-outline-variant">
                        <button type="submit" id="saveMarksBtn"
                            class="inline-flex items-center gap-2 px-space-md py-2.5 rounded-lg bg-primary text-on-primary font-title-sm text-title-sm shadow-md hover:bg-primary/90 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            @if ($isLocked) disabled @endif>
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>Save Marks</span>
                        </button>
                    </div>
                @endif
            </div>
        </form>
    @endif
</div>

<script>
    (function () {
        const gradeInfo = (value) => {
            if (value === '' || value === null || isNaN(value)) return null;
            const pct = Math.max(0, Math.min(100, Number(value)));
            if (pct >= 90) return { letter: 'A+', cls: 'bg-[#dcfce7] text-[#166534]' };
            if (pct >= 80) return { letter: 'A',  cls: 'bg-[#dcfce7] text-[#166534]' };
            if (pct >= 75) return { letter: 'B+', cls: 'bg-[#dbeafe] text-[#1e40af]' };
            if (pct >= 70) return { letter: 'B',  cls: 'bg-[#dbeafe] text-[#1e40af]' };
            if (pct >= 65) return { letter: 'C+', cls: 'bg-[#fef3c7] text-[#92400e]' };
            if (pct >= 60) return { letter: 'C',  cls: 'bg-[#fef3c7] text-[#92400e]' };
            if (pct >= 50) return { letter: 'D',  cls: 'bg-[#fef3c7] text-[#92400e]' };
            return { letter: 'F', cls: 'bg-[#fee2e2] text-[#ba1a1a]' };
        };

        const recomputeStats = () => {
            const inputs = document.querySelectorAll('.marks-input');
            let graded = 0, total = 0, count = 0;
            inputs.forEach((input) => {
                if (input.value !== '') {
                    graded++;
                    total += Number(input.value);
                    count++;
                }
            });
            const countGraded = document.getElementById('countGraded');
            const classAverage = document.getElementById('classAverage');
            if (countGraded) countGraded.textContent = graded;
            if (classAverage) classAverage.textContent = count > 0 ? (total / count).toFixed(1) + '%' : '—';
        };

        document.querySelectorAll('.marks-input').forEach((input) => {
            input.addEventListener('input', () => {
                let value = input.value === '' ? '' : parseInt(input.value, 10) || 0;
                if (value !== '' && value > 100) value = 100;
                if (value !== '' && value < 0) value = 0;
                input.value = value;

                const row = input.closest('tr');
                const badge = row.querySelector('.grade-badge');
                const info = gradeInfo(value);
                if (badge) {
                    badge.textContent = info ? info.letter : '—';
                    badge.className = 'grade-badge inline-flex px-2.5 py-1 rounded font-label-sm text-label-sm font-bold ' + (info ? info.cls : 'bg-surface-container text-on-surface-variant');
                }
                recomputeStats();
            });
        });
    })();
</script>
@endsection
