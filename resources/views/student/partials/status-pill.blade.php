{{-- Included with @include — expects a $status string. --}}
@php
    $statusTones = [
        'Graded'    => 'bg-success-container text-success',
        'Submitted' => 'bg-secondary-container text-on-secondary-container',
        'Pending'   => 'bg-warning-container text-warning',
        'Overdue'   => 'bg-error-container text-error',
        'Present'   => 'bg-success-container text-success',
        'Absent'    => 'bg-error-container text-error',
        'Late'      => 'bg-warning-container text-warning',
        'Excused'   => 'bg-surface-container text-on-surface-variant',
        'Published' => 'bg-success-container text-success',
        'Draft'     => 'bg-surface-container text-on-surface-variant',
    ];
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-xl text-label-sm font-semibold whitespace-nowrap {{ $statusTones[$status] ?? 'bg-surface-container text-on-surface-variant' }}">
    {{ $status }}
</span>
