@extends('layouts.app')

@section('title', 'Teacher · ' . $teacher->full_name)

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">

            {{-- Breadcrumb + actions --}}
            <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
                <nav class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm">
                    <a class="hover:text-primary transition-colors" href="{{ route('admin.teachers.index') }}">Teachers</a>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-on-surface font-semibold">{{ $teacher->full_name }}</span>
                </nav>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.teachers.edit', $teacher) }}"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        Edit details
                    </a>
                    <a href="{{ route('admin.teachers.index') }}"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                </div>
            </div>

            @include('admin.partials.flash')

            @if (session('assignment_summary'))
                <div class="mb-6 rounded-xl border border-outline-variant bg-surface-container-low px-4 py-3 flex items-start gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant text-[20px] shrink-0 mt-0.5">info</span>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">{{ session('assignment_summary') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-12 gap-gutter items-start">

                {{-- ── Left column: who they are, and their login ───────────── --}}
                <div class="col-span-12 lg:col-span-4 flex flex-col gap-gutter">

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm p-5">
                        <div class="flex items-center gap-4 mb-5">
                            <div
                                class="w-14 h-14 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-title-lg text-title-lg shrink-0">
                                {{ strtoupper(substr($teacher->first_name, 0, 1) . substr($teacher->last_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h1 class="font-title-lg text-title-lg text-on-surface truncate">{{ $teacher->full_name }}</h1>
                                <p class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $teacher->email }}</p>
                            </div>
                        </div>

                        <dl class="flex flex-col gap-3 font-body-md text-body-md">
                            <div class="flex justify-between gap-4">
                                <dt class="text-on-surface-variant">Phone</dt>
                                <dd class="text-on-surface text-right">{{ $teacher->phone ?: '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-on-surface-variant">Teacher ID</dt>
                                <dd class="font-data-mono text-data-mono text-on-surface">#{{ $teacher->teacher_id }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-on-surface-variant">Added</dt>
                                <dd class="text-on-surface text-right">{{ $teacher->created_at?->format('j M Y') ?? '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm p-5">
                        <h2 class="font-title-sm text-title-sm text-on-surface mb-1">Login account</h2>

                        @if ($teacher->user)
                            <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">
                                Signs in as <span class="font-data-mono text-data-mono text-on-surface">{{ $teacher->user->email }}</span>
                            </p>

                            <div class="flex flex-wrap gap-2 mb-5">
                                @if ($teacher->user->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary-container text-on-primary-container font-label-sm text-label-sm">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm">
                                        <span class="material-symbols-outlined text-[14px]">block</span> Deactivated
                                    </span>
                                @endif

                                @if ($teacher->user->must_change_password)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-secondary-fixed text-on-surface font-label-sm text-label-sm">
                                        <span class="material-symbols-outlined text-[14px]">key</span> Must set own password
                                    </span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.users.reset-password', $teacher->user) }}"
                                onsubmit="return confirm('Generate a new one-time password for {{ $teacher->full_name }}? Their current password stops working immediately.');">
                                @csrf
                                @method('PUT')
                                <button type="submit"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                    <span class="material-symbols-outlined text-[18px]">key</span>
                                    Generate new password
                                </button>
                            </form>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2.5">
                                Shown on screen once, for you to hand over. Deactivate or change the role from
                                <a class="text-primary hover:underline" href="{{ route('admin.users.index') }}">Account management</a>.
                            </p>
                        @else
                            <div class="rounded-lg border border-error/30 bg-error-container px-3 py-2.5">
                                <p class="font-body-sm text-body-sm text-on-error-container">
                                    This teacher record has no login account attached, so they cannot sign in at all.
                                </p>
                            </div>
                        @endif
                    </section>

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm p-5">
                        <h2 class="font-title-sm text-title-sm text-on-surface mb-3">Homeroom</h2>
                        @forelse ($teacher->homeroomClasses as $class)
                            <div class="flex items-center gap-2 py-1.5 font-body-md text-body-md text-on-surface">
                                <span class="material-symbols-outlined text-[18px] text-on-surface-variant">groups</span>
                                {{ $class->display_name }}
                            </div>
                        @empty
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Not a form teacher for any class.</p>
                        @endforelse
                    </section>
                </div>

                {{-- ── Right column: what they actually teach ────────────────── --}}
                <div class="col-span-12 lg:col-span-8 flex flex-col gap-gutter">

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-outline-variant">
                            <h2 class="font-title-sm text-title-sm text-on-surface">Teaching assignments</h2>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                What this teacher sees when they sign in. Marks, attendance, assignments and report cards
                                all hang off these rows.
                            </p>
                        </div>

                        @if ($teacher->classSubjects->isEmpty())
                            <div class="px-5 py-8 text-center">
                                <span class="material-symbols-outlined text-[40px] text-outline">school</span>
                                <p class="font-title-sm text-title-sm text-on-surface mt-2">No teaching assignments</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 max-w-md mx-auto">
                                    This teacher signs in to an empty portal — no classes to mark, no attendance to take,
                                    nothing to grade. Assign at least one subject below.
                                </p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full font-body-md text-body-md">
                                    <thead
                                        class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                        <tr>
                                            <th class="text-left px-5 py-3">Class</th>
                                            <th class="text-left px-5 py-3">Subject</th>
                                            <th class="text-left px-5 py-3">Recorded so far</th>
                                            <th class="text-right px-5 py-3">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant">
                                        @foreach ($teacher->classSubjects->sortBy(fn ($cs) => [$cs->schoolClass?->class_name, $cs->subject?->subject_name]) as $classSubject)
                                            @php
                                                $recorded = $classSubject->grades_count
                                                    + $classSubject->attendance_records_count
                                                    + $classSubject->assignments_count;
                                            @endphp
                                            <tr class="hover:bg-surface-container-low/60 transition-colors align-middle">
                                                <td class="px-5 py-3.5 text-on-surface">
                                                    {{ $classSubject->schoolClass?->display_name ?? '—' }}
                                                </td>
                                                <td class="px-5 py-3.5 text-on-surface">
                                                    {{ $classSubject->subject?->subject_name ?? '—' }}
                                                </td>
                                                <td class="px-5 py-3.5 text-on-surface-variant font-body-sm text-body-sm">
                                                    @if ($recorded === 0)
                                                        Nothing yet
                                                    @else
                                                        {{ $classSubject->grades_count }} marks ·
                                                        {{ $classSubject->attendance_records_count }} attendance ·
                                                        {{ $classSubject->assignments_count }} assignments
                                                    @endif
                                                </td>
                                                <td class="px-5 py-3.5 text-right">
                                                    @if ($recorded === 0)
                                                        <form method="POST"
                                                            action="{{ route('admin.teachers.unassign', [$teacher, $classSubject]) }}"
                                                            class="inline"
                                                            onsubmit="return confirm('Remove {{ $classSubject->subject?->subject_name }} in {{ $classSubject->schoolClass?->display_name }} from this teacher?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:text-error hover:border-error font-label-sm text-label-sm transition-colors">
                                                                <span class="material-symbols-outlined text-[16px]">close</span>
                                                                Remove
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="font-label-sm text-label-sm text-on-surface-variant"
                                                            title="Marks and attendance point at this assignment, so removing it would strand them.">
                                                            Has records
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Assign a subject --}}
                        <div class="px-5 py-4 border-t border-outline-variant bg-surface-container-low/40">
                            <h3 class="font-label-md text-label-md text-on-surface mb-3">Assign a subject</h3>
                            <form method="POST" action="{{ route('admin.teachers.assign', $teacher) }}"
                                class="flex flex-wrap items-end gap-3">
                                @csrf
                                <div class="flex-1 min-w-[180px]">
                                    <label for="class_id"
                                        class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Class</label>
                                    <select name="class_id" id="class_id" required
                                        class="w-full h-11 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 font-body-md text-body-md text-on-surface">
                                        <option value="">Choose a class…</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->class_id }}"
                                                @selected(old('class_id') == $class->class_id)>{{ $class->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex-1 min-w-[180px]">
                                    <label for="subject_id"
                                        class="block font-label-sm text-label-sm text-on-surface-variant mb-1">Subject</label>
                                    <select name="subject_id" id="subject_id" required
                                        class="w-full h-11 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 font-body-md text-body-md text-on-surface">
                                        <option value="">Choose a subject…</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->subject_id }}"
                                                @selected(old('subject_id') == $subject->subject_id)>{{ $subject->subject_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="submit"
                                    class="h-11 flex items-center gap-2 px-5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity">
                                    <span class="material-symbols-outlined text-[18px]">add</span>
                                    Assign
                                </button>

                                <label class="w-full flex items-start gap-2 mt-1 cursor-pointer">
                                    <input type="checkbox" name="confirm_handover" value="1" class="mt-0.5">
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">
                                        Hand over if another teacher already teaches it — any marks already recorded move
                                        with the subject.
                                    </span>
                                </label>
                            </form>

                            <p id="pair-warning" class="font-body-sm text-body-sm text-error mt-2 hidden"></p>
                        </div>
                    </section>

                    <section class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-2">
                            <h2 class="font-title-sm text-title-sm text-on-surface">Timetable</h2>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">
                                {{ $term?->name ?? 'No current term' }}
                            </span>
                        </div>

                        @if ($timetable->isEmpty())
                            <p class="px-5 py-6 font-body-sm text-body-sm text-on-surface-variant text-center">
                                @if ($term)
                                    No periods timetabled for this teacher in {{ $term->name }}.
                                @else
                                    Set a current term to see a timetable.
                                @endif
                            </p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full font-body-md text-body-md">
                                    <thead
                                        class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                        <tr>
                                            <th class="text-left px-5 py-3">Day</th>
                                            <th class="text-left px-5 py-3">Period</th>
                                            <th class="text-left px-5 py-3">Class</th>
                                            <th class="text-left px-5 py-3">Subject</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant">
                                        @foreach ($timetable as $slot)
                                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                                <td class="px-5 py-3 text-on-surface">{{ $slot->day_of_week }}</td>
                                                <td class="px-5 py-3 text-on-surface-variant">
                                                    {{ $slot->period?->name ?? '—' }}
                                                    @if ($slot->period?->start_time)
                                                        <span class="font-data-mono text-data-mono">({{ $slot->period->start_time }})</span>
                                                    @endif
                                                </td>
                                                <td class="px-5 py-3 text-on-surface">{{ $slot->schoolClass?->display_name ?? '—' }}</td>
                                                <td class="px-5 py-3 text-on-surface">{{ $slot->subject?->subject_name ?? 'Break' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
        <script>
            // Who already teaches each class/subject pair, so the admin is warned
            // before submitting rather than after. Server-side check still decides.
            (function () {
                const taken = @json($takenPairs->map(fn ($cs) => $cs->teacher?->full_name ?? 'another teacher'));
                const classSelect = document.getElementById('class_id');
                const subjectSelect = document.getElementById('subject_id');
                const warning = document.getElementById('pair-warning');
                const minePairs = @json($teacher->classSubjects->map(fn ($cs) => $cs->class_id . '-' . $cs->subject_id)->values());

                if (!classSelect || !subjectSelect || !warning) return;

                function check() {
                    const key = classSelect.value + '-' + subjectSelect.value;
                    warning.classList.add('hidden');

                    if (!classSelect.value || !subjectSelect.value) return;

                    if (minePairs.includes(key)) {
                        warning.textContent = 'This teacher already has that subject for that class.';
                        warning.classList.remove('hidden');
                        return;
                    }

                    if (taken[key]) {
                        warning.textContent = 'Currently taught by ' + taken[key] + '. Tick "hand over" to move it.';
                        warning.classList.remove('hidden');
                    }
                }

                classSelect.addEventListener('change', check);
                subjectSelect.addEventListener('change', check);
            })();
        </script>
    @endpush
@endsection
