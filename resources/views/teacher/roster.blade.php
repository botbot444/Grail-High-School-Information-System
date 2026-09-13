@extends('layouts.teacher')

@section('title', $schoolClass->display_name.' Roster')

@section('page')
    <div class="flex flex-col gap-space-lg">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-space-md">
            <div>
                <p class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">Teacher Portal / My Classes / Roster</p>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ $schoolClass->display_name }} Roster</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">{{ $rows->count() }} enrolled students · {{ $term?->name ?? 'No term selected' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" action="{{ route('teacher.classes.roster', $schoolClass->class_id) }}"><select name="term_id" onchange="this.form.submit()" class="h-10 rounded-lg border-0 bg-surface-container-low px-3 text-label-md">@foreach ($terms as $option)<option value="{{ $option->term_id }}" @selected($term?->term_id === $option->term_id)>{{ $option->name }} ({{ $option->academicYear?->label }})</option>@endforeach</select></form>
                <button type="button" onclick="window.print()" class="h-10 rounded-lg bg-surface-container-low px-3 font-label-md text-label-md"><span class="material-symbols-outlined align-middle text-[18px]">print</span> Print</button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-space-md">
            <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span class="font-label-sm text-label-sm uppercase text-outline">Enrolled</span><strong class="mt-2 block font-headline-md text-headline-md">{{ $rows->count() }}</strong></div>
            <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span class="font-label-sm text-label-sm uppercase text-outline">Class Average</span><strong class="mt-2 block font-headline-md text-headline-md">{{ $rows->whereNotNull('average')->isNotEmpty() ? number_format($rows->whereNotNull('average')->avg('average'), 1).'%' : '—' }}</strong></div>
            <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span class="font-label-sm text-label-sm uppercase text-outline">Attendance</span><strong class="mt-2 block font-headline-md text-headline-md">{{ $rows->whereNotNull('attendance')->isNotEmpty() ? number_format($rows->whereNotNull('attendance')->avg('attendance'), 1).'%' : '—' }}</strong></div>
            <div class="rounded-xl bg-surface-container-lowest p-space-md shadow-sm"><span class="font-label-sm text-label-sm uppercase text-outline">Needs Review</span><strong class="mt-2 block font-headline-md text-headline-md">{{ $rows->filter(fn ($row) => $row['average'] !== null && $row['average'] < 50)->count() }}</strong></div>
        </div>

        <section class="rounded-xl bg-surface-container-lowest shadow-sm">
            <div class="flex flex-col gap-3 border-b border-outline-variant p-space-lg lg:flex-row lg:items-center lg:justify-between"><div><h2 class="font-title-md text-title-md">Student Roster</h2><p class="font-body-sm text-body-sm text-on-surface-variant">Search and review students assigned to this class.</p></div><input id="rosterSearch" type="search" placeholder="Search by name or admission number" class="h-10 rounded-lg border-0 bg-surface-container-low px-3 text-label-md"></div>
            <div class="overflow-x-auto"><table class="w-full text-left"><thead><tr class="border-b border-outline-variant font-label-sm text-label-sm uppercase text-outline"><th class="px-4 py-3">#</th><th class="px-4 py-3">Student</th><th class="px-4 py-3">Admission No.</th><th class="px-4 py-3">Gender</th><th class="px-4 py-3">Average</th><th class="px-4 py-3">Attendance</th><th class="px-4 py-3">Status</th></tr></thead><tbody>
                @forelse ($rows as $index => $row)<tr class="roster-row border-b border-outline-variant last:border-0" data-search="{{ strtolower($row['student']->full_name.' '.$row['student']->student_number) }}"><td class="px-4 py-3 text-outline">{{ $index + 1 }}</td><td class="px-4 py-3 font-title-sm text-title-sm">{{ $row['student']->full_name }}</td><td class="px-4 py-3 font-data-sm text-data-sm">{{ $row['student']->student_number }}</td><td class="px-4 py-3">{{ $row['student']->gender }}</td><td class="px-4 py-3 font-semibold">{{ $row['average'] !== null ? number_format($row['average'], 1).'%' : '—' }}</td><td class="px-4 py-3">{{ $row['attendance'] !== null ? $row['attendance'].'%' : '—' }}</td><td class="px-4 py-3"><span class="rounded-full bg-secondary-fixed px-2 py-1 font-label-sm text-label-sm text-on-secondary-fixed">{{ $row['status'] }}</span></td></tr>@empty<tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant">No students are enrolled in this class.</td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
    <script>document.getElementById('rosterSearch')?.addEventListener('input', event => document.querySelectorAll('.roster-row').forEach(row => row.classList.toggle('hidden', !row.dataset.search.includes(event.target.value.toLowerCase()))));</script>
@endsection