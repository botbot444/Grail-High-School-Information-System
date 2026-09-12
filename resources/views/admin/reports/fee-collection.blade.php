@extends('layouts.app')

@section('title', 'Fee Collection Report')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Reports</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Fee Collection</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Fee Collection Report</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Financial performance and collection efficiency.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()"
                    class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined text-lg">print</span> Print
                </button>
                <a href="{{ route('admin.reports.fee-collection.export', request()->all()) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">file_download</span> Export CSV
                </a>
            </div>
        </div>

        @php
            $s = $reportData['summary'] ?? [];
            $hasBalance = ($s['total_balance'] ?? 0) > 0;
        @endphp

        <form method="GET" action="{{ route('admin.reports.fee-collection') }}"
            class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Academic Year</label>
                    <select name="academic_year_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Years</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->year_id }}" @selected(($filters['academic_year_id'] ?? null) == $year->year_id)>{{ $year->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Term</label>
                    <select name="term_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Terms</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->term_id }}" @selected(($filters['term_id'] ?? null) == $term->term_id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Class</label>
                    <select name="class_id" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->class_id }}" @selected(($filters['class_id'] ?? null) == $class->class_id)>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity">Apply</button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('admin.reports.fee-collection') }}" class="text-label-sm text-on-surface-variant hover:text-primary">Clear</a>
                    @endif
                </div>
            </div>
        </form>        {{-- KPI Cards --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">Total Fees</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">{{ $s['total_fees'] ?? 0 }}</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">Total Due</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">ZMW {{ number_format($s['total_amount_due'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">Collected</p>
                <p class="font-display-md text-display-md font-extrabold text-green-700">ZMW {{ number_format($s['total_collected'] ?? 0, 2) }}</p>
            </div>
            <div class="rounded-xl border shadow-sm p-4 {{ $hasBalance ? 'bg-error-container/30 border-error/20' : 'bg-green-50 border-green-200' }}">
                <p class="text-label-sm font-label-sm mb-2 {{ $hasBalance ? 'text-error' : 'text-green-700' }}">Outstanding Balance</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">ZMW {{ number_format($s['total_balance'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-primary-container/20 rounded-xl border border-primary/20 shadow-sm p-4">
                <p class="text-label-sm font-label-sm text-primary mb-2">Collection Rate</p>
                <p class="font-display-md text-display-md font-extrabold text-on-surface">{{ $s['collection_rate'] ?? 0 }}%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Status Breakdown --}}
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-outline-variant">
                    <h2 class="font-label-sm font-label-sm font-semibold text-on-surface">Status Breakdown</h2>
                    <p class="text-label-sm text-on-surface-variant">Fee status distribution</p>
                </div>
                <table class="w-full text-body-sm">
                    <thead>
                        <tr class="border-b border-outline-variant text-left text-label-sm text-on-surface-variant">
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Count</th>
                            <th class="px-5 py-3 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData['status_breakdown'] ?? [] as $status => $data)
                            <tr class="border-b last:border-0 border-outline-variant/60">
                                <td class="px-5 py-3"><x-status-pill :status="$status" /></td>
                                <td class="px-5 py-3 text-on-surface">{{ $data['count'] }}</td>
                                <td class="px-5 py-3 text-right font-mono text-on-surface">ZMW {{ number_format((float) $data['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-on-surface-variant">No fee data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Payment Methods --}}
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-outline-variant">
                    <h2 class="font-label-sm font-label-sm font-semibold text-on-surface">Payment Methods</h2>
                    <p class="text-label-sm text-on-surface-variant">Collection channel breakdown</p>
                </div>
                <table class="w-full text-body-sm">
                    <thead>
                        <tr class="border-b border-outline-variant text-left text-label-sm text-on-surface-variant">
                            <th class="px-5 py-3 font-semibold">Method</th>
                            <th class="px-5 py-3 font-semibold">Count</th>
                            <th class="px-5 py-3 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData['payment_methods'] ?? [] as $method => $data)
                            <tr class="border-b last:border-0 border-outline-variant/60">
                                <td class="px-5 py-3 text-on-surface capitalize">{{ str_replace('_', ' ', $method) ?: 'Other' }}</td>
                                <td class="px-5 py-3 text-on-surface">{{ $data['count'] }}</td>
                                <td class="px-5 py-3 text-right font-mono text-on-surface">ZMW {{ number_format((float) $data['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-on-surface-variant">No payment data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>        {{-- Collection Trend (bulk chart) --}}
        @php
            $trend = collect($reportData['trend'] ?? []);
            $trend = $trend->take(-30);
            $max = (float) $trend->max('amount') ?: 1;
            $barColor = $hasBalance ? '#b3261e' : '#005f2a';
        @endphp
        <div class="mt-8 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-label-sm font-label-sm font-semibold text-on-surface">Collection Trend</h2>
                    <p class="text-label-sm text-on-surface-variant">Daily collection activity (last {{ $trend->count() }} days with payments)</p>
                </div>
            </div>
            @if($trend->isEmpty())
                <p class="py-14 text-center text-on-surface-variant text-body-sm">No collection activity in the selected range.</p>
            @else
                <div class="flex items-end gap-1 h-48 overflow-hidden">
                    @foreach ($trend as $point)
                        @php
                            $h = round(((float) $point->amount / $max) * 100, 1);
                            $label = \Carbon\Carbon::parse($point->date)->format('d M');
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end h-full min-w-0" title="{{ $label }}: ZMW {{ number_format((float) $point->amount, 2) }}">
                            <div class="w-full rounded-t" style="height: {{ $h }}%; background-color: {{ $barColor }};"
                                title="{{ $label }}: ZMW {{ number_format((float) $point->amount, 2) }}"></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-2 text-[10px] text-on-surface-variant">
                    @if($trend->isNotEmpty())
                        <span>{{ \Carbon\Carbon::parse($trend->first()->date)->format('d M Y') }}</span>
                        <span>{{ \Carbon\Carbon::parse($trend->last()->date)->format('d M Y') }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Top Payers --}}
        <div class="mt-8 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-outline-variant">
                <h2 class="font-label-sm font-label-sm font-semibold text-on-surface">Top Payers</h2>
                <p class="text-label-sm text-on-surface-variant">Students with the highest total payments</p>
            </div>
            <table class="w-full text-body-sm">
                <thead>
                    <tr class="border-b border-outline-variant text-left text-label-sm text-on-surface-variant">
                        <th class="px-5 py-3 font-semibold">#</th>
                        <th class="px-5 py-3 font-semibold">Student</th>
                        <th class="px-5 py-3 font-semibold">Class</th>
                        <th class="px-5 py-3 font-semibold text-right">Total Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['top_payers'] ?? [] as $index => $payer)
                        <tr class="border-b last:border-0 border-outline-variant/60">
                            <td class="px-5 py-3 text-on-surface-variant">{{ $index + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $payer->student->full_name ?? 'Unknown' }}</td>
                            <td class="px-5 py-3 text-on-surface-variant">{{ $payer->student->schoolClass->class_name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 text-right font-mono text-green-700">ZMW {{ number_format((float) $payer->total_paid, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-on-surface-variant">No payment data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Overdue Fees --}}
        <div class="mt-8 rounded-xl border border-error/20 bg-error-container/20 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-error/20 flex items-center justify-between">
                <div>
                    <h2 class="font-label-sm font-label-sm font-semibold text-error">Overdue Fees</h2>
                    <p class="text-label-sm text-on-surface-variant">Fees past their due date</p>
                </div>
            </div>
            <table class="w-full text-body-sm">
                <thead>
                    <tr class="border-b border-error/20 text-left text-label-sm text-error">
                        <th class="px-5 py-3 font-semibold">Student</th>
                        <th class="px-5 py-3 font-semibold">Class</th>
                        <th class="px-5 py-3 font-semibold">Amount Due</th>
                        <th class="px-5 py-3 font-semibold">Due Date</th>
                        <th class="px-5 py-3 font-semibold">Days Overdue</th>
                        <th class="px-5 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData['overdue_fees'] ?? [] as $fee)
                        <tr class="border-b last:border-0 border-error/10">
                            <td class="px-5 py-3 font-medium text-on-surface">{{ $fee->student->full_name ?? 'Unknown' }}</td>
                            <td class="px-5 py-3 text-error">{{ $fee->student->schoolClass->class_name ?? 'N/A' }}</td>
                            <td class="px-5 py-3 font-mono text-error">ZMW {{ number_format((float) $fee->amount_due, 2) }}</td>
                            <td class="px-5 py-3 text-error">{{ $fee->due_date->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-mono text-error">{{ $fee->due_date->diffInDays(now()) }} days</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.fees.show', $fee) }}" class="text-label-sm text-primary hover:underline">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-on-surface-variant">No overdue fees. 🎉</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>
@endsection