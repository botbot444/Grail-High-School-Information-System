@extends('layouts.app')

@section('title', 'Parent Portal')

@section('content')

<div class="parent-portal">
    <style>
        .parent-portal .chart-bar { transition: height 1s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>

    @include('parent.sidebar')
    @include('parent.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <!-- ==================== PAGE CONTENT ==================== -->
        <main class="flex-1 pt-16 pb-8 px-4 md:px-6">
            <div class="max-w-7xl mx-auto">

                <!-- ======================================== -->
                <!-- TAB: DASHBOARD -->
                <!-- ======================================== -->
                <div id="tab-dashboard" class="tab-content">
                    <!-- Welcome Header -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">Hello, {{ explode(' ', trim($parentProfile->full_name ?? Auth::user()->name))[0] ?? 'there' }} 👋</h2>
                            <p class="text-gray-500 mt-1">Overview for: <span class="font-semibold text-[#003461]">{{ $primary['student']->full_name ?? '—' }}{{ $primary ? ', ' . ($primary['student']->schoolClass?->display_name ?? $primary['student']->schoolClass?->class_name ?? '') : '' }}</span></p>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <button class="px-4 py-2 bg-gray-100 border border-gray-200 text-gray-700 text-sm font-medium rounded-lg flex items-center gap-2 hover:bg-gray-200 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">calendar_view_month</span>
                                View Schedule
                            </button>
                            <button class="px-4 py-2 bg-[#0059bb] text-white text-sm font-medium rounded-lg flex items-center gap-2 hover:bg-[#004a9e] transition-colors shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">download</span>
                                Download Progress Report
                            </button>
                        </div>
                    </div>
<!-- Stats Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <!-- Attendance -->
                        <div class="bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-500 font-medium">Attendance Rate</span>
                                <span class="w-9 h-9 bg-blue-50 text-[#003461] rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">how_to_reg</span>
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">{{ $attendanceRate }}%</p>
                            <p class="text-xs text-green-600 font-medium mt-1">↑ across {{ $children->count() }} child{{ $children->count() === 1 ? '' : 'ren' }}</p>
                        </div>

                        <!-- GPA -->
                        <div class="bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-500 font-medium">Current GPA</span>
                                <span class="w-9 h-9 bg-gray-100 text-[#003461] rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">grade</span>
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($gpa, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">On a 4.0 scale</p>
                        </div>

                        <!-- Assignments -->
                        <div class="bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-500 font-medium">Pending Assignments</span>
                                <span class="w-9 h-9 bg-red-50 text-red-600 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">assignment_late</span>
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">{{ $pendingAssignments }}</p>
                            <p class="text-xs text-gray-500 mt-1">Across all children</p>
                        </div>

                        <!-- Upcoming Events -->
                        <div class="bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-500 font-medium">Upcoming Events</span>
                                <span class="w-9 h-9 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">event</span>
                                </span>
                            </div>
                            <p class="text-2xl font-bold text-gray-900">{{ $children->count() }}</p>
                            <p class="text-xs text-gray-500 mt-1">Next: PTA Meeting</p>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
<!-- Performance Trend -->
                        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Performance Trend</h3>
                                    <p class="text-sm text-gray-500">Average grade percentage by month</p>
                                </div>
                                <span class="bg-gray-50 border border-gray-200 rounded-lg text-sm px-3 py-1.5 text-gray-500">Current Academic Year</span>
                            </div>
                            @if ($performanceTrend->isNotEmpty())
                                <div class="h-48 flex items-end justify-between gap-2 pt-2">
                                    @foreach ($performanceTrend as $index => $point)
                                        <div class="flex flex-col items-center gap-1 flex-1 h-full">
                                            <div class="w-full bg-blue-500 rounded-t-sm chart-bar" style="height: {{ max(min($point['value'], 100), 4) }}%; transition-delay: {{ $index * 0.1 }}s;" title="{{ $point['value'] }}%"></div>
                                            <span class="text-xs text-gray-500">{{ $point['month'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="h-48 flex items-center justify-center border border-dashed border-gray-200 rounded-lg">
                                    <p class="text-sm text-gray-400">No recorded grades to chart yet.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Recent Results -->
                        <div class="bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900">Recent Results</h3>
                                <a href="#" onclick="switchTab('performance')" class="text-sm text-[#0059bb] hover:underline">View All</a>
                            </div>
                            <div class="space-y-3">
                                @forelse ($results as $result)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $result->classSubject->subject->subject_name ?? 'Subject' }}</p>
                                            <p class="text-xs text-gray-500">{{ $result->assessment_type ?? 'Exam' }} · {{ $result->term ?? '' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-[#003461]">{{ $result->letter_grade ?? '—' }}</p>
                                            <p class="text-xs text-gray-500">{{ $result->percentage }}%</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 text-center py-6">No results recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
<!-- Announcements -->
                    <div class="bg-white p-5 rounded-xl border border-gray-200 card-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Latest Announcements</h3>
                            <button class="text-gray-400 hover:text-gray-600" onclick="switchTab('announcements')">
                                <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                                <div class="h-1.5 w-full bg-[#003461] rounded-full mb-3"></div>
                                <span class="text-[10px] font-semibold bg-blue-50 text-[#003461] px-2 py-0.5 rounded">ADMIN</span>
                                <h4 class="font-semibold text-gray-900 mt-2">Campus Safety Policy Update</h4>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">Please review the updated guidelines for campus entry and visitor access.</p>
                            </div>
                            <div class="p-4 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                                <div class="h-1.5 w-full bg-[#0059bb] rounded-full mb-3"></div>
                                <span class="text-[10px] font-semibold bg-blue-50 text-[#003461] px-2 py-0.5 rounded">ACADEMIC</span>
                                <h4 class="font-semibold text-gray-900 mt-2">Term Break Schedule</h4>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">The official calendar for the upcoming term break has been published.</p>
                            </div>
                            <div class="p-4 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                                <div class="h-1.5 w-full bg-gray-600 rounded-full mb-3"></div>
                                <span class="text-[10px] font-semibold bg-gray-100 text-gray-700 px-2 py-0.5 rounded">FACULTY</span>
                                <h4 class="font-semibold text-gray-900 mt-2">Parent-Teacher Conference</h4>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">Book your slot for the quarterly parent-teacher conference sessions.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TAB: MY CHILDREN -->
                <!-- ======================================== -->
                <div id="tab-children" class="tab-content hidden">
                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900">My Children</h2>
                            <p class="text-gray-500 mt-1">View academic progress and attendance for each child</p>
                        </div>
                        <button class="px-4 py-2 bg-[#0059bb] text-white text-sm font-medium rounded-lg flex items-center gap-2 hover:bg-[#004a9e] transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Link New Child
                        </button>
                    </div>

                    <!-- Children Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
@forelse ($children as $child)
                            @php $s = $child['student']; @endphp
                            <div class="bg-white rounded-xl border border-gray-200 card-shadow overflow-hidden hover:border-blue-300 transition-colors">
                                <div class="p-5 flex gap-4">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-28 h-28 rounded-xl bg-[#003461]/10 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[#003461] text-5xl">person</span>
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 bg-green-500 border-2 border-white w-7 h-7 rounded-full flex items-center justify-center">
                                            <span class="material-symbols-outlined text-white text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Active</span>
                                                <h3 class="text-xl font-bold text-[#003461] mt-1">{{ $s->full_name }}</h3>
                                                <p class="text-sm text-gray-500">{{ $s->schoolClass?->display_name ?? $s->schoolClass?->class_name ?? '—' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] text-gray-500 uppercase">Admin #</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $s->student_number }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 mt-4">
                                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                                <p class="text-[10px] text-gray-500 uppercase">GPA Score</p>
                                                <div class="flex items-baseline gap-1">
                                                    <p class="text-xl font-bold text-[#003461]">{{ number_format($child['gpa'], 2) }}</p>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                                <p class="text-[10px] text-gray-500 uppercase">Attendance</p>
                                                <div class="flex items-baseline gap-1">
                                                    <p class="text-xl font-bold text-[#003461]">{{ $child['attendance_rate'] }}%</p>
                                                    <div class="w-12 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                        <div class="h-full bg-[#0059bb]" style="width: {{ min($child['attendance_rate'], 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
<div class="border-t border-gray-200 p-3 bg-gray-50/50 grid grid-cols-2 sm:grid-cols-4 gap-1">
                                    <button class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-blue-50 hover:text-[#003461] transition-all group" onclick="switchTab('performance')">
                                        <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">analytics</span>
                                        <span class="text-[10px] text-gray-500 group-hover:text-[#003461]">View Results</span>
                                    </button>
                                    <button class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-blue-50 hover:text-[#003461] transition-all group" onclick="switchTab('attendance')">
                                        <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">event_available</span>
                                        <span class="text-[10px] text-gray-500 group-hover:text-[#003461]">Attendance</span>
                                    </button>
                                    <button class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-blue-50 hover:text-[#003461] transition-all group" onclick="switchTab('assignments')">
                                        <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">assignment</span>
                                        <span class="text-[10px] text-gray-500 group-hover:text-[#003461]">Assignments</span>
                                    </button>
                                    <button class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-blue-50 hover:text-[#003461] transition-all group" onclick="switchTab('reports')">
                                        <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">description</span>
                                        <span class="text-[10px] text-gray-500 group-hover:text-[#003461]">Reports</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="md:col-span-2 bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                                <span class="material-symbols-outlined text-6xl text-gray-300">family_restroom</span>
                                <h3 class="text-xl font-semibold text-gray-900 mt-4">No Children Linked</h3>
                                <p class="text-gray-500 mt-2">No students are linked to your account yet.</p>
                            </div>
                        @endforelse
                    </div>
<!-- Recent Records & Assignments -->
                    <div class="mt-6 bg-white rounded-xl border border-gray-200 card-shadow overflow-hidden">
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
                            <h3 class="font-semibold text-[#003461]">Recent Records & Assignments</h3>
                            <button class="text-sm text-[#0059bb] hover:underline" onclick="switchTab('assignments')">View All</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                        <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Score</th>
                                        <th class="px-4 py-3 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @php
                                        $assignRows = $children->flatMap(function ($child) {
                                            return $child['results']->map(fn ($g) => [
                                                'name' => $child['student']->full_name,
                                                'subject' => $g->classSubject->subject->subject_name ?? 'Subject',
                                                'type' => $g->assessment_type ?? 'Exam',
                                                'score' => $g->percentage,
                                                'grade' => $g->letter_grade ?? '—',
                                            ]);
                                        })->take(8);
                                    @endphp
@forelse ($assignRows as $row)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-7 h-7 rounded-full bg-[#003461] text-white flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($row['name'], 0, 2)) }}</div>
                                                    <span class="text-sm">{{ $row['name'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm">{{ $row['subject'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['type'] }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-[#003461]">{{ $row['score'] }}%</td>
                                            <td class="px-4 py-3">
                                                <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-1 rounded-md">{{ $row['grade'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No grade records yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
<!-- ======================================== -->
                <!-- TAB: ATTENDANCE (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-attendance" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">calendar_today</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Attendance Overview</h3>
                        <p class="text-gray-500 mt-2">Detailed attendance tracking coming soon.</p>
                        <p class="text-sm text-gray-400 mt-1">Current attendance rate: <span class="font-bold text-[#003461]">{{ $attendanceRate }}%</span></p>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TAB: PERFORMANCE (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-performance" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">monitoring</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Academic Performance</h3>
                        <p class="text-gray-500 mt-2">Subject-wise performance analysis coming soon.</p>
                        <p class="text-sm text-gray-400 mt-1">Current GPA: <span class="font-bold text-[#003461]">{{ number_format($gpa, 2) }}</span></p>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TAB: REPORTS (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-reports" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">description</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Reports</h3>
                        <p class="text-gray-500 mt-2">Download report cards and academic summaries.</p>
                        <button class="mt-4 px-6 py-2 bg-[#0059bb] text-white text-sm font-medium rounded-lg hover:bg-[#004a9e] transition-colors">
                            Download Report Card
                        </button>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TAB: ASSIGNMENTS (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-assignments" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">assignment</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Assignments</h3>
                        <p class="text-gray-500 mt-2">View all assignments and submit work.</p>
                        <p class="text-sm text-gray-400 mt-1">{{ $pendingAssignments }} pending assignment(s)</p>
                    </div>
                </div>
<!-- ======================================== -->
                <!-- TAB: ANNOUNCEMENTS (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-announcements" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">campaign</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Announcements</h3>
                        <p class="text-gray-500 mt-2">School announcements and updates.</p>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TAB: MESSAGES (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-messages" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">chat</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Messages</h3>
                        <p class="text-gray-500 mt-2">Direct messaging with teachers and staff.</p>
                    </div>
                </div>

                <!-- ======================================== -->
                <!-- TAB: SETTINGS (Placeholder) -->
                <!-- ======================================== -->
                <div id="tab-settings" class="tab-content hidden">
                    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300">settings</span>
                        <h3 class="text-xl font-semibold text-gray-900 mt-4">Settings</h3>
                        <p class="text-gray-500 mt-2">Manage your account preferences and security.</p>
                        <div class="mt-4 flex flex-wrap justify-center gap-3">
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Change Password</button>
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Notification Preferences</button>
                            <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Privacy Settings</button>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center text-xs text-on-surface-variant py-4 border-t border-outline-variant bg-white">
            © {{ date('Y') }} Grail Student Information System. All rights reserved.
        </footer>
</div>

@endsection
@push('scripts')
<script>
    // ===== SIDEBAR TOGGLE =====
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
        sidebar.classList.toggle('sidebar-expanded', !isCollapsed);
        document.getElementById('mainContent').classList.toggle('main-expanded', isCollapsed);
        document.getElementById('header').classList.toggle('header-expanded', isCollapsed);
    }

    // Keep the admin shell responsive on viewport changes.
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
        } else if (!sidebar.classList.contains('sidebar-collapsed')) {
            sidebar.classList.add('sidebar-collapsed');
            sidebar.classList.remove('sidebar-expanded');
        }
    });

    document.querySelectorAll('[data-parent-tab]').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            switchTab(this.dataset.parentTab);
        });
    });

// ===== TAB SWITCHING =====
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.parent-portal .tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Show selected tab
        const targetTab = document.getElementById('tab-' + tabName);
        if (targetTab) {
            targetTab.classList.remove('hidden');
        }

        // Update nav active state
        document.querySelectorAll('[data-parent-tab]').forEach(item => {
            item.classList.toggle('bg-[#004493]', item.dataset.parentTab === tabName);
            item.classList.toggle('text-white', item.dataset.parentTab === tabName);
            item.classList.toggle('border-l-4', item.dataset.parentTab === tabName);
            item.classList.toggle('border-[#adc7ff]', item.dataset.parentTab === tabName);
            item.classList.toggle('rounded-r-lg', item.dataset.parentTab === tabName);
            item.classList.toggle('font-bold', item.dataset.parentTab === tabName);
        });

        // Close sidebar on mobile after navigation
        if (window.innerWidth < 768) {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
        }
    }

    // ===== ANIMATE CHART BARS ON LOAD =====
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.parent-portal .chart-bar');
        bars.forEach(bar => {
            const height = bar.style.height;
            bar.style.height = '0%';
            setTimeout(() => {
                bar.style.height = height;
            }, 300);
        });

        // Set default tab
        switchTab('dashboard');
    });

    // ===== CLOSE SIDEBAR WITH ESC KEY =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 768 && sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
            }
        }
    });
</script>
@endpush