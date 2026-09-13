@extends('layouts.student')

@section('title', 'Report Cards')
@section('page-title', 'Report Cards')
@section('page-subtitle', 'Termly academic reports')

@section('content')
    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
        <header class="px-space-md py-space-sm border-b border-outline-variant/50">
            <h2 class="text-headline-sm font-headline-sm text-on-surface">Available Terms</h2>
        </header>

        @if ($terms->isEmpty())
            @include('student.partials.empty-state', [
                'icon'    => 'description',
                'title'   => 'No report cards yet',
                'message' => 'A report card becomes available once marks have been recorded for a term.',
            ])
        @else
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($terms as $term)
                    <li class="flex items-center gap-space-md px-space-md py-space-sm">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">description</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-on-surface truncate">{{ $term->name }}</p>
                            <p class="text-body-sm text-on-surface-variant truncate">
                                {{ $term->academicYear?->label }}
                                @if ($term->start_date && $term->end_date)
                                    · {{ $term->start_date->format('j M') }} – {{ $term->end_date->format('j M Y') }}
                                @endif
                            </p>
                        </div>
                        <button type="button" disabled
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-surface-container text-on-surface-variant text-label-md font-medium cursor-not-allowed"
                            title="Report card PDFs arrive with Phase 11">
                            <span class="material-symbols-outlined text-lg">download</span>
                            Download
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="px-space-md py-space-sm border-t border-outline-variant/40 bg-surface-container-low/50">
                <p class="flex items-start gap-2 text-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-sm text-secondary mt-0.5">info</span>
                    <span>
                        Downloads are disabled until report card generation ships. That work — the PDF layout,
                        teacher comments and class rank — is Phase&nbsp;11 of the implementation plan.
                    </span>
                </p>
            </div>
        @endif
    </section>
@endsection
