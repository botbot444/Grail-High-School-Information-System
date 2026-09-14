import pathlib, sys

ok = True

def patch(path, old, new, label):
    global ok
    p = pathlib.Path(path); t = p.read_text()
    if old not in t:
        print(f'  ! {label}: anchor not found'); ok = False; return
    p.write_text(t.replace(old, new, 1))
    print(f'  + {label}')


# ── 1. Controller: make the filters real ─────────────────────────────────────
patch('app/Http/Controllers/Admin/AdminController.php',
      "use App\\Models\\Fee;",
      "use App\\Models\\Fee;\nuse App\\Models\\GradeLevel;",
      'AdminController: import GradeLevel')

OLD_INDEX = """        $students = Student::with('schoolClass', 'user')
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->class_id))
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', compact('students'));"""

NEW_INDEX = """        $filters = [
            'search'         => trim((string) $request->input('search', '')),
            'class_id'       => $request->integer('class_id') ?: null,
            'grade_level_id' => $request->integer('grade_level_id') ?: null,
            'status'         => $request->input('status') ?: null,
            'gender'         => $request->input('gender') ?: null,
        ];

        $students = Student::with('schoolClass', 'user')
            ->when($filters['search'], function ($query, $term) {
                // "Mary" matches either name or the admission number; "Mary Banda"
                // is treated as first + last rather than one string, so it works
                // without a driver-specific CONCAT.
                $parts = preg_split('/\\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);

                $query->where(function ($inner) use ($term, $parts) {
                    $like = '%' . $term . '%';

                    $inner->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('student_number', 'like', $like)
                        ->orWhere('guardian_name', 'like', $like);

                    if (count($parts) > 1) {
                        $first = '%' . $parts[0] . '%';
                        $last  = '%' . end($parts) . '%';

                        $inner->orWhere(fn ($both) => $both
                            ->where('first_name', 'like', $first)
                            ->where('last_name', 'like', $last));
                    }
                });
            })
            ->when($filters['class_id'], fn ($query, $id) => $query->where('class_id', $id))
            ->when($filters['grade_level_id'], fn ($query, $id) => $query->whereHas(
                'schoolClass',
                fn ($class) => $class->where('grade_level_id', $id)
            ))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['gender'], fn ($query, $gender) => $query->where('gender', $gender))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'students'    => $students,
            'classes'     => SchoolClass::orderBy('class_name')->get(),
            'gradeLevels' => GradeLevel::orderBy('order')->get(),
            'statuses'    => Student::STATUSES,
            'filters'     => $filters,
            'isFiltered'  => collect($filters)->filter()->isNotEmpty(),
        ]);"""

patch('app/Http/Controllers/Admin/AdminController.php', OLD_INDEX, NEW_INDEX,
      'AdminController@index: search + filters')


# ── 2. View: turn the decorative bar into a real GET form ────────────────────
OLD_FILTERS_START = """        <!-- Filters Section -->
        <div
            class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 mb-6 shadow-sm flex flex-wrap items-center gap-gutter">
            <div class="flex flex-col gap-1.5 min-w-[180px]">
                <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Grade / Level</label>
                <select
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option>All Grades</option>
                    <option>Grade 10</option>
                    <option>Grade 11</option>
                    <option>Grade 12</option>
                </select>
            </div>
            <div class="flex flex-col gap-1.5 min-w-[150px]">
                <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Status</label>
                <select
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Suspended</option>
                    <option>Pending</option>
                </select>
            </div>
            <div class="flex flex-col gap-1.5 min-w-[120px]">
                <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Gender</label>
                <select
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option>All</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>
            <div class="h-10 w-px bg-surface-container-high"></div>
            <div class="flex items-center gap-2">
                <button
                    class="bg-secondary-container text-on-secondary-container px-4 py-2 rounded-lg font-label-sm text-label-sm font-semibold hover:opacity-90 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span>
                    Advanced Filters
                </button>
                <button class="text-on-surface-variant font-label-sm text-label-sm hover:underline">
                    Clear all
                </button>
            </div>"""

NEW_FILTERS_START = """        {{-- Filters. A plain GET form: every choice ends up in the query string,
             so a filtered list can be bookmarked, shared, or reloaded, and
             pagination keeps the filters via withQueryString(). --}}
        <form method="GET" action="{{ route('admin.students.index') }}"
            class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 mb-6 shadow-sm flex flex-wrap items-end gap-gutter">

            <div class="flex flex-col gap-1.5 min-w-[240px] flex-1">
                <label for="search" class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Search</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant">search</span>
                    <input id="search" name="search" type="search" value="{{ $filters['search'] }}"
                        placeholder="Name, admission number or guardian"
                        class="w-full bg-surface border-outline-variant rounded-lg text-body-md py-1.5 pl-9 focus:ring-primary focus:border-primary">
                </div>
            </div>

            <div class="flex flex-col gap-1.5 min-w-[160px]">
                <label for="grade_level_id" class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Grade / Level</label>
                <select id="grade_level_id" name="grade_level_id"
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option value="">All grades</option>
                    @foreach ($gradeLevels as $level)
                        <option value="{{ $level->grade_level_id }}" @selected($filters['grade_level_id'] == $level->grade_level_id)>
                            {{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1.5 min-w-[140px]">
                <label for="class_id" class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Class</label>
                <select id="class_id" name="class_id"
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option value="">All classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->class_id }}" @selected($filters['class_id'] == $class->class_id)>
                            {{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1.5 min-w-[150px]">
                <label for="status" class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Status</label>
                <select id="status" name="status"
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1.5 min-w-[120px]">
                <label for="gender" class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Gender</label>
                <select id="gender" name="gender"
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option value="">All</option>
                    <option value="Male" @selected($filters['gender'] === 'Male')>Male</option>
                    <option value="Female" @selected($filters['gender'] === 'Female')>Female</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="bg-secondary-container text-on-secondary-container px-4 py-2 rounded-lg font-label-sm text-label-sm font-semibold hover:opacity-90 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span>
                    Apply
                </button>
                @if ($isFiltered)
                    <a href="{{ route('admin.students.index') }}"
                        class="text-on-surface-variant font-label-sm text-label-sm hover:underline">Clear all</a>
                @endif
            </div>

            <p class="w-full font-body-sm text-body-sm text-on-surface-variant">
                @if ($isFiltered)
                    {{ $students->total() }} {{ Str::plural('student', $students->total()) }} match these filters.
                @else
                    {{ $students->total() }} {{ Str::plural('student', $students->total()) }} on roll.
                @endif
            </p>"""

patch('resources/views/admin/students/index.blade.php',
      OLD_FILTERS_START, NEW_FILTERS_START, 'students view: real filter form')

sys.exit(0 if ok else 1)
