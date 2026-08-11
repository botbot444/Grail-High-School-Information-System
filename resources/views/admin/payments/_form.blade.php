@php
    /** @var \App\Models\Fee $fee */
@endphp

<section class="mt-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-headline-sm font-bold text-on-surface">Record Payment</h3>
        <span class="text-label-sm text-on-surface-variant">
            Balance: <strong class="text-on-surface">ZMW {{ number_format($fee->balance, 2) }}</strong>
        </span>
    </div>

    <form method="POST" action="{{ route('admin.fees.payments.store', $fee->fee_id) }}" class="space-y-3">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-1">Amount *</label>
                <input type="number" step="0.01" min="0.01" max="{{ $fee->balance }}" name="amount" value="{{ old('amount') }}" required
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
                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="mobile_money" {{ old('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
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
                <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-on-surface mb-1">Notes</label>
                <textarea name="notes" rows="2"
                    class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                Record Payment
            </button>
            <a href="{{ route('admin.fees.show', $fee->fee_id) }}" class="px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">
                Cancel
            </a>
        </div>
    </form>
</section>
