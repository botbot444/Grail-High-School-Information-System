@extends('layouts.teacher')

@section('title', 'My Timetable - Teacher Portal')

@php
    // Dates for the current Monday-Friday week shown in the day headers.
    $dow = (int) now()->format('w');  // 1=Mon..7=Sun so Monday is w=1
    $weekStart = now()->subDays($dow - 1)->startOfDay();
    $weekDates = [];
    foreach ($days as $idx => $d) {
        $weekDates[$d] = $weekStart->addDays($idx);
    }
    $todayName = now()->format('l');
@endphp

@section('page')
    {{-- Top Command & Action Bar --}}
    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-space-md bg-surface-container-lowest p-space-lg rounded-xl shadow-sm mb-space-lg">
        <div class="flex flex-col gap-1 min-w-0">
            <div class="flex items-center gap-space-sm">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-secondary text-on-primary">
                    <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                </span>
                <h1 class="font-headline-md text-headline-md text-on-surface tracking-tight">My Timetable</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed font-label-sm text-label-sm font-semibold">Week {{ $weekStart->weekOfYear }}</span>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant flex items-center gap-1.5 pl-10">
                <span>Week of {{ $weekStart->format('M j') }} - {{ $weekStart->addDays(4)->format('M j, Y') }}</span>
                <span class="w-1.5 h-1.5 rounded-full bg-outline-variant"></span>
                <span class="font-title-sm text-title-sm text-secondary">{{ $term?->name ?? 'Current Term' }}@if($term?->academicYear) · {{ $term->academicYear->label }}@endif</span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-space-sm">
            <div class="inline-flex items-center bg-surface-container-low rounded-lg p-1">
                <button aria-label="Previous Week" class="w-8 h-8 flex items-center justify-center rounded text-on-surface-variant hover:bg-surface-container-lowest hover:text-on-surface transition-all"><span class="material-symbols-outlined text-[18px]">chevron_left</span></button>
                <button class="px-3 py-1 font-label-md text-label-md text-on-surface hover:text-secondary flex items-center gap-1.5 transition-colors"><span class="material-symbols-outlined text-[16px] text-secondary">today</span><span>Current Week</span></button>
                <button aria-label="Next Week" class="w-8 h-8 flex items-center justify-center rounded text-on-surface-variant hover:bg-surface-container-lowest hover:text-on-surface transition-all"><span class="material-symbols-outlined text-[18px]">chevron_right</span></button>
            </div>
            {{-- Term selector (re-skins the existing functional selector) --}}
            <div class="relative">
                <form method="GET" action="{{ route('teacher.timetable') }}" class="inline-flex items-center">
                    <select name="term_id" onchange="this.form.submit()" class="h-10 pl-3 pr-8 appearance-none bg-surface-container-low text-on-surface font-label-md text-label-md rounded-lg cursor-pointer border-none focus:outline-none">
                        @foreach ($terms as $option)
                            <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>{{ $option->name }} ({{ $option->academicYear?->label }})</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-on-surface-variant text-[16px] pointer-events-none">unfold_more</span>
                </form>
            </div>
            <div class="flex items-center gap-space-xs">
                <button class="h-10 px-3.5 bg-surface-container hover:bg-surface-container-high text-on-surface font-title-sm text-title-sm rounded-lg flex items-center gap-2 transition-all shadow-sm" onclick="window.print()"><span class="material-symbols-outlined text-[18px]">print</span><span class="hidden sm:inline">Print</span></button>
            </div>
        </div>
    </div>

    @forelse ($classes as $card)
        @php
            $class = $card['class']; $periods = $card['periods']; $slots = $card['slots']; $dayCounts = $card['dayCounts'];
            $teachingPeriodCount = $periods->where('is_break', false)->count();
        @endphp
        <div class="w-full bg-surface-container-lowest rounded-xl shadow-sm p-space-md flex flex-col gap-space-md mb-space-xl">
            {{-- Class header for this grid --}}
            <div class="flex items-center gap-2 px-space-sm pb-space-sm">
                <span class="material-symbols-outlined text-secondary text-[20px]">class</span>
                <h2 class="font-title-md text-title-md text-on-surface">{{ $class->display_name }}</h2>
                <span class="px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed font-data-sm text-data-sm font-bold">{{ $card['roster'] }} students</span>
            </div>

            {{-- Matrix Header Bar --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between px-space-sm pb-space-sm gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-secondary-container animate-pulse"></span>
                    <span class="font-title-sm text-title-sm text-on-surface">{{ $term?->name ?? 'Current' }} Timetable</span>
                </div>
                <div class="flex items-center gap-space-sm">
                    <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">Today: {{ $todayName }}</span>
                    <span class="px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed font-data-sm text-data-sm font-bold">{{ $teachingPeriodCount }} PERIODS</span>
                </div>
            </div>

            {{-- Grid --}}
            <div class="w-full overflow-x-auto">
                <div class="min-w-[960px] flex flex-col gap-2">
                    {{-- Days header row --}}
                    <div class="grid grid-cols-[130px_repeat(5,1fr)] gap-3 items-center">
                        <div class="px-3 py-2 bg-surface-container-low rounded-lg flex flex-col justify-center"><span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">Time Slot</span><span class="font-data-sm text-data-sm text-on-surface-variant">{{ $teachingPeriodCount }} Periods</span></div>
                        @foreach ($days as $day)
                            @php $date = $weekDates[$day]; $isToday = $day === $todayName; $dayCount = $dayCounts[$day] ?? 0; @endphp
                            <div class="{{ $isToday ? 'p-3 bg-primary-container text-on-primary rounded-lg flex items-center justify-between shadow-md' : 'p-3 bg-surface-container-low rounded-lg flex items-center justify-between' }}">
                                <div class="flex flex-col">
                                    <span class="font-title-sm text-title-sm {{ $isToday ? 'text-on-primary' : 'text-on-surface' }}">{{ $day }}</span>
                                    <span class="font-label-sm text-label-sm {{ $isToday ? 'text-on-primary-container' : 'text-on-surface-variant' }} {{ $isToday ? 'font-semibold' : '' }}">{{ $date->format('M j') }}@if($isToday) (Today)@endif</span>
                                </div>
                                <span class="font-data-sm text-data-sm px-2 py-0.5 rounded {{ $isToday ? 'bg-tertiary-container text-on-primary' : 'bg-surface-container-highest text-on-surface-variant' }}">{{ $dayCount }} {{ $dayCount === 1 ? 'Class' : 'Classes' }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Period rows --}}
                    @foreach ($periods as $period)
                        @php $isBreak = (bool) $period->is_break; @endphp
                        @if ($isBreak)
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <div class="bg-surface-container-high/80 p-2.5 rounded-xl flex items-center justify-between"><span class="font-data-sm text-data-sm font-semibold text-on-surface">{{ $period->start_time?->format('H:i') }}{{ $period->end_time ? ' - '.$period->end_time->format('H:i') : '' }}</span><span class="material-symbols-outlined text-[16px] text-outline">restaurant</span></div>
                                <div class="bg-surface-container-high/60 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-sm gap-3">
                                    <span class="flex items-center gap-2 text-on-surface-variant font-body-sm text-body-sm"><span class="material-symbols-outlined text-[16px]">lunch_dining</span>{{ $period->name ?? 'Break' }} - Designated break &amp; office hours</span>
                                    <span class="font-label-sm text-label-sm text-on-surface px-2 py-0.5 rounded bg-surface-container-high">No classes</span>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-[130px_repeat(5,1fr)] gap-3 min-h-[118px]">
                                <div class="bg-surface-container-low p-3 rounded-xl flex flex-col justify-between">
                                    <div class="flex flex-col">
                                        <span class="font-data-md text-data-md font-semibold text-on-surface">{{ $period->start_time?->format('g:i A') }}</span>
                                        <span class="font-data-sm text-data-sm text-outline">{{ $period->end_time?->format('g:i A') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-on-surface-variant">
                                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                                        <span class="font-label-sm text-label-sm">{{ $period->name ?? 'Period' }}</span>
                                    </div>
                                </div>
                                @foreach ($days as $day)
                                    @php $slot = $slots->get($day.'-'.$period->id); @endphp
                                    @if ($slot && $slot->subject && $slot->isMine)
                                        <div class="group relative bg-primary-container text-on-primary p-3.5 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden cursor-pointer">
                                            <div class="flex items-start justify-between gap-1">
                                                <div class="flex flex-col min-w-0"><span class="font-label-sm text-label-sm tracking-wider uppercase text-secondary-container font-semibold">{{ $slot->subject->subject_name }}</span><span class="font-title-sm text-title-sm text-on-primary truncate">{{ $class->grade_level_name }} · {{ $class->class_name }}</span></div>
                                                <span class="material-symbols-outlined text-[18px] text-secondary-container">{{ $slot->icon ?? 'school' }}</span>
                                            </div>
                                            <div class="flex items-center justify-between text-on-primary-container font-data-sm text-data-sm pt-2">
                                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">group</span>{{ $card['roster'] }} Stud.</span>
                                                <span class="font-label-sm text-label-sm text-on-primary font-semibold">{{ $teacher?->first_name ?? '' }}</span>
                                            </div>
                                        </div>
                                    @elseif ($slot && $slot->subject)
                                        {{-- Another teacher's lesson — the class isn't free, it just isn't mine. --}}
                                        <div class="group relative bg-surface-container text-on-surface p-3.5 rounded-xl shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden cursor-pointer border border-outline-variant/60">
                                            <div class="flex items-start justify-between gap-1">
                                                <div class="flex flex-col min-w-0"><span class="font-label-sm text-label-sm tracking-wider uppercase text-outline font-semibold">{{ $slot->subject->subject_name }}</span><span class="font-title-sm text-title-sm text-on-surface truncate">{{ $class->grade_level_name }} · {{ $class->class_name }}</span></div>
                                                <span class="material-symbols-outlined text-[18px] text-outline">{{ $slot->icon ?? 'school' }}</span>
                                            </div>
                                            <div class="flex items-center justify-between text-on-surface-variant font-data-sm text-data-sm pt-2">
                                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">group</span>{{ $card['roster'] }} Stud.</span>
                                                <span class="font-label-sm text-label-sm text-on-surface-variant font-semibold truncate">{{ $slot->teacher?->first_name ?? 'Other teacher' }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="group bg-surface-container-low/70 rounded-xl p-3.5 flex flex-col justify-between hover:bg-surface-container-high transition-all cursor-pointer">
                                            <div class="flex items-start justify-between">
                                                <div class="flex flex-col"><span class="font-label-sm text-label-sm uppercase tracking-wider text-outline font-semibold">Unassigned</span><span class="font-title-sm text-title-sm text-on-surface-variant">Free Period</span></div>
                                                <span class="material-symbols-outlined text-[18px] text-outline">hourglass_empty</span>
                                            </div>
                                            <div class="flex items-center justify-between pt-2 text-outline font-label-md text-label-md"><span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">add_circle</span><span>+ Book Room</span></span></div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        {{-- Empty state: teacher has no slots assigned this term --}}
        <div class="flex flex-col items-center justify-center text-center p-space-xl bg-surface-container-lowest rounded-xl shadow-sm my-space-lg">
            <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center text-outline mb-space-md"><span class="material-symbols-outlined text-[42px]">calendar_view_week</span></div>
            <h3 class="font-headline-sm text-headline-sm text-primary mb-1">No Timetable Assigned Yet</h3>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-space-lg">Your weekly schedule for {{ $term?->name ?? 'this term' }} hasn't been built yet. Once the registrar drafts your class slots, your teaching timetable will appear here.</p>
            <div class="flex items-center gap-space-sm">
                @if ($terms->count() > 1)
                    <span class="font-label-md text-label-md text-on-surface-variant">Looking for a past term?</span>
                @endif
                <a class="px-space-md py-2 rounded bg-secondary hover:bg-on-secondary-container text-on-primary font-label-md text-label-md transition-colors flex items-center gap-1 shadow-sm" href="mailto:registrar@grail.edu"><span class="material-symbols-outlined text-[18px]">support_agent</span><span>Contact Registrar</span></a>
            </div>
        </div>
    @endforelse

    @if ($classes->isNotEmpty())
        {{-- Bottom Area: Subject Legend & Workload Summary Bento --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-md">
            <div class="lg:col-span-5 bg-surface-container-lowest p-space-lg rounded-xl shadow-sm flex flex-col justify-between gap-space-md">
                <div class="flex flex-col gap-1">
                    <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">Classification</span>
                    <h2 class="font-title-md text-title-md text-on-surface">Subject &amp; Activity Legend</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @forelse ($legend as $entry)
                        <div class="flex items-center gap-2.5 p-2 rounded-lg bg-primary-container text-on-primary">
                            <span class="material-symbols-outlined text-secondary-container text-[18px]">{{ $entry['icon'] }}</span>
                            <div class="flex flex-col min-w-0"><span class="font-title-sm text-title-sm leading-tight truncate">{{ $entry['subject'] }}</span><span class="font-data-sm text-data-sm text-on-primary-container">{{ $entry['periods'] }} period{{ $entry['periods'] === 1 ? '' : 's' }} / wk</span></div>
                        </div>
                    @empty
                        <div class="flex items-center gap-2.5 p-2 rounded-lg bg-surface-container-low text-on-surface-variant"><span class="material-symbols-outlined text-[18px]">hourglass_empty</span><span class="font-title-sm text-title-sm text-on-surface">No subjects assigned</span></div>
                    @endforelse
                </div>
                <div class="p-3 rounded-lg bg-surface-container-low flex items-center justify-between text-on-surface-variant">
                    <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-secondary">pin_drop</span><span class="font-label-md text-label-md">{{ $metrics['taught'] }} taught blocks · {{ $metrics['prep'] }} free blocks this week</span></div>
                </div>
            </div>
            <div class="lg:col-span-7 flex flex-col gap-space-md">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-md">
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between text-on-surface-variant"><span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">Weekly Load</span><span class="material-symbols-outlined text-[20px] text-secondary">timelapse</span></div>
                        <div class="flex flex-col pt-3"><span class="font-headline-lg text-headline-lg font-data-lg text-on-surface leading-tight">{{ $metrics['hours'] }}<span class="font-body-sm">h</span></span><span class="font-body-sm text-body-sm text-on-surface-variant">Total Teaching Hours</span></div>
                        <div class="w-full bg-surface-container-high h-1.5 rounded-full overflow-hidden mt-3"><div class="bg-secondary h-full" style="width: {{ min($metrics['hours_pct'],100) }}%"></div></div>
                    </div>
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between text-on-surface-variant"><span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">Roster</span><span class="material-symbols-outlined text-[20px] text-secondary">groups</span></div>
                        <div class="flex flex-col pt-3"><span class="font-headline-lg text-headline-lg font-data-lg text-on-surface leading-tight">{{ $metrics['classes'] }}</span><span class="font-body-sm text-body-sm text-on-surface-variant">Rostered Classes</span></div>
                        <span class="font-label-sm text-label-sm text-secondary font-semibold mt-3">{{ $metrics['students'] }} Active Students</span>
                    </div>
                    <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between text-on-surface-variant"><span class="font-label-sm text-label-sm uppercase tracking-wider text-outline">Free Blocks</span><span class="material-symbols-outlined text-[20px] text-secondary">science</span></div>
                        <div class="flex flex-col pt-3"><span class="font-headline-lg text-headline-lg font-data-lg text-on-surface leading-tight">{{ $metrics['prep'] }}</span><span class="font-body-sm text-body-sm text-on-surface-variant">Planning &amp; Prep</span></div>
                    </div>
                </div>
                @if ($upNext)
                    @php $next = $upNext['slot']; $nextClass = $upNext['class']; @endphp
                    <div class="bg-primary text-on-primary p-space-lg rounded-xl shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-md relative overflow-hidden">
                        <div class="absolute right-0 top-0 bottom-0 w-48 bg-gradient-to-l from-secondary/30 to-transparent pointer-events-none"></div>
                        <div class="flex items-center gap-space-md min-w-0 z-10">
                            <div class="w-12 h-12 rounded-xl bg-secondary flex items-center justify-center text-on-primary flex-shrink-0 shadow-sm"><span class="material-symbols-outlined text-[26px]">alarm_on</span></div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2"><span class="px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold uppercase">Up Next</span><span class="font-label-sm text-label-sm text-on-primary-container">{{ $upNext['day'] }} at {{ $next->period?->start_time?->format('g:i A') }}</span></div>
                                <h3 class="font-title-md text-title-md text-on-primary truncate">{{ $nextClass->display_name }} · {{ $next->subject?->subject_name }}</h3>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 z-10 w-full sm:w-auto justify-end">
                            <a class="px-3.5 py-2 rounded-lg bg-surface-container-highest/20 hover:bg-surface-container-highest/30 text-on-primary font-title-sm text-title-sm transition-all flex items-center gap-1.5" href="{{ route('teacher.performance') }}"><span class="material-symbols-outlined text-[16px]">menu_book</span><span>Lesson Plan</span></a>
                            <a class="px-3.5 py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm transition-all flex items-center gap-1.5 shadow-sm" href="{{ route('teacher.attendance') }}"><span class="material-symbols-outlined text-[16px]">checklist</span><span>Attendance</span></a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Clicking a class block opens a details modal --}}
    <div class="fixed inset-0 bg-primary/40 backdrop-blur-xs z-50 hidden items-center justify-center p-4" id="timetableModal">
        <div class="bg-surface-container-lowest max-w-lg w-full rounded-xl shadow-xl p-space-lg flex flex-col gap-space-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="material-symbols-outlined text-secondary text-[24px]">event</span><h3 class="font-headline-sm text-headline-sm text-on-surface" id="modalTitle">Class Details</h3></div>
                <button class="w-8 h-8 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant" id="closeModalBtn"><span class="material-symbols-outlined text-[20px]">close</span></button>
            </div>
            <div class="flex flex-col gap-space-sm bg-surface-container-low p-space-md rounded-lg">
                <div class="flex justify-between items-center"><span class="font-label-md text-label-md text-outline">Class &amp; Section:</span><span class="font-title-sm text-title-sm text-on-surface" id="modalClass">—</span></div>
                <div class="flex justify-between items-center"><span class="font-label-md text-label-md text-outline">Enrolled:</span><span class="font-data-md text-data-md text-on-surface" id="modalRoster">—</span></div>
            </div>
            <div class="flex items-center justify-end gap-space-sm pt-2">
                <button class="px-4 py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-title-sm text-title-sm transition-all" id="cancelModalBtn">Close</button>
                <button class="px-4 py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm transition-all flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">edit_calendar</span><span>Reschedule / Substitute</span></button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const modal = document.getElementById('timetableModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const cancelModalBtn = document.getElementById('cancelModalBtn');

            const classBlocks = document.querySelectorAll('[class*=bg-primary-container]');
            classBlocks.forEach(block => {
                block.addEventListener('click', () => {
                    const titleEl = block.querySelector('.font-title-sm');
                    const rosterEl = block.querySelector('.font-data-sm');
                    if (modal) {
                        if (titleEl) document.getElementById('modalClass').textContent = titleEl.textContent;
                        if (rosterEl) document.getElementById('modalRoster').textContent = rosterEl.textContent.trim();
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }
                });
            });

            const hideModal = () => {
                if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
            };
            if (closeModalBtn) closeModalBtn.addEventListener('click', hideModal);
            if (cancelModalBtn) cancelModalBtn.addEventListener('click', hideModal);
            if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });
        })();
    </script>
@endsection