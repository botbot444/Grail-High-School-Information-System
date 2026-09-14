import pathlib, sys

ok = True

def patch(path, old, new, label, count=1):
    global ok
    p = pathlib.Path(path); t = p.read_text()
    if old not in t:
        print(f'  ! {label}: anchor not found'); ok = False; return
    p.write_text(t.replace(old, new, count))
    print(f'  + {label}')


CTRL = 'app/Http/Controllers/Admin/AdminController.php'

# ── 1. imports ───────────────────────────────────────────────────────────────
patch(CTRL, "use Illuminate\\Http\\Request;",
      "use Illuminate\\Http\\Request;\nuse Symfony\\Component\\HttpFoundation\\StreamedResponse;",
      'import StreamedResponse')

# ── 2. index() delegates to a shared query builder ───────────────────────────
OLD_BODY = """        $filters = [
            'search'         => trim((string) $request->input('search', '')),
            'class_id'       => $request->integer('class_id') ?: null,
            'grade_level_id' => $request->integer('grade_level_id') ?: null,
            'status'         => $request->input('status') ?: null,
            'gender'         => $request->input('gender') ?: null,
        ];

        $students = Student::with('schoolClass', 'user')"""

NEW_BODY = """        [$query, $filters] = $this->filteredStudents($request);

        $students = $query"""

patch(CTRL, OLD_BODY, NEW_BODY, 'index(): use the shared query')

# strip the now-duplicated filter chain out of index(), leaving ordering+paging
OLD_CHAIN = """        $students = $query
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
            ->withQueryString();"""

NEW_CHAIN = """        $students = $query->paginate(20)->withQueryString();"""

patch(CTRL, OLD_CHAIN, NEW_CHAIN, 'index(): drop the duplicated chain')

# ── 3. the shared builder + the export ───────────────────────────────────────
ANCHOR = """    /**
     * Show the form for creating a new student (Resource: create)
     */
    public function create()"""

ADDITION = '''    /**
     * The student list, filtered by whatever is in the query string.
     *
     * Shared by the on-screen table and the CSV export so the file always
     * contains exactly the rows the admin was looking at — an export that
     * quietly ignores the filters is worse than no export at all.
     *
     * @return array{0: \\Illuminate\\Database\\Eloquent\\Builder, 1: array}
     */
    private function filteredStudents(Request $request): array
    {
        $filters = [
            'search'         => trim((string) $request->input('search', '')),
            'class_id'       => $request->integer('class_id') ?: null,
            'grade_level_id' => $request->integer('grade_level_id') ?: null,
            'status'         => $request->input('status') ?: null,
            'gender'         => $request->input('gender') ?: null,
        ];

        $query = Student::with('schoolClass', 'user')
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
            ->orderBy('first_name');

        return [$query, $filters];
    }

    /**
     * Download the current student list as CSV.
     *
     * Streamed and chunked: the roll is small today, but a real school's is
     * not, and building the whole file in memory first is how an export dies
     * on the one day someone actually needs it.
     */
    public function export(Request $request): StreamedResponse
    {
        [$query, $filters] = $this->filteredStudents($request);

        $applied = collect([
            'Search'  => $filters['search'] ?: null,
            'Class'   => $filters['class_id'] ? optional(SchoolClass::find($filters['class_id']))->class_name : null,
            'Grade'   => $filters['grade_level_id'] ? optional(GradeLevel::find($filters['grade_level_id']))->name : null,
            'Status'  => $filters['status'],
            'Gender'  => $filters['gender'],
        ])->filter();

        $filename = 'students_' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($query, $applied) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Grail SIS — Student List']);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i')]);

            if ($applied->isNotEmpty()) {
                fputcsv($handle, ['Filters', $applied->map(fn ($v, $k) => "{$k}: {$v}")->implode('; ')]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'Admission No', 'First Name', 'Last Name', 'Gender', 'Date of Birth',
                'Class', 'Grade', 'Status', 'Guardian', 'Guardian Phone', 'Enrolled On', 'Login Email',
            ]);

            $query->chunk(200, function ($students) use ($handle) {
                foreach ($students as $student) {
                    fputcsv($handle, [
                        $student->student_number,
                        $student->first_name,
                        $student->last_name,
                        $student->gender,
                        optional($student->date_of_birth)->format('Y-m-d'),
                        $student->schoolClass?->class_name,
                        $student->schoolClass?->grade_level,
                        $student->status,
                        $student->guardian_name,
                        $student->guardian_phone,
                        optional($student->enrolment_date)->format('Y-m-d'),
                        $student->user?->email,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\\"{$filename}\\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

''' + ANCHOR

patch(CTRL, ANCHOR, ADDITION, 'add filteredStudents() + export()')

# ── 4. route, declared BEFORE the resource so students/{student} cannot eat it ─
patch('routes/web.php',
      "        Route::resource('students', AdminController::class);",
      "        // Must precede the resource: students/{student} would otherwise\n"
      "        // swallow students/export and try to bind a model named \"export\".\n"
      "        Route::get('students/export', [AdminController::class, 'export'])->name('students.export');\n"
      "        Route::resource('students', AdminController::class);",
      'route: admin.students.export')

# ── 5. the Export CSV button, carrying the current filters ───────────────────
patch('resources/views/admin/students/index.blade.php',
      """                <a href="{{ route('admin.students.create') }}"
                    class="flex items-center gap-2 px-4 py-2.5 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined text-[20px]">file_download</span>
                    Export CSV
                </a>""",
      """                <a href="{{ route('admin.students.export', request()->query()) }}"
                    class="flex items-center gap-2 px-4 py-2.5 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined text-[20px]">file_download</span>
                    Export CSV
                </a>""",
      'view: Export CSV points at the export (and keeps the filters)')

# ── 6. the header search box, which searched nothing ─────────────────────────
patch('resources/views/admin/header.blade.php',
      """        <div class="relative w-full max-w-md">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input
                class="w-full pl-10 pr-4 py-2 bg-surface-container border-none rounded-full text-body-md focus:ring-2 focus:ring-primary focus:bg-white transition-all"
                placeholder="Search student records, classes, or reports..." type="text" />
        </div>""",
      """        <form method="GET" action="{{ route('admin.students.index') }}" class="relative w-full max-w-md">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input name="search" value="{{ request('search') }}"
                class="w-full pl-10 pr-4 py-2 bg-surface-container border-none rounded-full text-body-md focus:ring-2 focus:ring-primary focus:bg-white transition-all"
                placeholder="Search students by name or admission number" type="search" />
        </form>""",
      'header: search box actually searches')

sys.exit(0 if ok else 1)
