@extends('layouts.app')

@section('title', 'Report Cards')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-md text-headline-md text-primary font-bold">Report Cards</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Print any student's report card, or unfinalize a class so marks can be corrected.
            </p>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-2">
            @if ($selectedClass)
                <input type="hidden" name="class_id" value="{{ $selectedClass->class_id }}">
            @endif
            <label for="term_id" class="font-label-md text-label-md text-on-surface-variant">Term</label>
            <select name="term_id" id="term_id" onchange="this.form.submit()"
                class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                @foreach ($terms as $option)
                    <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                        {{ $option->name }} ({{ $option->academicYear?->label }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Classes --}}
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden self-start">
            <div class="px-5 py-4 border-b border-outline-variant/50">
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Classes</h2>
            </div>
            <ul class="divide-y divide-outline-variant/40">
                @foreach ($classes as $row)
                    <li>
                        <a href="{{ route('admin.report-cards.index') }}?term_id={{ $term?->term_id }}&class_id={{ $row['class']->class_id }}"
                           @class([
                               'flex items-center gap-3 px-5 py-3 transition-colors',
                               'bg-secondary-fixed' => $selectedClass?->class_id === $row['class']->class_id,
                               'hover:bg-surface-container' => $selectedClass?->class_id !== $row['class']->class_id,
                           ])>
                            <div class="min-w-0 flex-1">
                                <p class="font-title-sm text-title-sm text-on-surface">{{ $row['class']->class_name }}</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                    {{ $row['class']->students_count }} students ·
                                    {{ $row['class']->teacher?->full_name ?? 'No class teacher' }}
                                </p>
                            </div>
                            @if ($row['is_finalized'])
                                <span class="px-2 py-0.5 rounded-lg bg-green-50 text-green-700 font-label-sm text-label-sm font-semibold shrink-0">
                                    Finalized
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Students --}}
        <div class="lg:col-span-2">
            @if (! $selectedClass)
                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-16 text-center">
                    <span class="material-symbols-outlined text-[40px] text-outline">description</span>
                    <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">Choose a class</p>
                    <p class="mt-1 font-body-md text-body-md text-on-surface-variant">
                        Pick a class on the left to see its students and print their report cards.
                    </p>
                </div>
            @else
                @php $anyFinalized = $rows->contains(fn ($r) => $r['card']?->isFinalized()); @endphp

                <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
                    <div class="px-5 py-4 border-b border-outline-variant/50 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">
                                {{ $selectedClass->class_name }}
                            </h2>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                {{ $term?->name }} · {{ $rows->count() }} student{{ $rows->count() === 1 ? '' : 's' }}
                            </p>
                        </div>

                        @if ($anyFinalized)
                            <details class="relative">
                                <summary class="cursor-pointer list-none inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-error/40 text-error hover:bg-error-container font-label-md text-label-md font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">lock_open</span> Unfinalize
                                </summary>
                                <form method="POST" action="{{ route('admin.report-cards.unfinalize', $selectedClass->class_id) }}"
                                      class="mt-3 p-4 rounded-lg border border-outline-variant/60 bg-surface-container w-full sm:w-96">
                                    @csrf
                                    <input type="hidden" name="term_id" value="{{ $term?->term_id }}">
                                    <p class="font-body-sm text-body-sm text-on-surface-variant mb-2">
                                        Clears class positions and unlocks marks. Teachers' comments are kept.
                                        The reason is written to the audit log.
                                    </p>
                                    <label for="reason" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1">Reason</label>
                                    <input type="text" name="reason" id="reason" required minlength="5" maxlength="500"
                                        placeholder="e.g. Maths exam mark entered incorrectly for 3 pupils"
                                        class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                                    <button type="submit"
                                        class="mt-3 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-error text-white hover:bg-error/90 font-title-sm text-title-sm transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">lock_open</span> Confirm unfinalize
                                    </button>
                                </form>
                            </details>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full font-body-md text-body-md">
                            <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-4 py-3">Student</th>
                                    <th class="text-right font-medium px-4 py-3">Average</th>
                                    <th class="text-right font-medium px-4 py-3">Position</th>
                                    <th class="text-center font-medium px-4 py-3">Status</th>
                                    <th class="text-right font-medium px-4 py-3">Report card</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @forelse ($rows as $row)
                                    <tr class="hover:bg-surface-container-low/60 transition-colors">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-on-surface">{{ $row['student']->full_name }}</p>
                                            <p class="font-data-mono text-code-md text-on-surface-variant">{{ $row['student']->student_number }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right font-data-mono text-data-mono">
                                            {{ $row['card']?->term_average !== null ? $row['card']->term_average . '%' : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-data-mono text-data-mono">
                                            {{ $row['card']?->rank_label ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span @class([
                                                'inline-flex px-2.5 py-1 rounded-lg font-label-sm text-label-sm font-semibold',
                                                'bg-green-50 text-green-700' => $row['card']?->isFinalized(),
                                                'bg-tertiary-fixed text-on-surface-variant' => ! $row['card']?->isFinalized(),
                                            ])>{{ $row['card']?->isFinalized() ? 'Finalized' : 'Draft' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('admin.report-cards.show', $row['student']->student_id) }}?term_id={{ $term?->term_id }}"
                                                   target="_blank" rel="noopener" title="Preview"
                                                   class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors">
                                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                                </a>
                                                <a href="{{ route('admin.report-cards.show', $row['student']->student_id) }}?term_id={{ $term?->term_id }}&download=1"
                                                   title="Download PDF"
                                                   class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors">
                                                    <span class="material-symbols-outlined text-[20px]">download</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">
                                            This class has no students.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </main>
@endsection
