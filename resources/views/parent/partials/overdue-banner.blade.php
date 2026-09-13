{{--
    Phase 7 — in-app overdue fee notice.

    Included with @include, so `overdueFees` arrives as a plain local. Renders
    nothing when there is nothing overdue, which keeps the include harmless on
    every page it sits on.
--}}
@php
    $overdueFees  = $overdueFees ?? collect();
    $overdueTotal = $overdueTotal ?? $overdueFees->sum(fn ($fee) => max(0, (float) $fee->amount_due - (float) $fee->amount_paid));
    $showChild    = $showChild ?? true;
@endphp

@if ($overdueFees->isNotEmpty())
    <div class="bg-error-container border border-error/30 rounded-xl overflow-hidden shadow-sm" role="alert">
        <div class="px-5 py-4 flex flex-wrap items-start gap-4">
            <span class="w-10 h-10 shrink-0 bg-error text-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-[22px]">warning</span>
            </span>

            <div class="min-w-0 flex-1">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-error-container">
                    {{ $overdueFees->count() === 1 ? 'A fee is overdue' : $overdueFees->count() . ' fees are overdue' }}
                </h2>
                <p class="text-sm text-on-error-container/90 mt-1">
                    ZMW {{ number_format($overdueTotal, 2) }} is past its due date. Please settle with the
                    school bursar, or contact the office if you have already paid.
                </p>

                <ul class="mt-3 space-y-1.5">
                    @foreach ($overdueFees->take(4) as $fee)
                        @php
                            $balance = max(0, (float) $fee->amount_due - (float) $fee->amount_paid);
                            $daysLate = $fee->due_date ? (int) $fee->due_date->diffInDays(now()) : null;
                        @endphp
                        <li class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 text-sm">
                            <a href="{{ route('parent.fees.show', $fee->fee_id) }}"
                               class="font-semibold text-on-error-container underline underline-offset-2 hover:no-underline">
                                {{ $fee->description ?: 'Fee' }}
                            </a>
                            @if ($showChild && $fee->student)
                                <span class="text-on-error-container/80">· {{ $fee->student->full_name }}</span>
                            @endif
                            <span class="text-on-error-container/80">· ZMW {{ number_format($balance, 2) }}</span>
                            @if ($daysLate !== null)
                                <span class="text-xs font-mono bg-error/10 text-on-error-container px-1.5 py-0.5 rounded">
                                    {{ $daysLate }} day{{ $daysLate === 1 ? '' : 's' }} late
                                </span>
                            @endif
                        </li>
                    @endforeach

                    @if ($overdueFees->count() > 4)
                        <li class="text-sm text-on-error-container/80">
                            and {{ $overdueFees->count() - 4 }} more…
                        </li>
                    @endif
                </ul>
            </div>

            <a href="{{ route('parent.fees') }}"
               class="shrink-0 self-center text-xs font-bold bg-error text-white rounded-lg px-3 py-2 hover:bg-error/90 transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined" style="font-size:14px">payments</span>
                View fees
            </a>
        </div>
    </div>
@endif
