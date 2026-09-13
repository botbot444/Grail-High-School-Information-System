@extends('layouts.student')

@section('title', 'Timetable')
@section('page-title', 'My Timetable')
@section('page-subtitle', $student->schoolClass?->class_name ? 'Class ' . $student->schoolClass->class_name : 'No class assigned')

@section('content')
    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $slotMap = $slots->keyBy(fn ($slot) => $slot->day_of_week . '-' . $slot->period_id);
        $today = now()->format('l');
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-space-md mb-space-lg">
        <p class="text-body-md text-on-surface-variant">
            Read-only view of your class schedule. Ask the school office about changes.
        </p>

        <form method="GET" class="flex items-center gap-2">
            <label for="term_id" class="text-label-md text-on-surface-variant">Term</label>
            <select name="term_id" id="term_id" onchange="this.form.submit()"
                class="rounded-xl border-outline-variant/60 bg-surface-container-lowest text-body-md focus:border-secondary focus:ring-secondary/40">
                @foreach ($terms as $option)
                    <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                        {{ $option->name }} ({{ $option->academicYear?->label }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if (! $student->schoolClass)
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow">
            @include('student.partials.empty-state', [
                'icon'    => 'school',
                'title'   => 'No class assigned',
                'message' => 'You are not currently enrolled in a class, so there is no timetable to show. Contact the school office.',
            ])
        </div>
    @elseif ($periods->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow">
            @include('student.partials.empty-state', [
                'icon'    => 'schedule',
                'title'   => 'No periods defined',
                'message' => 'Your grade level does not have a daily period structure set up yet.',
            ])
        </div>
    @else
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-body-sm min-w-[760px]">
                    <thead>
                        <tr class="bg-surface-container-low">
                            <th class="sticky left-0 bg-surface-container-low text-left text-label-sm uppercase tracking-wider text-on-surface-variant font-medium px-space-md py-3 border-b border-outline-variant/50 w-28">
                                Day
                            </th>
                            @foreach ($periods as $period)
                                <th class="px-3 py-3 border-b border-l border-outline-variant/50 text-center min-w-[130px]">
                                    <p class="text-label-md font-semibold text-on-surface">{{ $period->name }}</p>
                                    <p class="text-code-sm font-data-mono text-on-surface-variant">
                                        {{ $period->start_time?->format('H:i') }}–{{ $period->end_time?->format('H:i') }}
                                    </p>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $day)
                            <tr class="{{ $day === $today ? 'bg-secondary-container/25' : '' }}">
                                <th class="sticky left-0 {{ $day === $today ? 'bg-secondary-container/40' : 'bg-surface-container-lowest' }} text-left px-space-md py-3 border-b border-outline-variant/40 align-top">
                                    <span class="font-semibold text-on-surface">{{ $day }}</span>
                                    @if ($day === $today)
                                        <span class="block text-label-sm text-secondary font-medium">Today</span>
                                    @endif
                                </th>

                                @foreach ($periods as $period)
                                    @php $slot = $slotMap->get($day . '-' . $period->id); @endphp
                                    <td class="px-2 py-2 border-b border-l border-outline-variant/40 align-top">
                                        @if ($period->is_break)
                                            <div class="h-full rounded-lg bg-surface-container px-2 py-2 text-center">
                                                <p class="text-label-sm font-medium text-on-surface-variant">{{ $period->name }}</p>
                                            </div>
                                        @elseif ($slot)
                                            <div class="h-full rounded-lg bg-primary/8 border border-primary/20 px-2.5 py-2">
                                                <p class="font-semibold text-on-surface leading-snug">
                                                    {{ $slot->subject?->subject_name ?? 'Unassigned' }}
                                                </p>
                                                <p class="text-label-sm text-on-surface-variant truncate mt-0.5">
                                                    {{ $slot->teacher?->full_name ?? 'Teacher TBC' }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="h-full rounded-lg border border-dashed border-outline-variant/60 px-2 py-2 text-center">
                                                <span class="text-label-sm text-outline">Free</span>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
