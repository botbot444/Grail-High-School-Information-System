@extends('layouts.app')

@section('title', 'Fee Management')

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
                    <span class="text-label-sm font-label-sm text-primary font-bold">Fees</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    Fee Records
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Multi-item fee structure with per-student balance tracking.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.fees.create') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    Add Fee
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.fees.index') }}" class="mb-4 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Academic Year</label>
                    <select name="academic_year_id"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Years</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->year_id }}" @selected(request('academic_year_id') == $year->year_id)>
                                {{ $year->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Term</label>
                    <select name="term_id"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Terms</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->term_id }}" @selected(request('term_id') == $term->term_id)>
                                {{ $term->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Status</label>
                    <select name="status"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="Pending" @selected(request('status') === 'Pending')>Pending</option>
                        <option value="Partially Paid" @selected(request('status') === 'Partially Paid')>Partially Paid</option>
                        <option value="Cleared" @selected(request('status') === 'Cleared')>Cleared</option>
                        <option value="Overdue" @selected(request('status') === 'Overdue')>Overdue</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">Filter</button>
                    <a href="{{ route('admin.fees.index') }}" class="px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">Reset</a>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.fees.bulk-action') }}" id="bulk-form">
            @csrf
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="select-all"
                        onchange="document.querySelectorAll('.fee-checkbox').forEach(cb => cb.checked = this.checked)">
                    <span class="text-xs text-on-surface-variant">Select All</span>
                </label>

                <select name="action" required
                    class="rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                    <option value="">Bulk Actions</option>
                    <option value="mark_cleared">Mark as Cleared</option>
                    <option value="mark_overdue">Mark as Overdue</option>
                    <option value="send_reminder">Send Reminder</option>
                    <option value="export_selected">Export Selected</option>
                    <option value="delete_selected">Delete Selected</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">Apply</button>
            </div>

            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container">
                                <th class="px-4 py-3 w-10"></th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant">Student</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant">Term / Year</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant text-right">Amount Due</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant text-right">Paid</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant text-right">Balance</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Status</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Due Date</th>
                                <th class="px-6 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fees as $fee)
                                <tr class="border-b border-outline-variant/60 hover:bg-surface-container-high/60 transition-colors">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="fee_ids[]" value="{{ $fee->fee_id }}" class="fee-checkbox">
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-body-md font-semibold text-on-surface">
                                            {{ $fee->student->full_name ?? 'N/A' }}
                                        </p>
                                        <p class="text-label-sm text-on-surface-variant">
                                            {{ $fee->student->schoolClass->class_name ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-body-md text-on-surface">
                                        {{ $fee->term }}
                                    </td>
                                    <td class="px-6 py-4 text-body-md text-on-surface text-right font-mono">
                                        ZMW {{ number_format($fee->amount_due, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-body-md text-on-surface text-right font-mono">
                                        ZMW {{ number_format($fee->amount_paid, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-body-md text-on-surface text-right font-mono">
                                        ZMW {{ number_format($fee->balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $badge = match($fee->status) {
                                                'Pending' => 'bg-warning/15 text-warning',
                                                'Partially Paid' => 'bg-secondary-fixed/15 text-secondary',
                                                'Cleared' => 'bg-green-100 text-green-700',
                                                'Overdue' => 'bg-error/15 text-error',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="inline-block px-3 py-1 rounded-full text-label-sm font-semibold {{ $badge }}">
                                            {{ $fee->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-body-md text-on-surface text-center">
                                        {{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.fees.show', $fee->fee_id) }}"
                                                class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                                title="View">
                                                <span class="material-symbols-outlined text-xl">visibility</span>
                                            </a>
                                            <a href="{{ route('admin.fees.edit', $fee->fee_id) }}"
                                                class="p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded transition-all"
                                                title="Edit">
                                                <span class="material-symbols-outlined text-xl">edit</span>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.fees.destroy', $fee->fee_id) }}"
                                                style="display: inline;"
                                                onsubmit="return confirm('Delete this fee record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container/20 rounded transition-all"
                                                    title="Delete">
                                                    <span class="material-symbols-outlined text-xl">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-on-surface-variant">
                                        No fee records found. <a href="{{ route('admin.fees.create') }}" class="text-primary hover:underline">Create one</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div
                    class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                    <p class="font-label-sm text-label-sm text-on-surface-variant">
                        Showing {{ $fees->firstItem() ?? 0 }} to {{ $fees->lastItem() ?? 0 }} of {{ $fees->total() }}
                        fees
                    </p>
                    <div class="flex items-center justify-end">
                        {{ $fees->links() }}
                    </div>
                </div>
            </div>
        </form>
    </main>
@endsection
