@extends('layouts.app')

@section('title', 'Statement - ' . $student->full_name)

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm"><a href="{{ route('admin.students.index') }}" class="hover:text-primary">Students</a></span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Statement of Account</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Statement of Account</h1>
                <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $student->full_name }} · {{ $student->student_number }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.financials', $student->student_id) }}"
                    class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined text-lg">arrow_back</span> Financials
                </a>
                <button onclick="window.print()"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary font-label-sm text-label-sm rounded-lg hover:opacity-90 transition-opacity shadow-md">
                    <span class="material-symbols-outlined text-lg">print</span> Print
                </button>
            </div>
        </div>

        @php
            $totalDue = 0.0; $totalPaid = 0.0; $totalBal = 0.0;
        @endphp

        <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
            {{-- Statement header --}}
            <div class="flex flex-col gap-2 border-b border-outline-variant pb-4 mb-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Grail SIS — {{ $student->schoolClass->class_name ?? 'Unassigned' }}</h2>
                    <p class="text-body-sm text-on-surface-variant mt-1">Statement of fees and payments</p>
                </div>
                <div class="text-body-sm text-on-surface">
                    <p><strong>{{ $student->full_name }}</strong></p>
                    <p class="text-on-surface-variant">{{ $student->student_number }}</p>
                    @if($student->guardian)
                        <p class="text-on-surface-variant">{{ $student->guardian->name }}</p>
                    @endif
                </div>
            </div>

            <table class="w-full text-body-sm">
                <thead>
                    <tr class="border-b-2 border-outline-variant text-left text-label-sm text-on-surface-variant">
                        <th class="py-2 pr-2 font-semibold">Date Due</th>
                        <th class="py-2 pr-2 font-semibold">Description</th>
                        <th class="py-2 pr-2 font-semibold">Period</th>
                        <th class="py-2 pr-2 font-semibold text-right">Charge</th>
                        <th class="py-2 pr-2 font-semibold text-right">Payments</th>
                        <th class="py-2 font-semibold text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fees as $fee)
                        @php
                            $totalDue += (float) $fee->amount_due;
                            $totalPaid += (float) $fee->amount_paid;
                            $totalBal += (float) $fee->balance;
                        @endphp
                        <tr class="border-b border-outline-variant/60 align-top">
                            <td class="py-3 pr-2 whitespace-nowrap text-on-surface">{{ $fee->due_date->format('d M Y') }}</td>
                            <td class="py-3 pr-2">
                                <p class="font-medium text-on-surface">{{ $fee->description ?: ('Fee #' . $fee->fee_id) }}</p>
                                <p class="text-xs text-on-surface-variant">
                                    {{ $fee->academicYear?->label ?? '' }}/{{ $fee->term?->name ?? '' }}
                                </p>
                            </td>
                            <td class="py-3 pr-2 text-on-surface-variant">
                                @foreach ($fee->feeItems as $item)
                                    <p class="whitespace-nowrap">{{ $item->item_name }} — ZMW {{ number_format((float) $item->amount, 2) }}</p>
                                @endforeach
                            </td>
                            <td class="py-3 pr-2 text-right font-mono text-on-surface">ZMW {{ number_format((float) $fee->amount_due, 2) }}</td>
                            <td class="py-3 pr-2 text-right font-mono text-green-700">ZMW {{ number_format((float) $fee->amount_paid, 2) }}</td>
                            <td class="py-3 text-right font-mono {{ $fee->balance > 0 ? 'text-error' : 'text-on-surface' }}">
                                ZMW {{ number_format((float) $fee->balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-on-surface-variant">No fees recorded for this student.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-outline-variant font-semibold text-on-surface">
                        <td class="pt-3 pr-2 font-headline-sm">Totals</td>
                        <td class="pt-3 pr-2"></td>
                        <td class="pt-3 pr-2"></td>
                        <td class="pt-3 pr-2 text-right font-mono">ZMW {{ number_format($totalDue, 2) }}</td>
                        <td class="pt-3 pr-2 text-right font-mono text-green-700">ZMW {{ number_format($totalPaid, 2) }}</td>
                        <td class="pt-3 text-right font-mono {{ $totalBal > 0 ? 'text-error' : 'text-on-surface' }}">ZMW {{ number_format($totalBal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <p class="mt-6 text-xs text-on-surface-variant">Generated {{ now()->format('d M Y H:i') }} · Grail School Information System</p>
        </div>

    </main>
@endsection