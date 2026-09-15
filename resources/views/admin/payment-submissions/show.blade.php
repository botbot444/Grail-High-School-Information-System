@extends('layouts.app')

@section('title', 'Review Payment Submission')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Payment Approvals</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Submission #{{ $submission->submission_id }}</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    {{ $submission->fee->student->full_name ?? 'N/A' }}
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ $submission->fee->term }} / {{ $submission->fee->academic_year }} · {{ $submission->fee->description ?? 'School fee' }}
                </p>
            </div>
            <a href="{{ route('admin.payment-submissions.index') }}"
                class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Back
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                {{-- Proof preview --}}
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-4">Proof of Payment</h2>

                    @if ($submission->proof_is_image)
                        <a href="{{ $submission->proof_url }}" target="_blank" rel="noopener">
                            <img src="{{ $submission->proof_url }}" alt="Proof of payment"
                                class="max-w-full rounded-lg border border-outline-variant" />
                        </a>
                    @else
                        <a href="{{ $submission->proof_url }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container border border-outline-variant rounded-lg text-label-sm text-primary font-semibold">
                            <span class="material-symbols-outlined text-lg">description</span>
                            Open {{ $submission->proof_original_filename ?? 'file' }}
                        </a>
                    @endif
                </section>

                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-4">Claimed Payment Details</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Amount</dt>
                            <dd class="text-body-md font-bold text-on-surface font-mono">ZMW {{ number_format($submission->amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Method</dt>
                            <dd class="text-body-md text-on-surface">{{ $submission->method_label }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Reference</dt>
                            <dd class="text-body-md text-on-surface">{{ $submission->reference_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Payment Date</dt>
                            <dd class="text-body-md text-on-surface">{{ $submission->payment_date->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Submitted By</dt>
                            <dd class="text-body-md text-on-surface">{{ $submission->submittedBy->name ?? 'N/A' }} · {{ $submission->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Fee Balance (current)</dt>
                            <dd class="text-body-md text-on-surface font-mono">ZMW {{ number_format($submission->fee->balance, 2) }}</dd>
                        </div>
                        @if ($submission->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Parent's Notes</dt>
                                <dd class="text-body-md text-on-surface">{{ $submission->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                @if (! $submission->isPending())
                    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-3">Review Outcome</h2>
                        <p class="text-body-sm text-on-surface">
                            <strong>{{ $submission->status_label }}</strong> by {{ $submission->reviewedBy->name ?? 'N/A' }}
                            on {{ $submission->reviewed_at?->format('d M Y H:i') }}
                        </p>
                        @if ($submission->review_notes)
                            <p class="mt-2 text-body-sm text-on-surface-variant">{{ $submission->review_notes }}</p>
                        @endif
                        @if ($submission->payment)
                            <a href="{{ route('admin.payments.receipt', $submission->payment) }}" target="_blank"
                                class="mt-3 inline-block text-primary hover:underline text-label-sm font-semibold">View Receipt</a>
                        @endif
                    </section>
                @endif
            </div>

            <div class="space-y-6">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-3">Status</h2>
                    @php
                        $badge = match ($submission->status) {
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default    => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-label-sm font-semibold {{ $badge }}">
                        {{ $submission->status_label }}
                    </span>

                    <p class="mt-4 text-body-sm text-on-surface-variant">
                        Verify this against your own bank statement or mobile money log before approving.
                        Approving creates a real payment record and updates the fee balance immediately.
                    </p>
                </section>

                @if ($submission->isPending())
                    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-3">Approve</h2>
                        <form method="POST" action="{{ route('admin.payment-submissions.approve', $submission->submission_id) }}"
                            onsubmit="return confirm('Confirm you have verified this payment outside the system. Approving will record ZMW {{ number_format($submission->amount, 2) }} against this fee.');">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                                Approve &amp; Record Payment
                            </button>
                        </form>
                    </section>

                    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-3">Reject</h2>
                        <form method="POST" action="{{ route('admin.payment-submissions.reject', $submission->submission_id) }}" class="space-y-3">
                            @csrf
                            <textarea name="review_notes" rows="3" required placeholder="Reason the parent will see, e.g. amount doesn't match our records."
                                class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary text-sm">{{ old('review_notes') }}</textarea>
                            @error('review_notes')
                                <p class="text-error text-label-sm">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="w-full px-4 py-2 bg-error text-on-error rounded-lg text-label-sm font-semibold">
                                Reject
                            </button>
                        </form>
                    </section>
                @endif
            </div>
        </div>
    </main>
@endsection
