@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        {{-- Same wrapper pattern as admin/students/show.blade.php: ml-[260px] clears
             the fixed sidebar, pt-[72px] clears the fixed header. Deliberately NOT
             using the old `.main-content` class (resources/css/app.css) — that's a
             leftover from an earlier prototype and its `padding: 25px` shorthand
             silently overrides any padding-top utility placed on the same element,
             which is what caused the header to overlap the page content. --}}
        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
        @include('admin.partials.flash')

            <div class="space-y-6">

                {{-- ── Welcome Header ── --}}
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">
                            Good day, {{ auth()->user()->name }}
                        </h1>
                        <p class="text-on-surface-variant text-sm mt-1">
                            @if($currentAcademicYear)
                                {{ $currentTerm?->name ?? 'Current Term' }} · {{ $currentAcademicYear->label }}
                                <span class="mx-2">•</span>
                                <span class="text-xs" style="color:#1F4D3D;">
                                    {{ $currentAcademicYear->start_date->format('M d') }} - {{ $currentAcademicYear->end_date->format('M d, Y') }}
                                </span>
                            @else
                                No academic year set as current
                            @endif
                        </p>
                    </div>
                </div>

                {{-- ── Today's Collections + Urgent Attention ── --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border bg-white p-4" style="border-color:#D9D4C8;">
                        <p class="text-xs font-medium uppercase tracking-wide" style="color:#5C6B66;">Today's Collections</p>
                        <p class="mt-2 font-[IBM_Plex_Mono] text-3xl" style="color:#16191C;">
                            ZMW {{ number_format($todayCollections ?? 0, 2) }}
                        </p>
                        <p class="mt-1 text-xs" style="color:#5C6B66;">
                            {{ $todayPayments ?? 0 }} payments received
                        </p>
                    </div>

                    <div class="rounded-lg border p-4" style="border-color:#D9D4C8; background-color:#F5DEDB;">
                        <p class="text-xs font-medium uppercase tracking-wide" style="color:#8A2A20;">Urgent Attention</p>
                        <p class="mt-2 font-[IBM_Plex_Mono] text-3xl" style="color:#16191C;">
                            {{ $urgentFees ?? 0 }}
                        </p>
                        <p class="mt-1 text-xs" style="color:#5C6B66;">
                            Fees due within 7 days
                        </p>
                    </div>
                </div>

                {{-- ── Statistics Cards ── --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    {{-- Card 1: Total Students --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200">
                        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-primary mb-4">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings:'FILL' 1">groups</span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Students</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalStudents }}</p>
                    </div>

                    {{-- Card 2: Total Teachers --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200">
                        <div class="w-12 h-12 bg-secondary-container/20 rounded-lg flex items-center justify-center text-secondary mb-4">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings:'FILL' 1">person_pin</span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Teachers</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalStaff }}</p>
                    </div>

                    {{-- Card 3: Total Parents --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200">
                        <div class="w-12 h-12 bg-tertiary-fixed-dim/20 rounded-lg flex items-center justify-center text-tertiary mb-4">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings:'FILL' 1">family_restroom</span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Parents</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalParents ?? '—' }}</p>
                    </div>

                    {{-- Card 4: Total Classes --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200">
                        <div class="w-12 h-12 bg-secondary-fixed/20 rounded-lg flex items-center justify-center text-on-secondary-container mb-4">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings:'FILL' 1">meeting_room</span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Classes</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalClasses ?? '—' }}</p>
                    </div>

                    {{-- Card 5: Total Subjects --}}
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-1 transition-all duration-200">
                        <div class="w-12 h-12 bg-surface-container-highest rounded-lg flex items-center justify-center text-on-surface-variant mb-4">
                            <span class="material-symbols-outlined text-2xl" style="font-variation-settings:'FILL' 1">book</span>
                        </div>
                        <p class="text-on-surface-variant text-xs font-semibold uppercase tracking-wide">Total Subjects</p>
                        <p class="text-4xl font-extrabold text-on-surface mt-1">{{ $totalSubjects ?? '—' }}</p>
                    </div>

                </div>

                {{-- ── Recently Edited Teachers + Quick Actions ── --}}
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
                                <div class="py-3 flex items-center justify-between hover:bg-surface-container-low transition-colors rounded px-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center font-bold text-primary text-sm">
                                            {{ strtoupper(substr($teacher->first_name, 0, 1)) }}{{ strtoupper(substr($teacher->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-on-surface">{{ $teacher->full_name }}</p>
                                            <p class="text-xs text-on-surface-variant">
                                                {{ $teacher->classSubjects->first()?->subject?->subject_name ?? 'Teacher' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-on-surface-variant">{{ $teacher->updated_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <div class="py-10 text-center text-on-surface-variant text-sm">No recent teacher activity.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Actions (1/3 width). bg-primary + text-on-primary (white) —
                         the same pair the header's "Add New" button already uses — not
                         bg-primary-container/text-on-primary-container, which pairs a
                         bright blue with dark navy text and is hard to read. --}}
                    <div class="bg-primary text-on-primary p-6 rounded-xl shadow-lg relative overflow-hidden">
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
                                <a href="{{ route('admin.fees.create') }}"
                                    class="w-full py-2.5 px-4 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-between transition-all text-xs font-semibold">
                                    <span class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-lg">receipt_long</span>
                                        New Fee
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
                            <span class="material-symbols-outlined text-[120px]" style="font-variation-settings:'FILL' 1">bolt</span>
                        </div>
                    </div>

                </div>

                {{-- ── Recent Registrations ── --}}
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
                                    <div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center font-bold text-on-surface-variant text-sm">
                                        {{ strtoupper(substr($student['name'], 0, 1)) }}{{ strtoupper(substr(strrchr($student['name'], ' '), 1, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-surface">{{ $student['name'] }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $student['class'] }}</p>
                                    </div>
                                </div>
                                @if ($student['fee_status'] === 'cleared')
                                    <span class="text-[11px] font-bold text-green-800 bg-green-100 px-2 py-1 rounded uppercase">Cleared</span>
                                @else
                                    <span class="text-[11px] font-bold text-amber-800 bg-amber-100 px-2 py-1 rounded uppercase">K{{ number_format($student['balance']) }} Due</span>
                                @endif
                            </div>
                        @empty
                            <div class="p-6 text-center text-on-surface-variant text-sm">No recent registrations found.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer spacing --}}
                <div class="h-12"></div>

            </div>
        </main>

    </div>
@endsection
