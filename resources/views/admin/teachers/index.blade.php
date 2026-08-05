@extends('layouts.app')

@section('title', 'Teacher Management')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Dashboard</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Teachers</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    Teacher Directory
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Manage and overview faculty staff members across all departments.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest border border-outline-variant text-on-surface font-label-sm text-label-sm rounded hover:bg-surface-container transition-colors"
                    type="button">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Export CSV
                </button>
                <a href="{{ route('admin.teachers.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    Add Teacher
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-6">
            <div
                class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined"
                        style="font-variation-settings: &quot;FILL&quot; 1">groups</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Teachers</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $teacherCount }}</p>
                </div>
            </div>
            <div
                class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined"
                        style="font-variation-settings: &quot;FILL&quot; 1">check_circle</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Active</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $activeTeachers }}</p>
                </div>
            </div>
            <div
                class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined"
                        style="font-variation-settings: &quot;FILL&quot; 1">pending_actions</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Pending Setup</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $pendingSetup }}</p>
                </div>
            </div>
            <div
                class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-error-container flex items-center justify-center text-error">
                    <span class="material-symbols-outlined"
                        style="font-variation-settings: &quot;FILL&quot; 1">assignment_turned_in</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Assigned Classes</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $assignedClasses }}</p>
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="p-6 border-b border-outline-variant flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface-variant px-1">Department</label>
                        <select
                            class="bg-surface-container-low border border-outline-variant rounded px-3 py-2 font-body-md text-body-md min-w-[180px] focus:ring-1 focus:ring-primary outline-none">
                            <option>All Departments</option>
                            <option>Mathematics</option>
                            <option>Science</option>
                            <option>Humanities</option>
                            <option>Arts &amp; Sports</option>
                            <option>Languages</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface-variant px-1">Status</label>
                        <select
                            class="bg-surface-container-low border border-outline-variant rounded px-3 py-2 font-body-md text-body-md min-w-[150px] focus:ring-1 focus:ring-primary outline-none">
                            <option>All Statuses</option>
                            <option>Active</option>
                            <option>Pending Setup</option>
                        </select>
                    </div>
                </div>
                <button class="flex items-center gap-2 text-primary font-label-sm text-label-sm hover:underline"
                    type="button">
                    <span class="material-symbols-outlined text-lg">filter_list_off</span>
                    Clear Filters
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Employee No.</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Teacher Name</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Assigned Subjects</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Classes</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Status</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        @forelse ($teachers as $teacher)
                            @php
                                $statusText = $teacher->classSubjects->isNotEmpty() ? 'Active' : 'Pending Setup';
                                $statusClasses = $teacher->classSubjects->isNotEmpty()
                                    ? 'bg-green-50 text-green-700 border-green-100'
                                    : 'bg-amber-50 text-amber-700 border-amber-100';
                                $statusDot = $teacher->classSubjects->isNotEmpty() ? 'bg-green-600' : 'bg-amber-500';
                                $classNames = $teacher->classSubjects
                                    ->pluck('schoolClass.class_name')
                                    ->filter()
                                    ->unique()
                                    ->take(3)
                                    ->values();
                            @endphp
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-4 font-data-mono text-data-mono text-primary font-bold">
                                    T-{{ $teacher->teacher_id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed font-bold border border-primary/10">
                                            {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $teacher->full_name }}</p>
                                            <p class="text-[11px] text-on-surface-variant">{{ $teacher->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($teacher->classSubjects->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($teacher->classSubjects->take(2) as $assignment)
                                                <span
                                                    class="px-2 py-0.5 bg-primary-fixed text-on-primary-fixed-variant rounded-full text-[11px] font-bold">
                                                    {{ $assignment->subject?->subject_name ?? 'N/A' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-on-surface-variant">No subjects assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant">
                                    {{ $classNames->isNotEmpty() ? $classNames->join(', ') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $statusClasses }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.teachers.show', $teacher->teacher_id) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="View profile">
                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.teachers.edit', $teacher->teacher_id) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="Edit teacher">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('admin.teachers.destroy', $teacher->teacher_id) }}"
                                            style="display: inline;" onsubmit="return confirm('Delete this teacher?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                title="Delete teacher">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No teachers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div
                class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $teachers->firstItem() ?? 0 }} to {{ $teachers->lastItem() ?? 0 }} of
                    {{ $teachers->total() }} teachers
                </p>
                <div class="flex items-center justify-end">
                    {{ $teachers->links() }}
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between py-6">
            <div class="flex items-center gap-6">
                <a class="font-label-sm text-label-sm text-primary hover:underline" href="#">Download Staff
                    Handbook</a>
                <a class="font-label-sm text-label-sm text-primary hover:underline" href="#">Department Hierarchy
                    View</a>
                <a class="font-label-sm text-label-sm text-primary hover:underline" href="#">Teacher Schedule Bulk
                    Import</a>
            </div>
            <div class="text-on-surface-variant font-label-sm text-[11px] uppercase tracking-widest">
                Grail School Information System v2.4.0
            </div>
        </div>
    </main>
@endsection
