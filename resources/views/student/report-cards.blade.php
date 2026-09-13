@extends('layouts.student')

@section('title', 'Report Cards')
@section('page-title', 'Report Cards')
@section('page-subtitle', 'Your termly academic reports')

@section('content')
    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
        <header class="px-space-md py-space-sm border-b border-outline-variant/50">
            <h2 class="text-headline-sm font-headline-sm text-on-surface">Available Reports</h2>
        </header>

        @if ($cards->isEmpty())
            @include('student.partials.empty-state', [
                'icon'    => 'description',
                'title'   => 'No report cards yet',
                'message' => 'A report card appears here once your class teacher has finalized the term.',
            ])
        @else
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($cards as $card)
                    <li class="flex flex-wrap items-center gap-space-md px-space-md py-space-sm">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">description</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-on-surface truncate">{{ $card->term?->name }}</p>
                            <p class="text-body-sm text-on-surface-variant truncate">
                                {{ $card->term?->academicYear?->label }}
                                @if ($card->term?->start_date && $card->term?->end_date)
                                    · {{ $card->term->start_date->format('j M') }} – {{ $card->term->end_date->format('j M Y') }}
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-space-lg text-center shrink-0">
                            <div>
                                <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Average</p>
                                <p class="font-data-mono text-data-mono font-semibold {{ ($card->term_average ?? 0) >= 50 ? 'text-success' : 'text-error' }}">
                                    {{ $card->term_average !== null ? $card->term_average . '%' : '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Position</p>
                                <p class="font-data-mono text-data-mono font-semibold text-on-surface">{{ $card->rank_label ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('student.report-card', $card->term_id) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-outline-variant/60 text-on-surface-variant hover:bg-surface-container-low hover:text-primary text-label-md font-medium transition-colors">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                                <span class="hidden sm:inline">View</span>
                            </a>
                            <a href="{{ route('student.report-card', ['term' => $card->term_id, 'download' => 1]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary text-on-primary hover:bg-on-primary-fixed-variant text-label-md font-semibold transition-colors">
                                <span class="material-symbols-outlined text-lg">download</span>
                                <span class="hidden sm:inline">PDF</span>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
