@extends('layouts.app')

@section('title', 'Fee Details')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Fees</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Fee #{{ $fee->fee_id }}</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    {{ $fee->student->full_name ?? 'N/A' }}
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ $fee->term }} / {{ $fee->academic_year }} · Due {{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.fees.edit', $fee->fee_id) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    Edit
                </a>
                <a href="{{ route('admin.fees.index') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-4">Fee Breakdown</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-outline-variant">
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Item</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Category</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fee->feeItems as $item)
                                    <tr class="border-b border-outline-variant/60">
                                        <td class="py-3 text-body-md text-on-surface">{{ $item->item_name }}</td>
                                        <td class="py-3 text-body-md text-on-surface">{{ $item->category }}</td>
                                        <td class="py-3 text-body-md text-on-surface text-right font-mono">ZMW {{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="pt-3 text-body-md font-semibold text-on-surface">Total Due</td>
                                    <td class="pt-3 text-body-md font-bold text-on-surface text-right font-mono">ZMW {{ number_format($fee->amount_due, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="pt-1 text-body-md text-on-surface">Total Paid</td>
                                    <td class="pt-1 text-body-md text-on-surface text-right font-mono">ZMW {{ number_format($fee->amount_paid, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="pt-1 text-body-md font-semibold text-on-surface">Balance</td>
                                    <td class="pt-1 text-body-md font-bold text-on-surface text-right font-mono">ZMW {{ number_format($fee->balance, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-headline-sm font-bold text-on-surface">Payment History</h2>
                        @if($fee->balance > 0)
                            <button onclick="document.getElementById('payment-section').scrollIntoView({behavior:'smooth'})"
                                class="px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                                Record Payment
                            </button>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-outline-variant">
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Date</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Method</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant text-right">Amount</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Reference</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant">Recorded By</th>
                                    <th class="pb-2 text-label-sm font-semibold text-on-surface-variant text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fee->payments as $payment)
                                    <tr class="border-b border-outline-variant/60">
                                        <td class="py-3 text-body-sm text-on-surface whitespace-nowrap">
                                            {{ $payment->payment_date->format('d M Y') }}
                                        </td>
                                        <td class="py-3 text-body-sm text-on-surface">{{ $payment->method_label }}</td>
                                        <td class="py-3 text-body-sm text-on-surface text-right font-mono">ZMW {{ number_format($payment->amount, 2) }}</td>
                                        <td class="py-3 text-body-sm text-on-surface">{{ $payment->reference_number ?? '-' }}</td>
                                        <td class="py-3 text-body-sm text-on-surface">{{ $payment->recordedBy?->name ?? 'N/A' }}</td>
                                        <td class="py-3 text-center">
                                            <a href="{{ route('admin.payments.receipt', $payment) }}"
                                                target="_blank"
                                                class="text-primary hover:underline text-label-sm">Receipt</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-on-surface-variant text-body-sm">
                                            No payments recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-3">Status</h2>
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
                    <div class="mt-4 space-y-2 text-body-sm text-on-surface">
                        <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($fee->due_date)->format('d M Y') }}</p>
                        <p><strong>Overdue:</strong> {{ $fee->is_overdue ? 'Yes' : 'No' }}</p>
                        <p><strong>Progress:</strong> {{ $fee->payment_progress }}%</p>
                    </div>
                </section>

                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-3">Actions</h2>
                    <div class="space-y-2">
                        @if($fee->balance > 0)
                            <form method="POST" action="{{ route('admin.fees.send-reminder', $fee->fee_id) }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-sm hover:bg-surface-container-high" style="color:#1F4D3D;">
                                    Send Reminder
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.fees.edit', $fee->fee_id) }}"
                            class="block px-3 py-2 rounded-md text-sm hover:bg-surface-container-high" style="color:#177aa4;">
                            Edit Fee
                        </a>
                        <form method="POST" action="{{ route('admin.fees.destroy', $fee->fee_id) }}"
                            onsubmit="return confirm('Delete this fee?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-sm text-error hover:bg-error-container/20">
                                Delete Fee
                            </button>
                        </form>
                    </div>
                </section>

                @if($fee->balance > 0)
                    <div id="payment-section">
                        @include('admin.payments._form', ['fee' => $fee])
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
