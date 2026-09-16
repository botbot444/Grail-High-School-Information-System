@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[88px]" id="mainContent">
        @include('admin.partials.flash')

            <div class="p-8 max-w-3xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">System Settings</h1>
                    <p class="text-on-surface-variant text-sm mt-1">
                        Areas of the system an administrator can configure.
                    </p>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('admin.settings.payments') }}"
                        class="flex items-center gap-4 bg-white p-5 rounded-xl border border-outline-variant shadow-sm hover:border-primary transition-colors">
                        <span class="w-10 h-10 rounded-lg bg-primary-container/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">account_balance_wallet</span>
                        </span>
                        <div class="flex-1">
                            <h2 class="text-base font-bold text-on-surface">Payment Details</h2>
                            <p class="text-sm text-on-surface-variant">Bank and mobile-money instructions shown to parents on the Fees page.</p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}"
                        class="flex items-center gap-4 bg-white p-5 rounded-xl border border-outline-variant shadow-sm hover:border-primary transition-colors">
                        <span class="w-10 h-10 rounded-lg bg-primary-container/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">sell</span>
                        </span>
                        <div class="flex-1">
                            <h2 class="text-base font-bold text-on-surface">Fee Categories</h2>
                            <p class="text-sm text-on-surface-variant">The categories fees can be billed under.</p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
