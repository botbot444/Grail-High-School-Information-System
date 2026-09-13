@extends('layouts.parent')

@section('title', 'Timetable – Parent Portal')

@section('page')
    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $slotMap = $slots->keyBy(fn ($slot) => $slot->day_of_week . '-' . $slot->period_id);
        $today = now()->format('l');
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    {{ $student->full_name }}'s Timetable
                </h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    {{ $student->schoolClass?->display_name ?? $student->schoolClass?->class_name ?? 'No class assigned' }}
                    @if ($term)
                        · {{ $term->name }}@if ($term->academicYear) · {{ $term->academicYear->label }}@endif
                    @endif
                </p>
            </div>

            <form method="GET" class="self-start">
                <label for="term_id" class="sr-only">Term</label>
                <select name="term_id" id="term_id" onchange="this.form.submit()"
                    class="text-sm rounded-lg border-outline-variant bg-white text-on-surface focus:border-primary focus:ring-primary/30">
                    @foreach ($terms as $option)
                        <option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>
                            {{ $option->name }} ({{ $option->academicYear?->label }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if (! $student->schoolClass)
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm px-5 py-12 text-center">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant">school</span>
                <p class="font-headline-sm text-headline-sm font-bold text-on-surface mt-3">No class assigned</p>
                <p class="text-sm text-on-surface-variant mt-1 max-w-md mx-auto">
                    {{ $student->first_name }} is not currently enrolled in a class, so there is no timetable
                    to show. Contact the school office.
                </p>
            </div>
        @elseif ($periods->isEmpty())
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm px-5 py-12 text-center">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant">schedule</span>
                <p class="font-headline-sm text-headline-sm font-bold text-on-surface mt-3">No periods defined</p>
                <p class="text-sm text-on-surface-variant mt-1 max-w-md mx-auto">
                    This grade level does not have a daily period structure set up yet.
                </p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-outline-variant flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Weekly Schedule</h2>
                        <p class="text-xs text-on-surface-variant mt-0.5">Read-only · set by the school office</p>
                    </div>
                    <span class="text-xs font-medium text-on-surface-variant hidden sm:block">
                        {{ $slots->count() }} lesson{{ $slots->count() === 1 ? '' : 's' }} scheduled
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm min-w-[760px]">
                        <thead>
                            <tr class="bg-surface-container">
                                <th class="sticky left-0 bg-surface-container text-left text-xs font-semibold uppercase tracking-wide text-on-surface-variant px-4 py-3 border-b border-outline-variant w-28">
                                    Day
                                </th>
                                @foreach ($periods as $period)
                                    <th class="px-3 py-3 border-b border-l border-outline-variant text-center min-w-[130px]">
                                        <p class="text-xs font-bold text-on-surface">{{ $period->name }}</p>
                                        <p class="text-[10px] font-mono text-on-surface-variant mt-0.5">
                                            {{ $period->start_time?->format('H:i') }}–{{ $period->end_time?->format('H:i') }}
                                        </p>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($days as $day)
                                <tr class="{{ $day === $today ? 'bg-primary/5' : '' }}">
                                    <th class="sticky left-0 {{ $day === $today ? 'bg-[#e8eefb]' : 'bg-white' }} text-left px-4 py-3 border-b border-outline-variant align-top">
                                        <span class="font-bold text-on-surface">{{ $day }}</span>
                                        @if ($day === $today)
                                            <span class="block text-[10px] font-bold uppercase tracking-wide text-primary mt-0.5">Today</span>
                                        @endif
                                    </th>

                                    @foreach ($periods as $period)
                                        @php $slot = $slotMap->get($day . '-' . $period->id); @endphp
                                        <td class="px-2 py-2 border-b border-l border-outline-variant align-top">
                                            @if ($period->is_break)
                                                <div class="rounded-lg bg-surface-container px-2 py-2 text-center">
                                                    <p class="text-xs font-medium text-on-surface-variant">{{ $period->name }}</p>
                                                </div>
                                            @elseif ($slot)
                                                <div class="rounded-lg bg-[#e8eefb] border border-primary/20 px-2.5 py-2">
                                                    <p class="font-semibold text-on-surface leading-snug text-sm">
                                                        {{ $slot->subject?->subject_name ?? 'Unassigned' }}
                                                    </p>
                                                    <p class="text-xs text-on-surface-variant truncate mt-0.5">
                                                        {{ $slot->teacher?->full_name ?? 'Teacher TBC' }}
                                                    </p>
                                                </div>
                                            @else
                                                <div class="rounded-lg border border-dashed border-outline-variant px-2 py-2 text-center">
                                                    <span class="text-xs text-on-surface-variant">Free</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
