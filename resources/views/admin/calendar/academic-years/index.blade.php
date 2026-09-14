{{-- resources/views/admin/calendar/academic-years/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Academic Years')

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
                    <span class="text-label-sm font-label-sm text-primary font-bold">Academic Years</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Academic Years</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Define the date windows that organize terms, holidays, grades and fees.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.academic-years.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Academic Year
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <!-- Stat Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-6">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">calendar_month</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Academic Years</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $academicYears->total() }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">view_agenda</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Terms</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $academicYears->sum('terms_count') }}</p>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Label</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Start Date</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">End Date</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Terms</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Holidays</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Status</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        @forelse ($academicYears as $year)
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-4 font-semibold text-on-surface">{{ $year->label }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant">{{ $year->start_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant">{{ $year->end_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-surface-container font-label-sm text-label-sm font-semibold text-on-surface">
                                        {{ $year->terms_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-surface-container font-label-sm text-label-sm font-semibold text-on-surface">
                                        {{ $year->holidays_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($year->is_current)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Current
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-surface-container font-label-sm text-label-sm font-semibold text-on-surface-variant">
                                            <span class="w-1.5 h-1.5 rounded-full bg-outline-variant"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.academic-years.edit', $year) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="Edit academic year">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}"
                                            style="display: inline;" onsubmit="return confirm('Delete this academic year? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                title="Delete academic year">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">No academic years found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $academicYears->firstItem() ?? 0 }} to {{ $academicYears->lastItem() ?? 0 }} of
                    {{ $academicYears->total() }} academic years
                </p>
                <div class="flex items-center justify-end">
                    {{ $academicYears->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
