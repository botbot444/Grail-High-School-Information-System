{{--
    "How to pay" — payment instructions, not a checkout.

    The school's own bank / mobile money details, the exact amount outstanding,
    and a per-fee reference the bursar can match against a deposit slip or a
    mobile money SMS. No money moves through this application.

    Expects: $student, $fees (the child's fees), $settings.
--}}
@php
    $settings = $settings ?? \App\Models\SchoolSetting::all_settings();

    $bank = array_filter([
        'Bank'           => $settings['payment_bank_name'] ?? null,
        'Account name'   => $settings['payment_bank_account_name'] ?? null,
        'Account number' => $settings['payment_bank_account_number'] ?? null,
        'Branch'         => $settings['payment_bank_branch'] ?? null,
    ]);

    $momo = array_filter([
        'MTN Mobile Money' => $settings['payment_momo_mtn'] ?? null,
        'Airtel Money'     => $settings['payment_momo_airtel'] ?? null,
    ]);

    // Only fees with something still owing are worth quoting a reference for.
    $outstanding = $fees->filter(fn ($fee) => (float) $fee->amount_due - (float) $fee->amount_paid > 0.009)
                        ->sortBy('due_date');
@endphp

@if ($bank || $momo)
    <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-outline-variant">
            <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">How to pay</h2>
            <p class="text-xs text-on-surface-variant mt-0.5">
                Pay through your own bank or mobile money, quoting the reference below.
            </p>
        </div>

        <div class="p-5 space-y-5">
            {{-- What to pay --}}
            @if ($outstanding->isEmpty())
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 flex items-start gap-2">
                    <span class="material-symbols-outlined text-green-700" style="font-size:18px">check_circle</span>
                    <p class="text-sm text-green-800">
                        <strong>Nothing outstanding for {{ $student->first_name }}.</strong>
                        The school's payment details are below if you need them.
                    </p>
                </div>
            @else
            <div>
                <h3 class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant mb-2">
                    What to pay for {{ $student->first_name }}
                </h3>
                <ul class="space-y-2">
                    @foreach ($outstanding as $fee)
                        @php $owing = round((float) $fee->amount_due - (float) $fee->amount_paid, 2); @endphp
                        <li class="rounded-lg border border-outline-variant bg-surface-container px-4 py-3">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-semibold text-on-surface text-sm">{{ $fee->description ?: 'School fees' }}</p>
                                    <p class="text-xs text-on-surface-variant mt-0.5">
                                        {{ $fee->term }} {{ $fee->academic_year }}
                                        @if ($fee->due_date)
                                            · due {{ $fee->due_date->format('j M Y') }}
                                        @endif
                                        @if ($fee->amount_paid > 0)
                                            · ZMW {{ number_format($fee->amount_paid, 2) }} already paid
                                        @endif
                                    </p>
                                </div>
                                <p class="font-extrabold text-primary text-lg whitespace-nowrap">
                                    ZMW {{ number_format($owing, 2) }}
                                </p>
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Reference</span>
                                <code class="font-mono text-sm font-bold tracking-wider bg-white border border-outline-variant rounded px-2 py-1 text-on-surface select-all">{{ $fee->payment_reference }}</code>
                                <button type="button"
                                    class="copy-ref text-xs font-semibold text-primary hover:underline flex items-center gap-1"
                                    data-ref="{{ $fee->payment_reference }}">
                                    <span class="material-symbols-outlined" style="font-size:14px">content_copy</span>
                                    <span class="copy-label">Copy</span>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Where to pay --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if ($momo)
                    <div class="rounded-lg border border-outline-variant p-4">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Mobile money</h3>
                        <dl class="space-y-1.5">
                            @foreach ($momo as $label => $value)
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-xs text-on-surface-variant">{{ $label }}</dt>
                                    <dd class="font-mono text-sm font-semibold text-on-surface select-all">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                @if ($bank)
                    <div class="rounded-lg border border-outline-variant p-4">
                        <h3 class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Bank transfer or deposit</h3>
                        <dl class="space-y-1.5">
                            @foreach ($bank as $label => $value)
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-xs text-on-surface-variant shrink-0">{{ $label }}</dt>
                                    <dd class="font-mono text-sm font-semibold text-on-surface text-right select-all">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>

            {{-- Note + honest disclaimer --}}
            <div class="rounded-lg bg-surface-container border border-outline-variant px-4 py-3">
                <p class="text-xs text-on-surface-variant">
                    @if (filled($settings['payment_note'] ?? null))
                        {{ $settings['payment_note'] }}
                    @else
                        Bring your deposit slip or mobile money confirmation to the bursar's office so your
                        payment can be recorded.
                    @endif
                </p>
                <p class="text-xs text-on-surface-variant mt-2">
                    Payments are not taken on this page. Your balance updates once the bursar records the
                    payment, which is not instant — quoting the reference is what lets them match it quickly.
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.copy-ref').forEach(function (button) {
                button.addEventListener('click', function () {
                    var reference = button.dataset.ref;
                    var label = button.querySelector('.copy-label');

                    function done(text) {
                        label.textContent = text;
                        setTimeout(function () { label.textContent = 'Copy'; }, 1800);
                    }

                    // Clipboard API needs a secure context; fall back to selecting
                    // the code so the parent can copy it by hand.
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(reference).then(
                            function () { done('Copied'); },
                            function () { done('Select it manually'); }
                        );
                        return;
                    }

                    var code = button.previousElementSibling;
                    if (code && window.getSelection) {
                        var range = document.createRange();
                        range.selectNodeContents(code);
                        var selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        done('Selected — press Ctrl/Cmd + C');
                    }
                });
            });
        </script>
    @endpush
@endif
