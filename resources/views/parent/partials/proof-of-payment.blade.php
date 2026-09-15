{{--
    Submit proof of a payment made outside the system (bank deposit, mobile
    money, etc.) for an admin to verify and approve. Nothing here moves money
    or changes a balance — approval is what creates the real Payment record.

    Expects: $student, $fees (the child's fees), $submissions (this child's
    proof-of-payment history, any status).
--}}
@php
    $outstandingFees = $fees->filter(fn ($fee) => (float) $fee->amount_due - (float) $fee->amount_paid > 0.009)
                             ->sortBy('due_date');

    $statusBadge = fn ($status) => match ($status) {
        'approved' => 'bg-green-50 text-green-700',
        'rejected' => 'bg-red-50 text-red-600',
        default    => 'bg-amber-50 text-amber-700',
    };
@endphp

<div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-outline-variant">
        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Submit Proof of Payment</h2>
        <p class="text-xs text-on-surface-variant mt-0.5">
            Already paid outside the system? Upload your deposit slip or mobile money confirmation here.
            An admin will verify it and apply it to the balance — this does not update your balance immediately.
        </p>
    </div>

    <div class="p-5 space-y-5">
        @if ($outstandingFees->isEmpty())
            <p class="text-sm text-on-surface-variant">Nothing outstanding for {{ $student->first_name }} right now.</p>
        @else
            <form method="POST" action="{{ route('parent.payment-proofs.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1">Which fee? *</label>
                        <select name="fee_id" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                            <option value="">— Select fee —</option>
                            @foreach ($outstandingFees as $fee)
                                @php $owing = round((float) $fee->amount_due - (float) $fee->amount_paid, 2); @endphp
                                <option value="{{ $fee->fee_id }}" {{ (string) old('fee_id') === (string) $fee->fee_id ? 'selected' : '' }}>
                                    {{ $fee->description ?: 'School fees' }} · {{ $fee->term }} {{ $fee->academic_year }} — ZMW {{ number_format($owing, 2) }} owing
                                </option>
                            @endforeach
                        </select>
                        @error('fee_id')
                            <p class="mt-1 text-error text-label-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Amount Paid *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                        @error('amount')
                            <p class="mt-1 text-error text-label-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Payment Method *</label>
                        <select name="payment_method" required
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                            <option value="">— Select method —</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="mobile_money" {{ old('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-error text-label-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Reference Number</label>
                        <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}"
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                        @error('payment_date')
                            <p class="mt-1 text-error text-label-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1">Proof of Payment *</label>
                        <input type="file" name="proof" required accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary text-sm" />
                        <p class="mt-1 text-xs text-on-surface-variant">Photo of a deposit slip, or a mobile money SMS/receipt screenshot. JPG, PNG or PDF, up to 5MB.</p>
                        @error('proof')
                            <p class="mt-1 text-error text-label-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-on-surface mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                    Submit for Review
                </button>
            </form>
        @endif

        {{-- ── Submission history ── --}}
        @if ($submissions->isNotEmpty())
            <div class="pt-4 border-t border-outline-variant">
                <h3 class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Your submissions</h3>
                <ul class="space-y-2">
                    @foreach ($submissions as $submission)
                        <li class="rounded-lg border border-outline-variant px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-on-surface">
                                        ZMW {{ number_format($submission->amount, 2) }} · {{ $submission->method_label }}
                                    </p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">
                                        Submitted {{ $submission->created_at->format('d M Y') }}
                                        @if ($submission->reference_number)
                                            · Ref {{ $submission->reference_number }}
                                        @endif
                                    </p>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded {{ $statusBadge($submission->status) }}">
                                    {{ $submission->status_label }}
                                </span>
                            </div>
                            @if ($submission->isRejected() && $submission->review_notes)
                                <p class="mt-2 text-xs text-red-700 bg-red-50 rounded px-2 py-1.5">{{ $submission->review_notes }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
