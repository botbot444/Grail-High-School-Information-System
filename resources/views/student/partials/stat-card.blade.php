{{--
    Included with @include, so variables arrive as plain locals (not component
    props). Defaults are applied here because @include does not fill gaps.

    Usage: @include('student.partials.stat-card', ['icon' => 'grade', 'label' => 'Average', 'value' => '82%'])
--}}
@php
    $meta = $meta ?? null;
    $tone = $tone ?? 'primary';

    $tones = [
        'primary' => ['bg' => 'bg-primary/10',        'fg' => 'text-primary'],
        'success' => ['bg' => 'bg-success-container', 'fg' => 'text-success'],
        'warning' => ['bg' => 'bg-warning-container', 'fg' => 'text-warning'],
        'error'   => ['bg' => 'bg-error-container',   'fg' => 'text-error'],
    ];
    $t = $tones[$tone] ?? $tones['primary'];
@endphp

<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 p-space-md card-shadow">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">{{ $label }}</p>
            <p class="mt-1 text-display-md font-display-md text-on-surface truncate">{{ $value }}</p>
            @if ($meta)
                <p class="mt-1 text-body-sm text-on-surface-variant truncate">{{ $meta }}</p>
            @endif
        </div>
        <div class="w-11 h-11 rounded-xl {{ $t['bg'] }} {{ $t['fg'] }} flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
        </div>
    </div>
</div>
