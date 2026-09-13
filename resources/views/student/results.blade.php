@extends('layouts.student')

@section('title', 'My Results')
@section('page-title', 'My Results')
@section('page-subtitle', $student->schoolClass?->class_name ? 'Class ' . $student->schoolClass->class_name : 'Academic performance')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-space-md mb-space-lg">
        <div class="flex items-center gap-space-md">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 px-space-md py-3 card-shadow">
                <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Overall Average</p>
                <p class="text-display-md font-display-md {{ ($overallAverage ?? 0) >= 50 ? 'text-success' : 'text-error' }}">
                    {{ $overallAverage !== null ? $overallAverage . '%' : '—' }}
                </p>
            </div>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <label for="term_id" class="text-label-md text-on-surface-variant">Term</label>
            <select name="term_id" id="term_id" onchange="this.form.submit()"
                class="rounded-xl border-outline-variant/60 bg-surface-container-lowest text-body-md focus:border-secondary focus:ring-secondary/40">
                <option value="">All terms</option>
                @foreach ($terms as $option)
                    <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                        {{ $option->name }} ({{ $option->academicYear?->label }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($bySubject->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow">
            @include('student.partials.empty-state', [
                'icon'    => 'grade',
                'title'   => 'No results for this selection',
                'message' => 'Once your teachers record marks they will appear here, grouped by subject.',
            ])
        </div>
    @else
        <div class="space-y-space-md" x-data="{ open: null }">
            @foreach ($bySubject as $index => $row)
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
                    <button type="button" @click="open === {{ $index }} ? open = null : open = {{ $index }}"
                        class="w-full flex items-center gap-space-md px-space-md py-space-sm text-left hover:bg-surface-container-low/60 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">menu_book</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-on-surface truncate">{{ $row['subject'] }}</p>
                            @if ($row['teacher'])
                                <p class="text-body-sm text-on-surface-variant truncate">{{ $row['teacher'] }}</p>
                            @endif
                        </div>

                        <div class="hidden sm:flex items-center gap-space-lg text-center">
                            <div>
                                <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">CA</p>
                                <p class="font-data-mono text-data-mono text-on-surface">{{ $row['ca_average'] !== null ? $row['ca_average'] . '%' : '—' }}</p>
                            </div>
                            <div>
                                <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Exam</p>
                                <p class="font-data-mono text-data-mono text-on-surface">{{ $row['exam_average'] !== null ? $row['exam_average'] . '%' : '—' }}</p>
                            </div>
                        </div>

                        <div class="text-right shrink-0 w-20">
                            <p class="text-label-sm uppercase tracking-wider text-on-surface-variant">Overall</p>
                            <p class="text-headline-sm font-headline-sm {{ ($row['overall'] ?? 0) >= 50 ? 'text-success' : 'text-error' }}">
                                {{ $row['overall'] !== null ? $row['overall'] . '%' : '—' }}
                            </p>
                        </div>

                        <span class="material-symbols-outlined text-outline shrink-0"
                              x-text="open === {{ $index }} ? 'expand_less' : 'expand_more'">expand_more</span>
                    </button>

                    <div x-show="open === {{ $index }}" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         class="border-t border-outline-variant/50 overflow-x-auto">
                        <table class="w-full text-body-md">
                            <thead class="bg-surface-container-low text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-space-md py-2.5">Assessment</th>
                                    <th class="text-left font-medium px-space-md py-2.5">Term</th>
                                    <th class="text-right font-medium px-space-md py-2.5">Score</th>
                                    <th class="text-right font-medium px-space-md py-2.5">%</th>
                                    <th class="text-right font-medium px-space-md py-2.5">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @foreach ($row['entries'] as $entry)
                                    <tr>
                                        <td class="px-space-md py-2.5 text-on-surface">{{ $entry->assessment_type }}</td>
                                        <td class="px-space-md py-2.5 text-on-surface-variant">{{ $entry->term ?: '—' }}</td>
                                        <td class="px-space-md py-2.5 text-right font-data-mono text-data-mono">
                                            {{ rtrim(rtrim(number_format((float) $entry->score, 1), '0'), '.') }}/{{ rtrim(rtrim(number_format((float) $entry->max_score, 1), '0'), '.') }}
                                        </td>
                                        <td class="px-space-md py-2.5 text-right font-data-mono text-data-mono">{{ $entry->percentage }}%</td>
                                        <td class="px-space-md py-2.5 text-right font-semibold {{ $entry->percentage >= 50 ? 'text-success' : 'text-error' }}">
                                            {{ $entry->letter_grade }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
