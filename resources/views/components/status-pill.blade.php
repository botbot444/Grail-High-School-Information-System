@props(['status'])

@php
    $map = [
        'Cleared'        => 'bg-green-100 text-green-700 border-green-200',
        'Partially Paid' => 'bg-secondary-fixed/15 text-secondary border-secondary/20',
        'Pending'        => 'bg-warning/15 text-warning border-warning/20',
        'Overdue'        => 'bg-error/15 text-error border-error/20',
    ];
    $classes = $map[$status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm font-semibold border {{ $classes }}">
    {{ $status }}
</span>