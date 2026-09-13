{{-- Included with @include — defaults applied locally, see stat-card for rationale. --}}
@php
    $icon    = $icon ?? 'inbox';
    $message = $message ?? null;
    $phase   = $phase ?? null;
@endphp

<div class="flex flex-col items-center justify-center text-center py-space-2xl px-space-md">
    <div class="w-14 h-14 rounded-[50%] bg-surface-container flex items-center justify-center text-outline mb-space-md">
        <span class="material-symbols-outlined text-[28px]">{{ $icon }}</span>
    </div>
    <p class="text-headline-sm font-headline-sm text-on-surface">{{ $title }}</p>
    @if ($message)
        <p class="mt-2 text-body-md text-on-surface-variant max-w-md">{{ $message }}</p>
    @endif
    @if ($phase)
        <span class="mt-space-md inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-secondary-container/60 text-on-secondary-container text-label-sm font-medium">
            <span class="material-symbols-outlined text-sm">schedule</span>
            Scheduled for {{ $phase }}
        </span>
    @endif
</div>
