@extends('layouts.app')

@section('title', 'Student Promotion')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Dashboard</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Promotion</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Year-End Promotion</h1>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                    Move a cohort up at the end of the year. Each class is promoted separately so you can
                    review the students before committing.
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-2 self-start">
                <form method="GET" class="flex items-center gap-2">
                    <label for="academic_year_id" class="font-label-md text-label-md text-on-surface-variant">Into year</label>
                    <select name="academic_year_id" id="academic_year_id" onchange="this.form.submit()"
                        class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                        @forelse ($years as $year)
                            <option value="{{ $year->year_id }}" @selected($targetYear?->year_id === $year->year_id)>{{ $year->label }}</option>
                        @empty
                            <option value="">No years created</option>
                        @endforelse
                    </select>
                </form>
                <a href="{{ route('admin.promotions.mappings') }}"
                    class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container hover:text-primary font-title-sm text-title-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">alt_route</span>
                    <span>Mappings</span>
                </a>
            </div>
        </div>

        @if (session('notification'))
            <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
                {{ session('notification') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3">
                <ul class="list-disc ml-5 font-body-sm text-body-sm text-on-error-container space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @if (! empty($blockers))
            <div class="mb-6 rounded-xl border border-error/30 bg-error-container px-5 py-4">
                <p class="font-title-sm text-title-sm text-on-error-container flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">block</span>
                    Promotion cannot run yet
                </p>
                <ul class="mt-2 ml-7 list-disc font-body-sm text-body-sm text-on-error-container space-y-1">
                    @foreach ($blockers as $blocker)<li>{{ $blocker }}</li>@endforeach
                </ul>
                <a href="{{ route('admin.academic-years.index') }}"
                    class="mt-3 inline-flex items-center gap-1.5 font-label-md text-label-md text-on-error-container underline">
                    <span class="material-symbols-outlined text-[16px]">calendar_month</span> Manage academic years
                </a>
            </div>
        @endif

        {{-- Classes --}}
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden mb-8">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Classes</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    The default outcome comes from your saved mappings, or from the next grade level up when none is set.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full font-body-md text-body-md">
                    <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">Class</th>
                            <th class="text-left font-medium px-4 py-3">Grade level</th>
                            <th class="text-right font-medium px-4 py-3">Enrolled</th>
                            <th class="text-left font-medium px-4 py-3">Default outcome</th>
                            <th class="text-right font-medium px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @forelse ($classes as $row)
                            @php
                                $target = $row['default']['to_class_id']
                                    ? $allClasses->firstWhere('class_id', $row['default']['to_class_id'])
                                    : null;
                            @endphp
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-4 py-3 font-semibold text-on-surface">{{ $row['class']->class_name }}</td>
                                <td class="px-4 py-3 text-on-surface-variant">{{ $row['class']->gradeLevel?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-data-mono text-data-mono">{{ $row['class']->students_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($row['default']['outcome'] === 'graduated')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-secondary-fixed text-on-secondary-container font-label-sm text-label-sm font-semibold">
                                            <span class="material-symbols-outlined text-[14px]">school</span> Graduates
                                        </span>
                                    @elseif ($target)
                                        <span class="inline-flex items-center gap-1.5 font-body-sm text-body-sm text-on-surface">
                                            <span class="material-symbols-outlined text-[16px] text-on-surface-variant">arrow_forward</span>
                                            {{ $target->class_name }}
                                        </span>
                                    @else
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">Retain — no destination set</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if (empty($blockers) && $row['class']->students_count > 0)
                                        <a href="{{ route('admin.promotions.show', $row['class']->class_id) }}?academic_year_id={{ $targetYear?->year_id }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm transition-all">
                                            <span class="material-symbols-outlined text-[18px]">arrow_upward</span> Promote
                                        </a>
                                    @else
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">
                                            {{ $row['class']->students_count === 0 ? 'No students' : 'Blocked' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center font-body-md text-body-md text-on-surface-variant">No classes exist yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- History --}}
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Promotion History</h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    Every run is recorded with the class each student came from, so a mistake can be undone.
                </p>
            </div>

            @if ($batches->isEmpty())
                <p class="px-5 py-10 text-center font-body-md text-body-md text-on-surface-variant">
                    No promotions have been run yet.
                </p>
            @else
                <ul class="divide-y divide-outline-variant/40">
                    @foreach ($batches as $batch)
                        <li class="px-5 py-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-title-sm text-title-sm text-on-surface">
                                    {{ $batch['total'] }} student{{ $batch['total'] === 1 ? '' : 's' }}
                                    into {{ $batch['year']?->label ?? 'an unnamed year' }}
                                    @if ($batch['rolled_back'])
                                        <span class="ml-1.5 inline-flex px-2 py-0.5 rounded-lg bg-surface-container text-on-surface-variant font-label-sm text-label-sm">Rolled back</span>
                                    @endif
                                </p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                    {{ $batch['promoted'] }} promoted · {{ $batch['retained'] }} retained ·
                                    {{ $batch['graduated'] }} graduated ·
                                    {{ $batch['ran_at']?->format('j M Y, H:i') }}
                                    @if ($batch['by']) · by {{ $batch['by']->name }} @endif
                                </p>
                                <p class="font-data-mono text-code-sm text-outline mt-0.5">{{ $batch['batch_ref'] }}</p>
                            </div>

                            @unless ($batch['rolled_back'])
                                <form method="POST" action="{{ route('admin.promotions.rollback', $batch['batch_ref']) }}"
                                      onsubmit="return confirm('Roll back this batch? Every student returns to the class they came from.');">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-error/40 text-error hover:bg-error-container font-label-md text-label-md font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">undo</span> Roll back
                                    </button>
                                </form>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
