@extends('layouts.parent')

@section('title', 'Fees – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Fees</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    @if ($student)
                        {{ $student->full_name }} · {{ $student->schoolClass?->display_name ?? ($student->schoolClass?->class_name ?? '—') }}
                    @endif
                </p>
            </div>
            @if ($overdueCount > 0)
                <span class="text-xs font-bold text-red-700 bg-red-50 px-2 py-1 rounded flex items-center gap-1 self-start">
                    <span class="material-symbols-outlined" style="font-size:14px">warning</span>
                    {{ $overdueCount }} overdue
                </span>
            @endif
        </div>

        {{-- ── Overdue fee notice (Phase 7). One child in view, so no name needed. ── --}}
        @include('parent.partials.overdue-banner', ['showChild' => false])

        {{-- ── Summary ── --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Total Billed</span>
                    <span class="w-9 h-9 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface">ZMW {{ number_format($totalDue, 2) }}</p>
                <p class="text-xs text-on-surface-variant mt-1">All fees invoiced</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Total Paid</span>
                    <span class="w-9 h-9 bg-green-50 text-green-700 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">savings</span>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-green-700">ZMW {{ number_format($totalPaid, 2) }}</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ $payments->count() }} payment{{ $payments->count() === 1 ? '' : 's' }} made</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm {{ $balance > 0 ? 'border-error/40' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Balance</span>
                    <span class="w-9 h-9 {{ $balance > 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700' }} rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">{{ $balance > 0 ? 'account_balance_wallet' : 'verified' }}</span>
                    </span>
                </div>
                <p class="text-2xl font-extrabold {{ $balance > 0 ? 'text-red-600' : 'text-green-700' }}">ZMW {{ number_format($balance, 2) }}</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ $balance > 0 ? 'Outstanding' : 'Fully settled' }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Next Due</span>
                    <span class="w-9 h-9 bg-amber-50 text-amber-700 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">event</span>
                    </span>
                </div>
                <p class="text-lg font-extrabold text-on-surface leading-tight mt-2">
                    @if ($nextDue)
                        ZMW {{ number_format($nextDue->balance ?? ($nextDue->amount_due - $nextDue->amount_paid), 2) }}
                    @else
                        —
                    @endif
                </p>
                <p class="text-xs text-on-surface-variant mt-1">
                    @if ($nextDue)
                        {{ $nextDue->description }} · due {{ \Carbon\Carbon::parse($nextDue->due_date)->format('M d, Y') }}
                    @else
                        No upcoming fees
                    @endif
                </p>
            </div>
        </div>

        {{-- ── Account credit, from a prior overpayment (Student::grantCredit()) ── --}}
        @if (($creditBalance ?? 0) > 0)
            <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-green-700" style="font-size:20px">savings</span>
                <div>
                    <p class="text-sm font-semibold text-green-800">
                        ZMW {{ number_format($creditBalance, 2) }} account credit available
                    </p>
                    <p class="text-xs text-green-700 mt-0.5">
                        From a previous overpayment. It's applied automatically to {{ $student->first_name }}'s next fee — no action needed.
                    </p>
                </div>
            </div>
        @endif

        {{-- ── How to pay: instructions + per-fee reference (no money moves here) ── --}}
        @include('parent.partials.how-to-pay')

        {{-- ── Submit proof of a payment made outside the system, for admin review ── --}}
        @include('parent.partials.proof-of-payment')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- ── Fee invoices ── --}}
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Fee Invoices</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Billed fees for your child</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-surface-container text-on-surface-variant">
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Description</th>
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide">Due</th>
                                <th class="px-5 py-2.5 text-right text-[10px] font-semibold uppercase tracking-wide">Amount</th>
                                <th class="px-5 py-2.5 text-right text-[10px] font-semibold uppercase tracking-wide">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($fees as $fee)
                                @php
                                    $paid = (float) ($fee->amount_paid ?? 0);
                                    $owed = max(0, (float) $fee->amount_due - $paid);
                                    $isOverdue = $owed > 0 && $fee->due_date && now()->gt($fee->due_date);
                                    $statusLabel = $owed <= 0 ? 'Paid' : ($isOverdue ? 'Overdue' : 'Pending');
                                    $statusClass = $owed <= 0
                                        ? 'bg-green-50 text-green-700'
                                        : ($isOverdue ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700');
                                @endphp
                                <tr class="hover:bg-surface-container transition-colors">
                                    <td class="px-5 py-3 font-semibold text-on-surface">{{ $fee->description ?? ($fee->fee_type ?? 'School fee') }}</td>
                                    <td class="px-5 py-3 text-on-surface-variant">
                                        {{ $fee->due_date ? \Carbon\Carbon::parse($fee->due_date)->format('M d, Y') : '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-bold text-on-surface">ZMW {{ number_format($fee->amount_due, 2) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center">
                                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">receipt_long</span>
                                        <p class="font-label-sm text-label-sm text-on-surface mt-2">No fees billed yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Payment history ── --}}
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Payment History</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Payments recorded by the bursar</p>
                </div>
                <ul class="divide-y divide-outline-variant">
                    @forelse ($payments as $payment)
                        <li class="px-5 py-3.5 flex items-center gap-3 hover:bg-surface-container transition-colors">
                            <span class="w-9 h-9 shrink-0 bg-green-50 text-green-700 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-on-surface text-sm">ZMW {{ number_format($payment->amount, 2) }}</p>
                                <p class="text-xs text-on-surface-variant mt-0.5">
                                    {{ $payment->method_label }}
                                    @if ($payment->payment_date)
                                        · {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
                                    @endif
                                </p>
                            </div>
                            @if ($payment->reference_number)
                                <span class="text-[10px] font-mono text-on-surface-variant bg-surface-container px-2 py-1 rounded">{{ $payment->reference_number }}</span>
                            @endif

                            {{-- Phase 7: parent-facing receipt, opens the print-friendly view. --}}
                            <a href="{{ route('parent.payments.receipt', $payment->payment_id) }}"
                               target="_blank" rel="noopener"
                               title="View receipt"
                               class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-primary hover:text-white hover:border-primary transition-colors">
                                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                <span class="text-xs font-medium hidden sm:inline">Receipt</span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center">
                            <span class="material-symbols-outlined text-3xl text-on-surface-variant">payments</span>
                            <p class="font-label-sm text-label-sm text-on-surface mt-2">No payments recorded yet</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

