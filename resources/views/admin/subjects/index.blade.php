{{-- resources/views/admin/subjects/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Subjects')

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
                    <span class="text-label-sm font-label-sm text-primary font-bold">Subjects</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Subjects</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Manage the curriculum registry and see which classes and teachers each subject is tied to.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.subjects.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Subject
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <!-- Stat Strip -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-6">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">menu_book</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Subjects</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $totalSubjects }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">groups</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Offered in Classes</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $offeredCount }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center text-amber-700">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">link_off</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Not Yet Offered</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $unassignedCount }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">badge</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Faculty Covered</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $facultyCovered }}</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET"
            class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by subject name"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Curriculum Status</label>
                    <select name="status"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Subjects</option>
                        <option value="offered" @selected(request('status') === 'offered')>Offered in a Class</option>
                        <option value="unassigned" @selected(request('status') === 'unassigned')>Not Yet Offered</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span> Apply
                </button>
                <a href="{{ route('admin.subjects.index') }}"
                    class="px-3 py-2 text-sm font-medium text-on-surface-variant hover:text-primary">Clear all</a>
            </div>
        </form>

        <!-- Data Table -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Subject</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Classes Offering It</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Teachers Assigned</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Curriculum Status</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        @forelse ($subjects as $subject)
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center text-primary-container flex-shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">menu_book</span>
                                        </div>
                                        <span class="font-semibold text-on-surface">{{ $subject->subject_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary-fixed/80 text-on-primary-fixed-variant font-label-sm text-label-sm font-semibold">
                                        <span class="material-symbols-outlined text-[14px]">groups</span>
                                        {{ $subject->classes_count }} {{ $subject->classes_count === 1 ? 'Class' : 'Classes' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-on-surface font-label-sm text-label-sm font-semibold">
                                        <span class="material-symbols-outlined text-[14px]">badge</span>
                                        {{ $subject->teachers_count }} {{ $subject->teachers_count === 1 ? 'Teacher' : 'Teachers' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($subject->classes_count > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Offered in Curriculum
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm font-semibold">
                                            Not Yet Offered
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.subjects.show', $subject) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="View details">
                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.subjects.edit', $subject) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="Edit subject">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                                            style="display: inline;" onsubmit="return confirm('Delete this subject? It will be removed from any classes offering it.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                title="Delete subject">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-on-surface-variant">No subjects found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $subjects->firstItem() ?? 0 }} to {{ $subjects->lastItem() ?? 0 }} of
                    {{ $subjects->total() }} subjects
                </p>
                <div class="flex items-center justify-end">
                    {{ $subjects->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
