{{-- resources/views/admin/classes/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Classes')

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
                    <span class="text-label-sm font-label-sm text-primary font-bold">Classes</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Classes</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Manage class sections, homeroom teacher assignments, and curriculum subjects.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.classes.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Class
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <!-- Stat Strip -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-6">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">meeting_room</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Classes</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $totalClasses }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">groups</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Students Enrolled</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $totalStudents }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">badge</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Homeroom Coverage</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $homeroomCoverage }}%</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center text-amber-700">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">person_off</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Needs Teacher</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $needsTeacherCount }}</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET"
            class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Class name or homeroom teacher"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Grade Level</label>
                    <select name="grade_level_id"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Grades</option>
                        @foreach ($gradeLevels as $gradeLevel)
                            <option value="{{ $gradeLevel->grade_level_id }}" @selected(request('grade_level_id') == $gradeLevel->grade_level_id)>{{ $gradeLevel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Status</label>
                    <select name="status"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Has Homeroom Teacher</option>
                        <option value="needs-teacher" @selected(request('status') === 'needs-teacher')>Needs Teacher</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span> Apply
                </button>
                <a href="{{ route('admin.classes.index') }}"
                    class="px-3 py-2 text-sm font-medium text-on-surface-variant hover:text-primary">Clear all</a>
            </div>
        </form>

        <!-- Data Table -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Class</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Grade</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Homeroom Teacher</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Subjects</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Enrolled</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Status</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        @forelse ($classes as $class)
                            @php
                                $subjectList = $class->subjects;
                                $visibleSubjects = $subjectList->take(3);
                                $extraCount = $subjectList->count() - $visibleSubjects->count();
                            @endphp
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.classes.show', $class) }}" class="font-semibold text-on-surface hover:text-primary transition-colors">
                                        {{ $class->class_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-surface-container font-label-sm text-label-sm font-semibold text-on-surface">
                                        {{ $class->grade_level_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($class->teacher)
                                        <span class="text-on-surface">{{ $class->teacher->full_name }}</span>
                                    @else
                                        <span class="text-error font-medium italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5 flex-wrap max-w-xs">
                                        @forelse ($visibleSubjects as $subject)
                                            <span class="px-2.5 py-0.5 rounded-full bg-surface-container font-label-sm text-label-sm text-on-surface font-medium">{{ $subject->subject_name }}</span>
                                        @empty
                                            <span class="text-on-surface-variant text-sm">None assigned</span>
                                        @endforelse
                                        @if ($extraCount > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-micro text-label-micro text-on-surface-variant font-bold">+{{ $extraCount }} more</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant whitespace-nowrap">
                                    {{ $class->students_count }} {{ $class->students_count === 1 ? 'student' : 'students' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($class->teacher_id)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                            Needs Teacher
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.classes.show', $class) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="View details">
                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.classes.edit', $class) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="Edit class">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.classes.destroy', $class) }}"
                                            style="display: inline;" onsubmit="return confirm('Delete this class? Students enrolled in it will need to be reassigned.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                title="Delete class">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">No classes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $classes->firstItem() ?? 0 }} to {{ $classes->lastItem() ?? 0 }} of
                    {{ $classes->total() }} classes
                </p>
                <div class="flex items-center justify-end">
                    {{ $classes->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
