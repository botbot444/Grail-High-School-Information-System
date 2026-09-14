{{-- resources/views/admin/classes/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Class')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-on-surface-variant font-label-sm text-label-sm mb-2">
                    <a href="{{ route('admin.classes.index') }}" class="hover:text-primary transition-colors">Classes</a>
                    <span class="material-symbols-outlined text-[14px] text-outline">chevron_right</span>
                    <span class="text-on-surface font-semibold">Add New</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Create New Class</h1>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl">
                    Set the class name and grade level, assign a homeroom teacher, and choose which subjects it offers.
                </p>
            </div>
            <a href="{{ route('admin.classes.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm self-start">
                <span class="material-symbols-outlined text-[18px]">close</span>
                <span>Cancel</span>
            </a>
        </div>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.classes.store') }}">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <section class="lg:col-span-7 flex flex-col gap-6">
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-8 shadow-sm">
                        <div class="flex items-center gap-3.5 pb-6 mb-6 border-b border-outline-variant">
                            <div class="w-11 h-11 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-sm">
                                <span class="material-symbols-outlined text-[24px]">school</span>
                            </div>
                            <div>
                                <h2 class="font-headline-md text-headline-md text-on-surface">Class Details</h2>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Section name, grade level, and homeroom teacher</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="class_name">
                                    Class Name <span class="text-error">*</span>
                                </label>
                                <input type="text" id="class_name" name="class_name" value="{{ old('class_name') }}" required
                                    placeholder="e.g. 10A"
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all @error('class_name') ring-2 ring-error @enderror">
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Unique section code (e.g. 10A, 10-Science).</p>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="grade_level_id">
                                    Grade Level <span class="text-error">*</span>
                                </label>
                                @if ($gradeLevels->isEmpty())
                                    <div class="px-3.5 py-2.5 rounded-lg bg-error-container/40 text-on-error-container font-body-sm text-body-sm">
                                        No grade levels set up yet.
                                        <a href="{{ route('admin.grade-levels.create') }}" class="underline font-semibold">Add one first</a>.
                                    </div>
                                @else
                                    <select id="grade_level_id" name="grade_level_id" required
                                        class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md appearance-none focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer @error('grade_level_id') ring-2 ring-error @enderror">
                                        <option value="">Select a grade level</option>
                                        @foreach ($gradeLevels as $gradeLevel)
                                            <option value="{{ $gradeLevel->grade_level_id }}" @selected(old('grade_level_id') == $gradeLevel->grade_level_id)>{{ $gradeLevel->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Cohort academic level for transcripts and rosters.</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-1.5">
                            <label class="block font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider" for="teacher_id">
                                Homeroom Teacher
                            </label>
                            <select id="teacher_id" name="teacher_id"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md appearance-none focus:bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                <option value="">Unassigned — decide later</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->teacher_id }}" @selected(old('teacher_id') == $teacher->teacher_id)>
                                        {{ $teacher->full_name }} ({{ $teacher->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Optional — a class can be created before a homeroom teacher is decided.</p>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-5 flex flex-col gap-6">
                    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-outline-variant">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-secondary-fixed flex items-center justify-center text-secondary shadow-sm">
                                    <span class="material-symbols-outlined text-[22px]">menu_book</span>
                                </div>
                                <div>
                                    <h2 class="font-headline-md text-headline-md text-on-surface">Subjects Offered</h2>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant">Select the curriculum for this class</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-fixed text-primary font-label-sm text-label-sm font-bold" id="selectedCounter">
                                0 Selected
                            </span>
                        </div>

                        <div class="relative mb-3">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[18px]">search</span>
                            <input type="text" id="subjectSearch" placeholder="Filter subjects by name..."
                                class="w-full pl-9 pr-4 py-2 rounded-lg bg-surface-container text-on-surface font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest focus:ring-1 focus:ring-primary">
                        </div>

                        <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1" id="subjectList">
                            @forelse ($subjects as $subject)
                                <label class="subject-row flex items-center gap-3 p-3 rounded-xl bg-surface transition-all cursor-pointer hover:bg-surface-container-low">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->subject_id }}"
                                        class="subject-checkbox w-4 h-4 rounded accent-primary cursor-pointer"
                                        @checked(collect(old('subject_ids', []))->contains($subject->subject_id))>
                                    <span class="font-title-md text-title-md text-on-surface font-semibold">{{ $subject->subject_name }}</span>
                                </label>
                            @empty
                                <p class="text-body-sm font-body-sm text-on-surface-variant p-3">No subjects exist yet. <a href="{{ route('admin.subjects.create') }}" class="text-primary underline">Create one</a>.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8">
                <a href="{{ route('admin.classes.index') }}"
                    class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all flex items-center gap-2 shadow-md">
                    <span class="material-symbols-outlined text-[20px]">add_circle</span>
                    <span>Create Class</span>
                </button>
            </div>
        </form>
    </main>

    @push('scripts')
        <script>
            (function () {
                const checkboxes = document.querySelectorAll('.subject-checkbox');
                const counter = document.getElementById('selectedCounter');
                const searchInput = document.getElementById('subjectSearch');
                const rows = document.querySelectorAll('.subject-row');

                function updateCounter() {
                    const selected = document.querySelectorAll('.subject-checkbox:checked').length;
                    if (counter) counter.textContent = `${selected} Selected`;
                }

                checkboxes.forEach((box) => {
                    box.addEventListener('change', () => {
                        const row = box.closest('.subject-row');
                        row.classList.toggle('bg-surface-container-low', box.checked);
                        row.classList.toggle('bg-surface', !box.checked);
                        updateCounter();
                    });
                    if (box.checked) box.closest('.subject-row').classList.replace('bg-surface', 'bg-surface-container-low');
                });

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const query = e.target.value.toLowerCase();
                        rows.forEach((row) => {
                            row.style.display = row.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
                        });
                    });
                }

                updateCounter();
            })();
        </script>
    @endpush
@endsection
