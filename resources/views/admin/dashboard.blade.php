@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[88px]" id="mainContent">
            <div class="p-8 space-y-6">

                {{-- ── Welcome Header ── --}}
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">
                            Institutional Overview
                        </h1>
                        <p class="text-on-surface-variant text-sm mt-1">
                            Real-time performance metrics and administrative insights for Academic Year 2023-24.
                        </p>
                    </div>
                    <div class="flex gap-2 bg-surface-container p-1 rounded-lg">
                        <button
                            class="px-4 py-1.5 text-xs font-semibold rounded bg-white shadow-sm text-primary">Daily</button>
                        <button
                            class="px-4 py-1.5 text-xs font-semibold rounded text-on-surface-variant hover:text-on-surface transition-colors">Weekly</button>
                        <button
                            class="px-4 py-1.5 text-xs font-semibold rounded text-on-surface-variant hover:text-on-surface transition-colors">Monthly</button>
                    </div>
                </div>

                {{-- ── BENTO GRID: Statistics Cards ── --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    {{-- Card 1: Total Students --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">groups</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:14px">trending_up</span> 12%
                            </span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Students</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalStudents }}</p>
                    </div>

                    {{-- Card 2: Total Teachers --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-secondary-container/20 rounded-lg flex items-center justify-center text-secondary">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">person_pin</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-on-surface-variant bg-surface-container px-2 py-0.5 rounded flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:14px">horizontal_rule</span> 0%
                            </span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Teachers</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalStaff }}</p>
                    </div>

                    {{-- Card 3: Total Parents --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-tertiary-fixed-dim/20 rounded-lg flex items-center justify-center text-tertiary">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">family_restroom</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:14px">trending_up</span> 4.2%
                            </span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Parents</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalParents ?? '—' }}</p>
                    </div>

                    {{-- Card 4: Total Classes --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-secondary-fixed/20 rounded-lg flex items-center justify-center text-on-secondary-container">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">meeting_room</span>
                            </div>
                            <span
                                class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size:14px">trending_down</span> 2
                            </span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Classes</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalClasses ?? '—' }}</p>
                    </div>

                    {{-- Card 5: Total Subjects --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-surface-container-highest rounded-lg flex items-center justify-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">book</span>
                            </div>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Subjects</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalSubjects ?? '—' }}</p>
                    </div>

                    {{-- Card 6: Attendance Rate --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-primary-fixed/30 rounded-lg flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">how_to_reg</span>
                            </div>
                            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded">High</span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Attendance Rate</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">94.2%</p>
                    </div>

                    {{-- Card 7: Active Users --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-on-tertiary-fixed-variant/10 rounded-lg flex items-center justify-center text-on-tertiary-fixed-variant">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">wifi_tethering</span>
                            </div>
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full bg-blue-100 border border-white"></div>
                                <div class="w-6 h-6 rounded-full bg-blue-200 border border-white"></div>
                            </div>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Active Users</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">124</p>
                    </div>

                    {{-- Card 8: Graduation Rate --}}
                    <div
                        class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200 group">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-error-container/20 rounded-lg flex items-center justify-center text-error">
                                <span class="material-symbols-outlined text-2xl"
                                    style="font-variation-settings:'FILL' 1">verified</span>
                            </div>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Graduation Rate</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">98.5%</p>
                    </div>

                </div>

                {{-- ── ANALYTICS SECTION ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Recently Edited Teachers (2/3 width) --}}
                    <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-base font-bold text-on-surface">Recently Edited Teachers</h3>
                            <a href="{{ route('admin.teachers.index') }}"
                                class="text-primary text-xs font-semibold hover:underline">View All</a>
                        </div>
                        <div class="divide-y divide-surface-container-high">
                            @forelse($recentTeachers ?? [] as $teacher)
                                <div
                                    class="py-3 flex items-center justify-between hover:bg-surface-container-low transition-colors rounded px-2">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center font-bold text-primary text-sm">
                                            {{ strtoupper(substr($teacher->first_name, 0, 1)) }}{{ strtoupper(substr($teacher->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-on-surface">{{ $teacher->full_name }}</p>
                                            <p class="text-xs text-on-surface-variant">
                                                {{ $teacher->classSubjects->first()?->subject?->subject_name ?? 'Teacher' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs text-on-surface-variant">{{ $teacher->updated_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                {{-- Fallback static rows --}}
                                @foreach ([['initials' => 'SJ', 'name' => 'Dr. Sarah Jenkins', 'subject' => 'Advanced Physics', 'time' => '2 mins ago', 'color' => 'bg-blue-50 text-primary'], ['initials' => 'MC', 'name' => 'Michael Chen', 'subject' => 'World History', 'time' => '15 mins ago', 'color' => 'bg-secondary-container/20 text-secondary'], ['initials' => 'ER', 'name' => 'Elena Rodriguez', 'subject' => 'Creative Writing', 'time' => '45 mins ago', 'color' => 'bg-tertiary-fixed-dim/20 text-tertiary'], ['initials' => 'DW', 'name' => 'David Wilson', 'subject' => 'Mathematics', 'time' => '1 hour ago', 'color' => 'bg-blue-50 text-primary'], ['initials' => 'AL', 'name' => 'Amanda Lee', 'subject' => 'Biology', 'time' => '3 hours ago', 'color' => 'bg-secondary-container/20 text-secondary']] as $row)
                                    <div
                                        class="py-3 flex items-center justify-between hover:bg-gray-50 transition-colors rounded px-2">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full {{ $row['color'] }} flex items-center justify-center font-bold text-sm">
                                                {{ $row['initials'] }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-on-surface">{{ $row['name'] }}</p>
                                                <p class="text-xs text-on-surface-variant">{{ $row['subject'] }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-on-surface-variant">{{ $row['time'] }}</span>
                                    </div>
                                @endforeach
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Actions + Announcements (1/3 width) --}}
                    <div class="space-y-6">

                        {{-- Quick Actions --}}
                        <div
                            class="bg-primary-container text-on-primary-container p-6 rounded-xl shadow-lg relative overflow-hidden">
                            <div class="relative z-10">
                                <h3 class="text-base font-bold mb-4">Quick Actions</h3>
                                <div class="space-y-2">
                                    <a href="{{ route('admin.students.create') }}"
                                        class="w-full py-2.5 px-4 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-between transition-all text-xs font-semibold">
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-lg">person_add</span>
                                            Add Student
                                        </span>
                                        <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                                    </a>
                                    <a href="{{ route('admin.teachers.index') }}"
                                        class="w-full py-2.5 px-4 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-between transition-all text-xs font-semibold">
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-lg">manage_accounts</span>
                                            Manage Teachers
                                        </span>
                                        <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                                    </a>
                                    <a href="{{ route('admin.classes.index') }}"
                                        class="w-full py-2.5 px-4 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-between transition-all text-xs font-semibold">
                                        <span class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-lg">summarize</span>
                                            Classes &amp; Subjects
                                        </span>
                                        <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                                    </a>
                                </div>
                            </div>
                            <div class="absolute -right-4 -bottom-4 opacity-10">
                                <span class="material-symbols-outlined text-[120px]"
                                    style="font-variation-settings:'FILL' 1">bolt</span>
                            </div>
                        </div>

                        {{-- Announcements --}}
                        <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                            <h3 class="text-base font-bold text-on-surface mb-4">School Announcements</h3>
                            <div
                                class="p-4 bg-red-50 border border-red-200 rounded-lg cursor-pointer hover:bg-red-100 transition-all">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-red-600"
                                        style="font-variation-settings:'FILL' 1">warning</span>
                                    <div>
                                        <p class="text-sm font-bold text-red-800">Campus Safety Policy Update</p>
                                        <p class="text-xs text-on-surface-variant mt-1">Effective immediately: All
                                            personnel must update digital badges.</p>
                                        <p class="text-[10px] text-red-600 font-semibold mt-2 uppercase">Priority: High</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── LOWER DATA GRID: Charts ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Attendance Trend Bar Chart --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <h3 class="text-base font-bold text-on-surface mb-6">Attendance Trend (Daily Average)</h3>
                        <div
                            class="flex items-end justify-between h-48 gap-2 pb-6 px-2 border-b border-surface-container-high">
                            <div class="w-full bg-secondary-container rounded-t-sm transition-all hover:opacity-80"
                                style="height:85%"></div>
                            <div class="w-full bg-secondary-container rounded-t-sm transition-all hover:opacity-80"
                                style="height:92%"></div>
                            <div class="w-full bg-secondary-container rounded-t-sm transition-all hover:opacity-80"
                                style="height:78%"></div>
                            <div class="w-full bg-primary rounded-t-sm transition-all hover:opacity-80"
                                style="height:94%"></div>
                            <div class="w-full bg-secondary-container rounded-t-sm transition-all hover:opacity-80"
                                style="height:90%"></div>
                            <div class="w-full bg-secondary-container rounded-t-sm transition-all hover:opacity-80"
                                style="height:88%"></div>
                            <div class="w-full bg-secondary-container rounded-t-sm transition-all hover:opacity-80"
                                style="height:82%"></div>
                        </div>
                        <div
                            class="flex justify-between mt-2 px-2 text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">
                            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                        </div>
                    </div>

                    {{-- Academic Performance Area Chart --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <h3 class="text-base font-bold text-on-surface mb-6">Academic Performance Trend</h3>
                        <div class="relative h-48 overflow-hidden rounded bg-gray-50 border border-surface-container-high">
                            <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                                <defs>
                                    <linearGradient id="areaGradient" x1="0" x2="0" y1="0"
                                        y2="1">
                                        <stop offset="0%" stop-color="#adc7ff" stop-opacity="0.4"></stop>
                                        <stop offset="100%" stop-color="#adc7ff" stop-opacity="0"></stop>
                                    </linearGradient>
                                </defs>
                                <path d="M0,80 C20,70 40,85 60,60 C80,35 100,50 100,50 L100,100 L0,100 Z"
                                    fill="url(#areaGradient)"></path>
                                <path d="M0,80 C20,70 40,85 60,60 C80,35 100,50 100,50" fill="none" stroke="#0059bb"
                                    stroke-width="2"></path>
                            </svg>
                            <div class="absolute top-4 left-4">
                                <p class="text-2xl font-extrabold text-primary leading-none">3.82</p>
                                <p class="text-xs text-on-surface-variant">Avg GPA (All Depts)</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── BOTTOM ROW: Lists ── --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Recent Registrations --}}
                    <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-surface-container-high flex justify-between items-center">
                            <h3 class="text-base font-bold text-on-surface">Recent Registrations</h3>
                            <a href="{{ route('admin.students.index') }}"
                                class="text-primary text-xs font-semibold hover:underline">View All</a>
                        </div>
                        <div class="divide-y divide-surface-container-high">
                            @forelse($students as $student)
                                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center font-bold text-on-surface-variant text-sm">
                                            {{ strtoupper(substr($student['name'], 0, 1)) }}{{ strtoupper(substr(strrchr($student['name'], ' '), 1, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-on-surface">{{ $student['name'] }}</p>
                                            <p class="text-xs text-on-surface-variant">{{ $student['class'] }}</p>
                                        </div>
                                    </div>
                                    @if ($student['fee_status'] === 'cleared')
                                        <span
                                            class="text-[11px] font-bold text-green-800 bg-green-100 px-2 py-1 rounded uppercase">Cleared</span>
                                    @else
                                        <span
                                            class="text-[11px] font-bold text-amber-800 bg-amber-100 px-2 py-1 rounded uppercase">K{{ number_format($student['balance']) }}
                                            Due</span>
                                    @endif
                                </div>
                            @empty
                                <div class="p-6 text-center text-on-surface-variant text-sm">No recent registrations found.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Upcoming Examinations --}}
                    <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-surface-container-high flex justify-between items-center">
                            <h3 class="text-base font-bold text-on-surface">Upcoming Examinations</h3>
                            <button
                                class="w-8 h-8 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">calendar_today</span>
                            </button>
                        </div>
                        <div class="p-4 space-y-4">
                            @foreach ([['month' => 'Nov', 'day' => '14', 'title' => 'AP World History: Mid-Term', 'detail' => 'Hall A-2 • 09:00 AM - 12:00 PM', 'color' => 'border-primary'], ['month' => 'Nov', 'day' => '16', 'title' => 'Intro to Computer Science', 'detail' => 'Lab 4 • 02:00 PM - 04:00 PM', 'color' => 'border-secondary'], ['month' => 'Nov', 'day' => '18', 'title' => 'Advanced Physics Lab Exam', 'detail' => 'Science Wing • All Day Event', 'color' => 'border-tertiary']] as $exam)
                                <div class="flex items-center gap-4 group">
                                    <div
                                        class="flex flex-col items-center justify-center w-14 h-14 bg-surface-container rounded-lg border-b-4 {{ $exam['color'] }} group-hover:scale-105 transition-transform">
                                        <span
                                            class="text-[10px] font-bold text-on-surface-variant uppercase">{{ $exam['month'] }}</span>
                                        <span class="text-lg font-extrabold text-on-surface">{{ $exam['day'] }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-on-surface">{{ $exam['title'] }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $exam['detail'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Footer spacing --}}
                <div class="h-12"></div>

            </div>
        </div>

    </div>

    {{-- FAB: Support trigger --}}
    <button
        class="fixed bottom-8 right-8 w-14 h-14 bg-on-surface text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all z-50">
        <span class="material-symbols-outlined">support_agent</span>
    </button>

    <script>
        // Micro-interaction: card lift on hover
        document.querySelectorAll('.group').forEach(card => {
            card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-4px)');
            card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
        });
    </script>

@endsection
