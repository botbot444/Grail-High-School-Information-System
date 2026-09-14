{{-- resources/views/admin/classes/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Class')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 mb-4 font-label-md text-label-md text-on-surface-variant">
            <a href="{{ route('admin.classes.index') }}" class="hover:text-primary transition-colors">Classes</a>
            <span class="text-outline-variant">/</span>
            <span class="text-on-surface font-semibold">{{ $class->class_name }}</span>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Edit</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center gap-3">
                    <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Edit Class — {{ $class->class_name }}</h1>
                    @if ($class->teacher_id)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm font-semibold">
                            <span class="w-2 h-2 rounded-full bg-tertiary-container"></span>
                            ACTIVE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-semibold">
                            <span class="w-2 h-2 rounded-full bg-error"></span>
                            NEEDS TEACHER
                        </span>
                    @endif
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Update section details, reassign the homeroom teacher, or change enrolled subjects.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start md:self-auto">
                <a href="{{ route('admin.students.index', ['class_id' => $class->class_id]) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container-low transition-colors font-label-md text-label-md shadow-sm">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant">group</span>
                    <span>View Roster</span>
                </a>
                <a href="{{ route('admin.classes.show', $class) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm">
                    <span>Cancel</span>
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.classes.update', $class) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
                <div class="lg:col-span-7 flex flex-col gap-6">
                    <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-6">
                        <div class="flex items-center gap-3 pb-4 border-b border-outline-variant">
                            <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                                <span class="material-symbols-outlined text-[22px]">edit_note</span>
                            </div>
                            <div>
                                <h2 class="font-title-md text-title-md text-on-surface font-semibold">Class Details</h2>
                                <span class="font-body-sm text-body-sm text-on-surface-variant">Section name and grade level</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="class_name">
                                    Class Name <span class="text-error">*</span>
                                </label>
                                <input type="text" id="class_name" name="class_name" value="{{ old('class_name', $class->class_name) }}" required
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('class_name') ring-2 ring-error @enderror">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="grade_level_id">
                                    Grade Level <span class="text-error">*</span>
                                </label>
                                @if ($gradeLevels->isEmpty())
                                    <div class="px-3.5 py-2.5 rounded-lg bg-error-container/40 text-on-error-container font-body-sm text-body-sm">
                                        No grade levels set up yet.
                                        <a href="{{ route('admin.grade-levels.create') }}" class="underline font-semibold">Add one first</a>.
                                    </div>
                                @else
                                    <select id="grade_level_id" name="grade_level_id" required
                                        class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md appearance-none focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm cursor-pointer @error('grade_level_id') ring-2 ring-error @enderror">
                                        <option value="">Select a grade level</option>
                                        @foreach ($gradeLevels as $gradeLevel)
                                            <option value="{{ $gradeLevel->grade_level_id }}"
                                                @selected(old('grade_level_id', $class->grade_level_id) == $gradeLevel->grade_level_id)>{{ $gradeLevel->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 pt-2">
                            <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="teacher_id">
                                Homeroom Teacher
                            </label>
                            @if ($class->teacher)
                                <div class="flex items-center gap-3.5 p-4 rounded-xl bg-surface-container-low mb-1">
                                    <div class="w-11 h-11 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed-variant font-bold shrink-0">
                                        {{ strtoupper(substr($class->teacher->first_name, 0, 1) . substr($class->teacher->last_name, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <h3 class="font-title-md text-title-md text-on-surface font-semibold truncate">{{ $class->teacher->full_name }}</h3>
                                        <span class="font-body-sm text-body-sm text-on-surface-variant truncate">{{ $class->teacher->email }}</span>
                                    </div>
                                </div>
                            @endif
                            <select id="teacher_id" name="teacher_id"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md appearance-none focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm cursor-pointer">
                                <option value="">Unassigned</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->teacher_id }}"
                                        @selected(old('teacher_id', $class->teacher_id) == $teacher->teacher_id)>
                                        {{ $teacher->full_name }} ({{ $teacher->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">Choose a different teacher to reassign, or "Unassigned" to clear it.</p>
                        </div>
                    </section>

                    <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-5">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">analytics</span>
                            <h3 class="font-title-md text-title-md text-on-surface font-semibold">Cohort Overview</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-surface-container-low flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold">Enrolled Students</span>
                                    <span class="font-headline-lg text-headline-lg font-bold text-on-surface mt-1">{{ $class->students_count }}</span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                                    <span class="material-symbols-outlined text-[20px]">groups</span>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-surface-container-low flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold">Subjects Assigned</span>
                                    <span class="font-headline-lg text-headline-lg font-bold text-on-surface mt-1" id="assignedSubjectStat">{{ count($assignedSubjects) }}</span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shadow-sm">
                                    <span class="material-symbols-outlined text-[20px]">menu_book</span>
                                </div>
                            </div>
                        </div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">
                            Last updated {{ $class->updated_at->diffForHumans() }}.
                        </p>
                    </section>
                </div>

                <div class="lg:col-span-5 flex flex-col gap-6">
                    <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-5">
                        <div class="flex items-center justify-between pb-3 border-b border-outline-variant">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                                    <span class="material-symbols-outlined text-[22px]">auto_stories</span>
                                </div>
                                <div>
                                    <h2 class="font-title-md text-title-md text-on-surface font-semibold">Subjects Offered</h2>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant">Assigned curriculum</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-primary-fixed text-on-primary-fixed font-label-sm text-label-sm font-semibold" id="selectedCountBadge">
                                {{ count($assignedSubjects) }} Selected
                            </span>
                        </div>

                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-on-surface-variant text-[18px]">search</span>
                            <input type="text" id="subjectSearch" placeholder="Filter subjects by name..."
                                class="w-full pl-9 pr-4 py-2 rounded-lg bg-surface text-on-surface placeholder:text-on-surface-variant font-body-sm text-body-sm focus:outline-none focus:bg-surface-container-lowest shadow-sm">
                        </div>

                        <div class="flex flex-col gap-2.5 max-h-[420px] overflow-y-auto pr-1" id="subjectRoster">
                            @php $selectedSubjects = old('subject_ids', $assignedSubjects); @endphp
                            @forelse ($subjects as $subject)
                                <label class="subject-item flex items-center gap-3 p-3 rounded-xl {{ in_array($subject->subject_id, $selectedSubjects) ? 'bg-surface-container-low' : 'bg-surface' }} cursor-pointer transition-all hover:bg-surface-container">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->subject_id }}"
                                        class="subject-checkbox w-4 h-4 rounded accent-primary cursor-pointer"
                                        @checked(in_array($subject->subject_id, $selectedSubjects))>
                                    <span class="font-title-md text-title-md text-on-surface font-semibold">{{ $subject->subject_name }}</span>
                                </label>
                            @empty
                                <p class="text-body-sm font-body-sm text-on-surface-variant p-3">No subjects exist yet. <a href="{{ route('admin.subjects.create') }}" class="text-primary underline">Create one</a>.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8">
                <form method="POST" action="{{ route('admin.classes.destroy', $class) }}"
                    onsubmit="return confirm('Delete this class? Students enrolled in it will need to be reassigned.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2.5 rounded-lg text-error hover:bg-error-container/30 transition-colors font-label-md text-label-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        <span>Delete Class</span>
                    </button>
                </form>
                <a href="{{ route('admin.classes.show', $class) }}"
                    class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all shadow-md">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </main>

    @push('scripts')
        <script>
            (function () {
                const checkboxes = document.querySelectorAll('.subject-checkbox');
                const badge = document.getElementById('selectedCountBadge');
                const stat = document.getElementById('assignedSubjectStat');
                const searchInput = document.getElementById('subjectSearch');
                const items = document.querySelectorAll('.subject-item');

                function updateCounter() {
                    const selected = document.querySelectorAll('.subject-checkbox:checked').length;
                    if (badge) badge.textContent = `${selected} Selected`;
                    if (stat) stat.textContent = selected;
                }

                checkboxes.forEach((box) => {
                    box.addEventListener('change', () => {
                        const item = box.closest('.subject-item');
                        item.classList.toggle('bg-surface-container-low', box.checked);
                        item.classList.toggle('bg-surface', !box.checked);
                        updateCounter();
                    });
                });

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        const query = e.target.value.toLowerCase();
                        items.forEach((item) => {
                            item.style.display = item.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
                        });
                    });
                }

                updateCounter();
            })();
        </script>
    @endpush
@endsection
