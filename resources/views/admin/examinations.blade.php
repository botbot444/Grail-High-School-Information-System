@extends('layouts.app')

@section('title', 'Examinations')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[88px]" id="mainContent">
            <div class="p-8 max-w-7xl mx-auto space-y-6">
                <nav class="flex items-center gap-2 text-sm text-on-surface-variant">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary font-semibold">Examinations</span>
                </nav>

                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">Results Analysis</h1>
                        <p class="text-on-surface-variant text-sm mt-1">Academic Session 2023-24 • Term 2 (Final
                            Examinations)</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg bg-white text-on-surface hover:bg-surface-container-high transition-all">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            Export PDF
                        </button>
                        <button
                            class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg bg-white text-on-surface hover:bg-surface-container-high transition-all">
                            <span class="material-symbols-outlined text-[18px]">filter_list</span>
                            Filter View
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Pass Rate</p>
                        <p class="mt-2 text-4xl font-extrabold text-on-surface">87.4%</p>
                        <p class="mt-2 text-sm text-green-600">↑ 4.2% from last term</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Top Subject</p>
                        <p class="mt-2 text-4xl font-extrabold text-on-surface">Mathematics</p>
                        <p class="mt-2 text-sm text-on-surface-variant">Average 82.6%</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">At Risk</p>
                        <p class="mt-2 text-4xl font-extrabold text-on-surface">14</p>
                        <p class="mt-2 text-sm text-red-600">Students below 50%</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-outline-variant shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Average Score</p>
                        <p class="mt-2 text-4xl font-extrabold text-on-surface">72.9</p>
                        <p class="mt-2 text-sm text-on-surface-variant">Across all cohorts</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-on-surface">Performance by Subject</h2>
                                <p class="text-sm text-on-surface-variant">Snapshot of average scores by subject.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold">Term
                                2</span>
                        </div>
                        <div class="space-y-4">
                            @php($subjects = [['name' => 'Mathematics', 'score' => 82.6, 'color' => 'bg-primary'], ['name' => 'English', 'score' => 78.4, 'color' => 'bg-secondary'], ['name' => 'Science', 'score' => 74.9, 'color' => 'bg-tertiary'], ['name' => 'History', 'score' => 70.2, 'color' => 'bg-slate-500']])
                            @foreach ($subjects as $subject)
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-semibold text-on-surface">{{ $subject['name'] }}</span>
                                        <span class="text-sm text-on-surface-variant">{{ $subject['score'] }}%</span>
                                    </div>
                                    <div class="h-2.5 rounded-full bg-surface-container">
                                        <div class="h-2.5 rounded-full {{ $subject['color'] }}"
                                            style="width: {{ min($subject['score'], 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                        <h2 class="text-lg font-bold text-on-surface">Upcoming Examinations</h2>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-lg border border-outline-variant p-3">
                                <p class="font-semibold text-on-surface">Practical Biology</p>
                                <p class="text-sm text-on-surface-variant">23 Jul • Lab 2</p>
                            </div>
                            <div class="rounded-lg border border-outline-variant p-3">
                                <p class="font-semibold text-on-surface">Physics Oral</p>
                                <p class="text-sm text-on-surface-variant">28 Jul • Science Block</p>
                            </div>
                            <div class="rounded-lg border border-outline-variant p-3">
                                <p class="font-semibold text-on-surface">English Composition</p>
                                <p class="text-sm text-on-surface-variant">02 Aug • Main Hall</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-on-surface">Recent Results</h2>
                            <p class="text-sm text-on-surface-variant">Latest exam submissions and status.</p>
                        </div>
                        <a href="#" class="text-sm font-semibold text-primary">View all</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-on-surface-variant border-b border-outline-variant">
                                    <th class="pb-3 pr-4">Student</th>
                                    <th class="pb-3 pr-4">Subject</th>
                                    <th class="pb-3 pr-4">Score</th>
                                    <th class="pb-3 pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-on-surface">Amina Yusuf</td>
                                    <td class="py-3 pr-4">Mathematics</td>
                                    <td class="py-3 pr-4">88</td>
                                    <td class="py-3 pr-4"><span
                                            class="px-2 py-1 rounded-full bg-green-50 text-green-600 text-xs font-semibold">Excellent</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-on-surface">Daniel Kim</td>
                                    <td class="py-3 pr-4">English</td>
                                    <td class="py-3 pr-4">76</td>
                                    <td class="py-3 pr-4"><span
                                            class="px-2 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">Good</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-on-surface">Lilian Otieno</td>
                                    <td class="py-3 pr-4">Science</td>
                                    <td class="py-3 pr-4">49</td>
                                    <td class="py-3 pr-4"><span
                                            class="px-2 py-1 rounded-full bg-red-50 text-red-600 text-xs font-semibold">Needs
                                            Review</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
