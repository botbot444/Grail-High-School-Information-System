@extends('layouts.app')

@section('title', 'Fee Aging Report')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Reports</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Fee Aging</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Fee Aging</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Outstanding balances by how long they have been past due, as at {{ $report['as_of']->format('j M Y') }}.
                </p>
            </div>
            <a href="{{ route('admin.reports.aging.export', request()->query()) }}"
                class="self-start inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Export CSV</span>
            </a>
        </div>

        <form method="GET" class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px]">
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1">Class</label>
                    <select name="class_id" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All classes</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->class_id }}" @selected(($filters['class_id'] ?? null) == $c->class_id)>{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- Buckets --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            @foreach ($report['buckets'] as $bucket => $data)
                @php
                    $tone = match ($bucket) {
                        'Not yet due' => ['bg-surface-container-lowest', 'text-on-surface'],
                        '1–30 days'   => ['bg-amber-50', 'text-amber-800'],
                        '31–60 days'  => ['bg-orange-50', 'text-orange-800'],
                        '61–90 days'  => ['bg-red-50', 'text-red-700'],
                        default       => ['bg-red-100', 'text-red-800'],
                    };
                @endphp
                <div class="{{ $tone[0] }} p-5 rounded-xl border border-outline-variant shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide {{ $tone[1] }}">{{ $bucket }}</p>
                    <p class="mt-1 font-headline-md text-headline-md font-extrabold {{ $tone[1] }}">
                        ZMW {{ number_format($data['balance'], 2) }}
                    </p>
                    <p class="text-xs {{ $tone[1] }} opacity-80 mt-0.5">{{ $data['count'] }} fee{{ $data['count'] === 1 ? '' : 's' }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- By class --}}
            <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden self-start">
                <div class="px-5 py-4 border-b border-outline-variant">
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">By Class</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Largest outstanding first</p>
                </div>
                <ul class="divide-y divide-outline-variant/40">
                    @forelse ($report['by_class'] as $row)
                        <li class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-on-surface text-sm">{{ $row['class'] }}</p>
                                <p class="text-xs text-on-surface-variant">
                                    {{ $row['students'] }} student{{ $row['students'] === 1 ? '' : 's' }} ·
                                    worst {{ $row['worst'] }} day{{ $row['worst'] === 1 ? '' : 's' }} late
                                </p>
                            </div>
                            <p class="font-mono text-sm font-bold text-on-surface whitespace-nowrap">
                                ZMW {{ number_format($row['balance'], 2) }}
                            </p>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-on-surface-variant">Nothing outstanding.</li>
                    @endforelse
                </ul>
            </section>

            {{-- Detail --}}
            <section class="lg:col-span-2 rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Outstanding Fees</h2>
                        <p class="text-xs text-on-surface-variant mt-0.5">Most overdue first</p>
                    </div>
                    <p class="text-sm font-bold text-on-surface">
                        Total ZMW {{ number_format($report['summary']['balance'], 2) }}
                        <span class="font-normal text-xs text-on-surface-variant">
                            ({{ $report['summary']['students'] }} students)
                        </span>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full font-body-md text-body-md">
                        <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="text-left font-medium px-4 py-3">Student</th>
                                <th class="text-left font-medium px-4 py-3">Fee</th>
                                <th class="text-right font-medium px-4 py-3">Days late</th>
                                <th class="text-right font-medium px-4 py-3">Balance</th>
                                <th class="text-left font-medium px-4 py-3">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @forelse ($report['rows'] as $row)
                                <tr class="hover:bg-surface-container-low/60 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-on-surface">{{ $row['student']?->full_name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $row['class'] }}</p>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <p class="text-on-surface">{{ $row['description'] }}</p>
                                        <p class="text-xs text-on-surface-variant">
                                            due {{ $row['due_date']?->format('j M Y') ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <span @class([
                                            'font-mono text-sm font-semibold',
                                            'text-on-surface-variant' => $row['days_late'] === 0,
                                            'text-amber-700' => $row['days_late'] > 0 && $row['days_late'] <= 60,
                                            'text-error' => $row['days_late'] > 60,
                                        ])>{{ $row['days_late'] > 0 ? $row['days_late'] : '—' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-sm font-bold text-on-surface whitespace-nowrap">
                                        ZMW {{ number_format($row['balance'], 2) }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <a href="{{ route('admin.fees.show', $row['fee']->fee_id) }}"
                                           class="font-mono text-xs text-primary hover:underline">{{ $row['fee']->payment_reference }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">
                                    Nothing outstanding — every fee is settled.
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
