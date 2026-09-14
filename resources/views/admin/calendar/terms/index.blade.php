{{-- resources/views/admin/calendar/terms/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Terms')

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
                    <span class="text-label-sm font-label-sm text-primary font-bold">Terms</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Terms</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Manage the teaching windows within each academic year.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.terms.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Term
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <!-- Stat Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter mb-6">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">view_agenda</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Registered Terms</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $terms->total() }}</p>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET"
            class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 lg:max-w-sm">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="academic_year_id">Filter by Academic Year</label>
                    <select id="academic_year_id" name="academic_year_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer">
                        <option value="">All years</option>
                        @foreach ($academicYears as $id => $label)
                            <option value="{{ $id }}" {{ $id == $academicYearId ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <!-- Data Table -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Academic Year</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Name</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Start Date</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">End Date</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">School Days</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Status</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        @forelse ($terms as $term)
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-4 text-on-surface-variant">{{ $term->academicYear->label ?? chr(8212) }}</td>
                                <td class="px-6 py-4 font-semibold text-on-surface">{{ $term->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant">{{ $term->start_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant">{{ $term->end_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-surface-container font-label-sm text-label-sm font-semibold text-on-surface">
                                        {{ $term->school_days }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($term->is_current)
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
                                        <a href="{{ route('admin.terms.edit', $term) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="Edit term">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.terms.destroy', $term) }}"
                                            style="display: inline;" onsubmit="return confirm('Delete this term? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                title="Delete term">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-on-surface-variant">No terms found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $terms->firstItem() ?? 0 }} to {{ $terms->lastItem() ?? 0 }} of
                    {{ $terms->total() }} terms
                </p>
                <div class="flex items-center justify-end">
                    {{ $terms->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
