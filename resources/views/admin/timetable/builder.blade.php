{{-- resources/views/admin/timetable/builder.blade.php --}}
@extends('layouts.app')

@section('title', 'Timetable Builder')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 mb-4 font-label-md text-label-md text-on-surface-variant">
            <span class="text-on-surface-variant">Dashboard</span>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Timetables</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex flex-col gap-1.5">
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Timetable Builder</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    @if ($schoolClass && $term)
                        Building the weekly schedule for <span class="font-semibold text-on-surface">{{ $schoolClass->display_name }}</span>
                        · {{ $term->name }} ({{ $term->academicYear?->label }})
                    @else
                        Select a class and term below to view or build its weekly schedule.
                    @endif
                </p>
            </div>
        </div>

        @include('admin.partials.flash')

        {{-- Class / term selector --}}
        <form method="GET" action="{{ route('admin.timetable.index') }}"
            class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:max-w-xl">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="class_id">Class</label>
                    <select id="class_id" name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                        @foreach ($classes as $class)
                            <option value="{{ $class->class_id }}" @selected($schoolClass?->class_id === $class->class_id)>{{ $class->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="term_id">Term</label>
                    <select id="term_id" name="term_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                        @foreach ($terms as $option)
                            <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                                {{ $option->name }} ({{ $option->academicYear?->label }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        @if (! $schoolClass || ! $term)
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-12 flex flex-col items-center text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-[28px]">calendar_month</span>
                </div>
                <h3 class="font-title-md text-title-md text-on-surface font-semibold">No class or term selected</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm">
                    Choose a class and a term above to view its timetable, or create one if none exists yet.
                </p>
            </div>
        @elseif ($periods->isEmpty())
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-12 flex flex-col items-center text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-error-container/40 flex items-center justify-center text-on-error-container">
                    <span class="material-symbols-outlined text-[28px]">schedule</span>
                </div>
                <h3 class="font-title-md text-title-md text-on-surface font-semibold">No periods set up for this grade level</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant max-w-sm">
                    Add periods for {{ $schoolClass->gradeLevel?->name ?? 'this grade level' }} first.
                </p>
                <a href="{{ route('admin.periods.create') }}"
                    class="mt-2 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-on-primary font-label-md text-label-md font-semibold hover:opacity-90 transition-opacity shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span> Add a Period
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.timetable.slots.store') }}" id="timetableForm">
                @csrf
                <input type="hidden" name="school_class_id" value="{{ $schoolClass->class_id }}">
                <input type="hidden" name="term_id" value="{{ $term->term_id }}">

                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden mb-4">
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-outline-variant">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                                <span class="material-symbols-outlined text-[22px]">table_chart</span>
                            </div>
                            <div>
                                <h2 class="font-title-md text-title-md text-on-surface font-semibold">Weekly Schedule</h2>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">Pick a subject and teacher for each period, then save once</span>
                            </div>
                        </div>
                        <span id="filledBadge"
                            class="shrink-0 px-2.5 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm font-semibold whitespace-nowrap">
                            0 filled
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                                    <th class="px-4 py-3 font-semibold border-b border-outline-variant whitespace-nowrap">Day</th>
                                    @foreach ($periods as $period)
                                        <th class="px-3 py-3 font-semibold border-b border-outline-variant min-w-[180px]">
                                            {{ $period->name }}
                                            <div class="normal-case font-normal text-on-surface-variant/80 text-[10px] tracking-normal mt-0.5">
                                                {{ $period->start_time?->format('H:i') }}–{{ $period->end_time?->format('H:i') }}
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach ($days as $day)
                                    <tr>
                                        <th class="px-4 py-3 font-label-sm text-label-sm text-on-surface font-semibold bg-surface-container-low/40 whitespace-nowrap align-top">
                                            {{ $day }}
                                        </th>
                                        @foreach ($periods as $period)
                                            @php($slot = $slots->get($day . '-' . $period->id))
                                            <td class="px-2 py-2 align-top border-l border-outline-variant/60">
                                                @if ($period->is_break)
                                                    <div class="h-full min-h-[64px] flex items-center justify-center rounded-lg bg-surface-container-low text-on-surface-variant">
                                                        <span class="font-label-sm text-label-sm uppercase tracking-wider font-semibold">Break</span>
                                                    </div>
                                                @else
                                                    <div class="flex flex-col gap-1.5 tt-cell" data-filled="{{ ($slot?->subject_id) ? 1 : 0 }}">
                                                        <select name="cells[{{ $day }}][{{ $period->id }}][subject_id]"
                                                            class="tt-subject w-full rounded-lg border border-outline-variant px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                                                            <option value="">Free</option>
                                                            @foreach ($subjects as $subject)
                                                                <option value="{{ $subject->subject_id }}"
                                                                    @selected($slot?->subject_id === $subject->subject_id)>{{ $subject->subject_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <select name="cells[{{ $day }}][{{ $period->id }}][teacher_id]"
                                                            class="tt-teacher w-full rounded-lg border border-outline-variant px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                                                            <option value="">Unassigned</option>
                                                            @foreach ($teachers as $teacher)
                                                                <option value="{{ $teacher->teacher_id }}"
                                                                    @selected($slot?->teacher_id === $teacher->teacher_id)>{{ $teacher->full_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Single sticky save action for the whole grid --}}
                <div class="sticky bottom-4 flex items-center justify-end gap-3 mt-4 z-10">
                    <span id="dirtyIndicator" class="hidden font-label-sm text-label-sm text-on-surface-variant items-center gap-1.5 bg-surface-container-lowest border border-outline-variant rounded-full px-3 py-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-tertiary"></span>
                        Unsaved changes
                    </span>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all shadow-lg">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        <span>Save Timetable</span>
                    </button>
                </div>
            </form>

            {{-- Whole-timetable utility actions — deliberately separate from the grid's Save button above --}}
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5 mt-6">
                <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider font-semibold mb-3">Timetable Actions</h3>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <form method="POST" action="{{ route('admin.timetable.copy') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="school_class_id" value="{{ $schoolClass->class_id }}">
                        <input type="hidden" name="source_term_id" value="{{ $term->term_id }}">
                        <select name="target_term_id" required
                            class="rounded-lg border border-outline-variant px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                            <option value="">Copy to term…</option>
                            @foreach ($terms as $option)
                                @if ($option->term_id !== $term->term_id)
                                    <option value="{{ $option->term_id }}">{{ $option->name }} ({{ $option->academicYear?->label }})</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">content_copy</span>
                            Copy
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.timetable.clear') }}"
                        onsubmit="return confirm('Clear the entire timetable for {{ $schoolClass->display_name }} · {{ $term->name }}? This cannot be undone.');">
                        @csrf
                        <input type="hidden" name="school_class_id" value="{{ $schoolClass->class_id }}">
                        <input type="hidden" name="term_id" value="{{ $term->term_id }}">
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-error border border-error/30 hover:bg-error-container/30 transition-colors font-label-md text-label-md">
                            <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
                            Clear Timetable
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </main>

    @push('scripts')
        <script>
            (function () {
                const form = document.getElementById('timetableForm');
                if (! form) return;

                const badge = document.getElementById('filledBadge');
                const dirtyIndicator = document.getElementById('dirtyIndicator');
                const cells = document.querySelectorAll('.tt-cell');

                function updateFilledBadge() {
                    let filled = 0;
                    cells.forEach((cell) => {
                        const subject = cell.querySelector('.tt-subject');
                        if (subject && subject.value !== '') filled++;
                    });
                    if (badge) badge.textContent = `${filled} filled`;
                }

                function markDirty() {
                    if (dirtyIndicator) {
                        dirtyIndicator.classList.remove('hidden');
                        dirtyIndicator.classList.add('inline-flex');
                    }
                }

                form.querySelectorAll('.tt-subject, .tt-teacher').forEach((select) => {
                    select.addEventListener('change', () => {
                        updateFilledBadge();
                        markDirty();
                    });
                });

                form.addEventListener('submit', () => {
                    if (dirtyIndicator) dirtyIndicator.classList.add('hidden');
                });

                updateFilledBadge();
            })();
        </script>
    @endpush
@endsection
