@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        <div class="mb-8">
            <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                <span class="text-label-sm font-label-sm">Settings</span>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="text-label-sm font-label-sm text-primary font-bold">Payment Details</span>
            </nav>
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Payment Details</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                Where parents send fee payments. These appear on every parent's fees page alongside the
                exact amount owed and a reference code. Leave a channel blank to hide it.
            </p>
        </div>

        @if (session('notification'))
            <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
                {{ session('notification') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3">
                <ul class="list-disc ml-5 font-body-sm text-body-sm text-on-error-container space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.payments.update') }}" class="max-w-3xl">
            @csrf
            @method('PUT')

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5 mb-5">
                <h2 class="text-headline-sm font-bold text-on-surface mb-1">Bank transfer / deposit</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">
                    Shown for parents paying over the counter or by transfer.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ([
                        'payment_bank_name'           => 'Bank name',
                        'payment_bank_account_name'   => 'Account name',
                        'payment_bank_account_number' => 'Account number',
                        'payment_bank_branch'         => 'Branch / sort code',
                    ] as $key => $label)
                        <div>
                            <label for="{{ $key }}" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">
                                {{ $label }}
                            </label>
                            <input type="text" name="{{ $key }}" id="{{ $key }}"
                                value="{{ old($key, $settings[$key] ?? '') }}"
                                class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5 mb-5">
                <h2 class="text-headline-sm font-bold text-on-surface mb-1">Mobile money</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">
                    The school's registered numbers. Parents send from their own wallet and quote the reference.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ([
                        'payment_momo_mtn'    => 'MTN Mobile Money number',
                        'payment_momo_airtel' => 'Airtel Money number',
                    ] as $key => $label)
                        <div>
                            <label for="{{ $key }}" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">
                                {{ $label }}
                            </label>
                            <input type="text" name="{{ $key }}" id="{{ $key }}"
                                value="{{ old($key, $settings[$key] ?? '') }}"
                                placeholder="e.g. 0977 000 000"
                                class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5 mb-5">
                <h2 class="text-headline-sm font-bold text-on-surface mb-1">Note to parents</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">
                    Anything else they should do — for example, bringing the deposit slip to the bursar's office.
                </p>
                <textarea name="payment_note" id="payment_note" rows="3" maxlength="1000"
                    class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40"
                    placeholder="Bring your deposit slip or mobile money confirmation to the bursar's office so your payment can be recorded.">{{ old('payment_note', $settings['payment_note'] ?? '') }}</textarea>
            </section>

            <div class="rounded-lg border border-outline-variant bg-surface-container px-4 py-3 mb-5">
                <p class="font-body-sm text-body-sm text-on-surface-variant">
                    <strong class="text-on-surface">This does not take payments.</strong>
                    Parents pay through their own bank or mobile money app, then the bursar records it here as
                    usual. Nothing on this page moves money.
                </p>
            </div>

            <button type="submit"
                class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span>Save payment details</span>
            </button>
        </form>
    </div>
@endsection
