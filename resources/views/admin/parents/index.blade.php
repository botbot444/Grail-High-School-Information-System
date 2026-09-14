{{-- resources/views/admin/parents/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Parents & Guardians')

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
                    <span class="text-label-sm font-label-sm text-primary font-bold">Parents & Guardians</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    Parents & Guardians
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Manage the guardian directory, portal access, and student linkages.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.parents.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">person_add</span>
                    Add Parent
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <!-- Stat Strip -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-6">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">supervised_user_circle</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Total Guardians</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $totalGuardians }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">verified_user</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Portal Verified</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $portalVerified }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">diversity_3</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Linked to Students</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $linkedCount }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center text-amber-700">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">link_off</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Unassigned</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $unassignedCount }}</p>
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
                        placeholder="Name or email"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Linkage</label>
                    <select name="linkage"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All</option>
                        <option value="linked" @selected(request('linkage') === 'linked')>Has Children Linked</option>
                        <option value="unlinked" @selected(request('linkage') === 'unlinked')>No Children Linked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Account Status</label>
                    <select name="status"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="reset" @selected(request('status') === 'reset')>Password Reset Required</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary hover:bg-primary/90 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_alt</span> Apply
                </button>
                <a href="{{ route('admin.parents.index') }}"
                    class="px-3 py-2 text-sm font-medium text-on-surface-variant hover:text-primary">Clear all</a>
            </div>
        </form>

        <!-- Data Table -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant uppercase tracking-wider font-label-sm text-[11px]">
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Guardian</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Phone</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Occupation</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Children Linked</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant">Account Status</th>
                            <th class="px-6 py-4 font-semibold border-b border-outline-variant text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        @forelse ($parents as $parent)
                            @php
                                $childCount = $parent->students->count();
                                $mustReset = (bool) $parent->user?->must_change_password;
                                $isActive = (bool) $parent->user?->is_active;
                            @endphp
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed-variant font-bold border border-primary/10">
                                            {{ strtoupper(substr($parent->first_name, 0, 1) . substr($parent->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-on-surface">{{ $parent->full_name }}</p>
                                            <p class="text-[11px] text-on-surface-variant">{{ $parent->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant whitespace-nowrap">
                                    {{ $parent->phone ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant">
                                    {{ $parent->occupation ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-primary-container font-label-sm text-label-sm font-semibold">
                                        <span class="material-symbols-outlined text-[14px]">family_restroom</span>
                                        {{ $childCount }} {{ $childCount === 1 ? 'Child' : 'Children' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($mustReset)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            Password Reset Required
                                        </span>
                                    @elseif ($isActive)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm font-semibold">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.parents.show', $parent->parent_id) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="View profile">
                                            <span class="material-symbols-outlined text-xl">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.parents.edit', $parent->parent_id) }}"
                                            class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                            title="Edit parent">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('admin.parents.destroy', $parent->parent_id) }}"
                                            style="display: inline;" onsubmit="return confirm('Delete this parent? This also removes their login account.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                title="Delete parent">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant">No parents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $parents->firstItem() ?? 0 }} to {{ $parents->lastItem() ?? 0 }} of
                    {{ $parents->total() }} parents
                </p>
                <div class="flex items-center justify-end">
                    {{ $parents->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
