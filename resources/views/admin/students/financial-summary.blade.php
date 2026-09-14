@extends('layouts.app')

@section('title', 'Financial Summary - ' . $student->full_name)

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


        <div class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                        <span class="text-label-sm font-label-sm"><a href="{{ route('admin.students.index') }}" class="hover:text-primary">Students</a></span>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-label-sm font-label-sm text-primary font-bold">Financial Summary</span>
                    </nav>
                    <h2 class="font-headline-sm text-headline-sm font-extrabold text-on-surface">{{ $student->full_name }}</h2>
                    <p class="text-body-sm text-on-surface-variant mt-1">
                        {{ $student->student_number }} · {{ $student->schoolClass->class_name ?? 'Unassigned' }}
                    </p>
                    @if($student->guardian)
                        <p class="text-body-sm text-on-surface-variant">
                            Guardian: {{ $student->guardian->name }} ({{ $student->guardian->email ?? 'No email' }})
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.students.show', $student->student_id) }}"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined text-lg">arrow_back</span> Profile
                    </a>
                    <button onclick="window.print()"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined text-lg">print</span> Print
                    </button>
                    <a href="{{ route('admin.students.statement', $student->student_id) }}"
                        class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                        <span class="material-symbols-outlined text-lg">description</span> Download Statement
                    </a>
                </div>
            </div>
        </div>

        @php $hasBalance = ($summary['total_balance'] ?? 0) > 0; @endphp

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">Total Due</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">ZMW {{ number_format($summary['total_due'], 2) }}</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">Total Paid</p>
                <p class="font-display-md text-display-md font-extrabold text-green-700">ZMW {{ number_format($summary['total_paid'], 2) }}</p>
            </div>
            <div class="rounded-xl border shadow-sm p-4 {{ $hasBalance ? 'bg-error-container/30 border-error/20' : 'bg-green-50 border-green-200' }}">
                <p class="text-label-sm font-label-sm mb-2 {{ $hasBalance ? 'text-error' : 'text-green-700' }}">Outstanding Balance</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">ZMW {{ number_format($summary['total_balance'], 2) }}</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">Fees</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">{{ $summary['total_fees'] }}</p>
                <p class="mt-1 text-xs text-on-surface-variant">
                    <span class="text-green-700">{{ $summary['cleared'] }} cleared</span> ·
                    <span class="text-secondary">{{ $summary['partial'] }} partial</span> ·
                    <span class="text-error">{{ $summary['overdue'] }} overdue</span>
                </p>
            </div>
        </div>        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-outline-variant">
                <h2 class="font-label-sm font-label-sm font-semibold text-on-surface">Fee History</h2>
            </div>
            <table class="w-full text-body-sm">
                <thead>
                    <tr class="border-b border-outline-variant text-left text-label-sm text-on-surface-variant">
                        <th class="px-5 py-3 font-semibold">Description</th>
                        <th class="px-5 py-3 font-semibold">Period</th>
                        <th class="px-5 py-3 font-semibold">Due Date</th>
                        <th class="px-5 py-3 font-semibold text-right">Amount</th>
                        <th class="px-5 py-3 font-semibold text-right">Paid</th>
                        <th class="px-5 py-3 font-semibold text-right">Balance</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fees as $fee)
                        <tr class="border-b last:border-0 border-outline-variant/60">
                            <td class="px-5 py-3">
                                <p class="font-medium text-on-surface">Fee #{{ $fee->fee_id }}</p>
                                <p class="text-xs text-on-surface-variant">{{ $fee->feeItems->count() }} item(s)</p>
                            </td>
                            <td class="px-5 py-3 text-on-surface-variant">
                                {{ $fee->term?->name ?? 'N/A' }}<br>
                                <span class="text-xs">{{ $fee->academicYear?->label ?? '' }}</span>
                            </td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $fee->due_date->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right font-mono text-on-surface">ZMW {{ number_format((float) $fee->amount_due, 2) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-green-700">ZMW {{ number_format((float) $fee->amount_paid, 2) }}</td>
                            <td class="px-5 py-3 text-right font-mono {{ $fee->balance > 0 ? 'text-error' : 'text-on-surface' }}">
                                ZMW {{ number_format((float) $fee->balance, 2) }}
                            </td>
                            <td class="px-5 py-3"><x-status-pill :status="$fee->status" /></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.fees.show', $fee) }}" class="text-label-sm text-primary hover:underline">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-on-surface-variant">No fees found for this student.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>
@endsection