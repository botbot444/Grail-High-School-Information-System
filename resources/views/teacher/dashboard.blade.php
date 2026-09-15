@extends('layouts.teacher')

@section('title', 'Dashboard – Teacher Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- 1. Welcome Header --}}
        <section class="flex flex-col md:flex-row md:items-end justify-between gap-4 bg-surface-container-lowest p-space-lg rounded-xl shadow-sm relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-primary-fixed/30 blur-3xl pointer-events-none"></div>
            <div class="flex flex-col gap-1 z-10">
                <div class="flex items-center gap-2">
                    <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">Academic Session
                        @if ($currentTerm)
                            • {{ $currentTerm->name }}@if($currentTerm->academicYear) · {{ $currentTerm->academicYear->label ?? '' }}@endif
                        @else
                            • In progress
                        @endif
                    </span>
                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">{{ $teacher?->full_name ?? auth()->user()->name }}</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight font-bold">Good {{ now()->format('A') === 'AM' ? 'morning' : 'afternoon' }}, {{ $greetingName }}</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Here's your teaching schedule and pending instructional tasks.</p>
            </div>
            <div class="flex items-center gap-3 z-10 flex-wrap">
                <a href="{{ route('teacher.timetable') }}"
                    class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-surface-container-low hover:bg-surface-container text-on-surface font-title-sm text-title-sm transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    <span>View Timetable</span>
                </a>
                <a href="{{ route('teacher.attendance') }}"
                    class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                    <span>Take Attendance</span>
                </a>
            </div>
        </section>

        {{-- 2. Metric KPI Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">
            {{-- Card 1: Today's Classes --}}
            <div class="flex flex-col justify-between p-space-md rounded-xl bg-surface-container-lowest shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider">My Classes</span>
                        <span class="font-headline-lg text-headline-lg text-primary font-bold mt-1">{{ $assignments->count() }}</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-surface-container-low flex items-center justify-center text-primary group-hover:bg-secondary-fixed transition-colors">
                        <span class="material-symbols-outlined text-[24px]">schedule</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 font-body-sm text-body-sm text-on-surface-variant">
                    @if ($assignments->isNotEmpty())
                        @php $first = $assignments->first(); @endphp
                        <span class="w-2 h-2 rounded-full bg-secondary-container"></span>
                        <span>Includes <strong class="font-title-sm text-on-surface">{{ $first->schoolClass?->class_name ?? 'Class' }}</strong> · {{ $first->subject?->subject_name ?? '' }}</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-outline-variant"></span>
                        <span>No classes rostered yet</span>
                    @endif
                </div>
            </div>

            {{-- Card 2: Students Total --}}
            <div class="flex flex-col justify-between p-space-md rounded-xl bg-surface-container-lowest shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider">Students Total</span>
                        <span class="font-headline-lg text-headline-lg text-primary font-bold mt-1">{{ $totalStudents }}</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-surface-container-low flex items-center justify-center text-secondary group-hover:bg-secondary-fixed transition-colors">
                        <span class="material-symbols-outlined text-[24px]">groups</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 font-body-sm text-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-[16px] text-secondary">class</span>
                    <span>Across {{ $rosteredClasses->count() }} rostered {{ $rosteredClasses->count() === 1 ? 'class' : 'classes' }}</span>
                </div>
            </div>

            {{-- Card 3: Pending Marks --}}
            <div class="flex flex-col justify-between p-space-md rounded-xl bg-surface-container-lowest shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider">Pending Marks</span>
                        <span class="font-headline-lg text-headline-lg text-error font-bold mt-1">{{ $pendingMarks }}</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-error-container flex items-center justify-center text-error group-hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[24px]">assignment_late</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 font-body-sm text-body-sm text-error">
                    <span class="w-2 h-2 rounded-full bg-error"></span>
                    <span class="font-title-sm">{{ $pendingMarks > 0 ? 'Awaiting mark entry' : 'All marks up to date' }}</span>
                </div>
            </div>

            {{-- Card 4: Attendance Today --}}
            <div class="flex flex-col justify-between p-space-md rounded-xl bg-surface-container-lowest shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex flex-col">
                        <span class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider">Attendance Today</span>
                        <span class="font-headline-lg text-headline-lg text-on-surface font-bold mt-1">{{ $attendanceRate }}%</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary group-hover:bg-secondary group-hover:text-on-primary transition-colors">
                        <span class="material-symbols-outlined text-[24px]">how_to_reg</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between font-body-sm text-body-sm">
                    <span class="text-on-surface-variant">{{ $attendancePresent }}/{{ $attendanceTotal }} present</span>
                    <span class="px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface font-label-sm">{{ $attendanceRate >= 90 ? 'High' : ($attendanceRate >= 75 ? 'Medium' : 'Low') }}</span>
                </div>
            </div>
        </section>

        {{-- 3. Two-Column Working Canvas --}}
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-space-lg">
            {{-- Left: Schedule Table --}}
            <div class="lg:col-span-2 flex flex-col bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="px-space-md py-space-md bg-surface-container-low flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary text-[22px]">calendar_view_day</span>
                        <h2 class="font-title-md text-title-md text-primary">Teaching Schedule</h2>
                    </div>
                    <a href="{{ route('teacher.timetable') }}" class="p-1.5 rounded hover:bg-surface-container transition-colors text-on-surface-variant" title="Full Timetable">
                        <span class="material-symbols-outlined text-[18px]">tune</span>
                    </a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left font-body-md text-body-md border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low/50 font-label-sm text-label-sm uppercase text-on-surface-variant">
                                <th class="py-3 px-space-md">Class &amp; Subject</th>
                                <th class="py-3 px-space-sm">Grade</th>
                                <th class="py-3 px-space-sm">Students</th>
                                <th class="py-3 px-space-sm">Status</th>
                                <th class="py-3 px-space-md text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignments as $asn)
                                <tr class="hover:bg-surface-container-low/40 transition-colors">
                                    <td class="py-3.5 px-space-md">
                                        <div class="flex flex-col">
                                            <span class="font-title-sm text-title-sm text-on-surface">{{ $asn->subject?->subject_name ?? 'Subject' }}</span>
                                            <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $asn->schoolClass?->display_name ?? $asn->schoolClass?->class_name ?? '' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-space-sm font-data-mono text-data-mono text-on-surface whitespace-nowrap">{{ $asn->schoolClass?->gradeLevel?->name ?? $asn->schoolClass?->grade_level ?? '—' }}</td>
                                    <td class="py-3.5 px-space-sm font-data-mono text-data-mono text-on-surface">{{ $classSizes[$asn->class_id] ?? 0 }}</td>
                                    <td class="py-3.5 px-space-sm">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container text-on-surface-variant">
                                            Rostered
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-space-md text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('teacher.performance') }}"
                                                class="px-2.5 py-1 rounded bg-surface-container-low hover:bg-surface-container text-on-surface font-title-sm text-[12px] transition-colors">Performance</a>
                                            <a href="{{ route('teacher.classes') }}"
                                                class="px-2.5 py-1 rounded bg-surface-container-low hover:bg-surface-container text-on-surface font-title-sm text-[12px] transition-colors">Roster</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-on-surface-variant font-body-md text-body-md">
                                        No classes assigned yet. Contact the administrator to be rostered.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-space-md py-space-sm bg-surface-container-low/60 flex items-center justify-between text-on-surface-variant">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-secondary">info</span>
                        <span class="font-body-sm text-body-sm">{{ $assignments->count() }} rostered teaching assignment{{ $assignments->count() === 1 ? '' : 's' }} · {{ $totalStudents }} students</span>
                    </div>
                    <a href="{{ route('teacher.timetable') }}" class="font-title-sm text-title-sm text-secondary hover:underline flex items-center gap-1">
                        <span>Full Timetable</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </div>

            {{-- Right: Quick Actions & Activity --}}
            <div class="flex flex-col gap-space-lg">
                {{-- Quick Actions --}}
                <div class="flex flex-col p-space-md rounded-xl bg-surface-container-lowest shadow-sm">
                    <div class="flex items-center justify-between mb-space-sm">
                        <h3 class="font-title-md text-title-md text-primary">Quick Actions</h3>
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Shortcuts</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('teacher.attendance') }}" class="flex flex-col items-start p-space-sm rounded-lg bg-surface-container-low hover:bg-secondary-fixed transition-colors text-left group">
                            <span class="material-symbols-outlined text-secondary text-[22px] mb-1 group-hover:scale-105 transition-transform">how_to_reg</span>
                            <span class="font-title-sm text-title-sm text-on-surface leading-tight">Take Attendance</span>
                            <span class="font-body-sm text-body-sm text-on-surface-variant text-[11px] mt-0.5">Mark daily presence</span>
                        </a>
                        <a href="{{ route('teacher.marks') }}" class="flex flex-col items-start p-space-sm rounded-lg bg-surface-container-low hover:bg-secondary-fixed transition-colors text-left group">
                            <span class="material-symbols-outlined text-secondary text-[22px] mb-1 group-hover:scale-105 transition-transform">edit_note</span>
                            <span class="font-title-sm text-title-sm text-on-surface leading-tight">Enter Marks</span>
                            <span class="font-body-sm text-body-sm text-on-surface-variant text-[11px] mt-0.5">Post quiz &amp; exams</span>
                        </a>
                        <a href="{{ route('teacher.performance') }}" class="flex flex-col items-start p-space-sm rounded-lg bg-surface-container-low hover:bg-secondary-fixed transition-colors text-left group">
                            <span class="material-symbols-outlined text-secondary text-[22px] mb-1 group-hover:scale-105 transition-transform">query_stats</span>
                            <span class="font-title-sm text-title-sm text-on-surface leading-tight">Performance</span>
                            <span class="font-body-sm text-body-sm text-on-surface-variant text-[11px] mt-0.5">Cohort analytics</span>
                        </a>
                        <a href="{{ route('teacher.report-cards.index') }}" class="flex flex-col items-start p-space-sm rounded-lg bg-surface-container-low hover:bg-secondary-fixed transition-colors text-left group">
                            <span class="material-symbols-outlined text-secondary text-[22px] mb-1 group-hover:scale-105 transition-transform">description</span>
                            <span class="font-title-sm text-title-sm text-on-surface leading-tight">Report Cards</span>
                            <span class="font-body-sm text-body-sm text-on-surface-variant text-[11px] mt-0.5">Finalize &amp; comment</span>
                        </a>
                    </div>
                </div>

                {{-- Recent Student Activity --}}
                <div class="flex flex-col p-space-md rounded-xl bg-surface-container-lowest shadow-sm flex-1">
                    <div class="flex items-center justify-between mb-space-sm">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary text-[20px]">notifications_active</span>
                            <h3 class="font-title-md text-title-md text-primary">Recent Activity</h3>
                        </div>
                        <a href="{{ route('teacher.marks') }}" class="text-on-surface-variant hover:text-on-surface transition-colors" title="Enter marks">
                            <span class="material-symbols-outlined text-[18px]">edit_note</span>
                        </a>
                    </div>
                    <div class="flex flex-col gap-3">
                        @forelse ($recentActivity as $activity)
                            <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-surface-container-low transition-colors">
                                <div class="w-8 h-8 rounded-full {{ $activity['avatarBg'] }} flex items-center justify-center shrink-0 font-title-sm text-[11px] {{ $activity['avatarText'] }}">
                                    {{ $activity['initials'] }}
                                </div>
                                <div class="flex flex-col flex-1 min-w-0">
                                    <p class="font-body-sm text-body-sm text-on-surface line-clamp-2">
                                        <strong class="font-title-sm text-on-surface">{{ $activity['student'] }}</strong>
                                        {{ $activity['message'] }}
                                    </p>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant mt-0.5">{{ $activity['meta'] }}</span>
                                </div>
                                <span class="material-symbols-outlined {{ $activity['iconColor'] }} text-[16px]">{{ $activity['icon'] }}</span>
                            </div>
                        @empty
                            <p class="py-6 text-center font-body-sm text-body-sm text-on-surface-variant">No recent activity recorded.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('teacher.marks') }}" class="mt-3 w-full py-1.5 text-center font-label-md text-label-md text-secondary hover:bg-surface-container-low rounded transition-colors">
                        Go to mark entry
                    </a>
                </div>
            </div>
        </section>

        {{-- 4. Bottom Row (Tasks & Events) --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg mb-4">
            {{-- Pending Tasks --}}
            <div class="flex flex-col bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="px-space-md py-space-md bg-surface-container-low flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-error text-[20px]">pending_actions</span>
                        <h3 class="font-title-md text-title-md text-primary">Pending Instructional Tasks</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-error-container text-error font-label-sm text-label-sm font-bold">{{ $pendingTasks->count() }} to review</span>
                </div>
                <div class="p-space-md flex flex-col gap-3">
                    @forelse ($pendingTasks as $task)
                        <a href="{{ $task['href'] }}"
                            class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low/60 hover:bg-surface-container-low transition-colors gap-3">
                            <div class="flex flex-col min-w-0">
                                <span class="font-title-sm text-title-sm text-on-surface truncate">{{ $task['title'] }}</span>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $task['subtitle'] }}</span>
                            </div>
                            <span class="px-2.5 py-1 rounded font-label-sm text-label-sm {{ $task['badgeClass'] }} shrink-0">{{ $task['badge'] }}</span>
                        </a>
                    @empty
                        <p class="py-6 text-center font-body-sm text-body-sm text-on-surface-variant">No pending tasks — all caught up!</p>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div class="flex flex-col bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="px-space-md py-space-md bg-surface-container-low flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary text-[20px]">event_upcoming</span>
                        <h3 class="font-title-md text-title-md text-primary">Upcoming School Events</h3>
                    </div>
                </div>
                <div class="p-space-md flex flex-col gap-3">
                    @forelse ($upcomingEvents as $event)
                        <div class="flex items-center gap-4 p-3 rounded-lg bg-surface-container-low/60 hover:bg-surface-container-low transition-colors">
                            <div class="flex flex-col items-center justify-center w-12 h-12 rounded-lg bg-surface-container-lowest shadow-xs shrink-0">
                                <span class="font-label-sm text-[10px] uppercase {{ $event['monthColor'] }} font-bold leading-none">{{ $event['month'] }}</span>
                                <span class="font-headline-sm text-headline-sm text-primary font-bold leading-none mt-0.5">{{ $event['day'] }}</span>
                            </div>
                            <div class="flex flex-col flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-title-sm text-title-sm text-primary truncate">{{ $event['title'] }}</span>
                                    @if ($event['tag'])
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-label-sm bg-secondary-fixed text-on-secondary-fixed font-bold">{{ $event['tag'] }}</span>
                                    @endif
                                </div>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $event['detail'] }}</span>
                            </div>
                            <span class="material-symbols-outlined {{ $event['iconColor'] }} text-[20px]">{{ $event['icon'] }}</span>
                        </div>
                    @empty
                        <p class="py-6 text-center font-body-sm text-body-sm text-on-surface-variant">No upcoming events scheduled.</p>
                    @endforelse
                </div>
            </div>
        </section>

    </div>
@endsection