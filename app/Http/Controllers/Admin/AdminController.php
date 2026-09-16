<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\GradeLevel;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Traits\GeneratesTemporaryPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    use GeneratesTemporaryPassword;

    /**
     * Admin Dashboard - Display key metrics and student roster
     */
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalStaff    = Teacher::count();
        $totalParents  = ParentProfile::count();
        $totalClasses  = SchoolClass::count();
        $totalSubjects = Subject::count();

        // Calculate total fees collected (only cleared/fully-paid fees)
        $feesCollected = Fee::cleared()->sum('amount_paid');

        // 5 most recently updated teachers for the dashboard panel
        $recentTeachers = Teacher::with(['user', 'classSubjects.subject'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        // Get students with their class and fee status
        $students = Student::with('schoolClass')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($student) {
                $totalFees = $student->fees()->sum('amount_due');
                $paidFees  = $student->fees()->cleared()->sum('amount_paid');
                $balance   = $totalFees - $paidFees;

                return [
                    'id'         => $student->student_id,
                    'name'       => $student->full_name,
                    'class'      => $student->schoolClass?->class_name ?? 'N/A',
                    'balance'    => $balance,
                    'fee_status' => $balance > 0 ? 'pending' : 'cleared',
                ];
            });

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalStaff',
            'totalParents',
            'totalClasses',
            'totalSubjects',
            'feesCollected',
            'recentTeachers',
            'students'
        ));
    }

    /**
     * Display a listing of students (Resource: index)
     */
    public function index(Request $request)
    {
        [$query, $filters] = $this->filteredStudents($request);

        $students = $query->paginate(20)->withQueryString();

        return view('admin.students.index', [
            'students'    => $students,
            'classes'     => SchoolClass::orderBy('class_name')->get(),
            'gradeLevels' => GradeLevel::orderBy('order')->get(),
            'statuses'    => Student::STATUSES,
            'filters'     => $filters,
            'isFiltered'  => collect($filters)->filter()->isNotEmpty(),
        ]);
    }

    /**
     * The student list, filtered by whatever is in the query string.
     *
     * Shared by the on-screen table and the CSV export so the file always
     * contains exactly the rows the admin was looking at — an export that
     * quietly ignores the filters is worse than no export at all.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: array}
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

        $query = Student::with('schoolClass', 'user', 'guardian')
            ->when($filters['search'], function ($query, $term) {
                // "Mary" matches either name or the admission number; "Mary Banda"
                // is treated as first + last rather than one string, so it works
                // without a driver-specific CONCAT.
                $parts = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);

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
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Show the form for creating a new student (Resource: create)
     */
    public function create()
    {
        $classes = SchoolClass::all();
        $parents = ParentProfile::with('user')->orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.students.create', compact('classes', 'parents'));
    }

    /**
     * Store a newly created student (Resource: store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'class_id' => 'required|exists:school_classes,class_id',
            'parent_user_id' => 'nullable|exists:users,id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'enrolment_date' => 'nullable|date',
            // A student login is optional — most young children don't need
            // one, the parent portal already covers them.
            'email' => 'nullable|email|unique:users,email',
        ]);

        if (! empty($validated['parent_user_id'])) {
            $validated['parent_user_id'] = (int) $validated['parent_user_id'];
        }

        $email = $validated['email'] ?? null;
        unset($validated['email']);

        // Generated up front so it can be flashed to admin after the
        // transaction commits — never stored anywhere in readable form.
        $temporary = $email ? $this->temporaryPassword() : null;

        try {
            $student = DB::transaction(function () use ($validated, $email, $temporary) {
                $student = Student::createWithGeneratedNumber($validated);

                if ($email) {
                    $user = User::create([
                        'name' => trim("{$student->first_name} {$student->last_name}"),
                        'email' => $email,
                        'password' => Hash::make($temporary),
                        'role_id' => Role::where('name', 'student')->value('id'),
                        'email_verified_at' => now(),
                        // Forces the student onto their settings page at
                        // first login until they choose their own password.
                        'must_change_password' => true,
                    ]);

                    $student->user_id = $user->id;
                    $student->save();
                }

                return $student;
            });

            $redirect = redirect()->route('admin.students.index')->with(
                'notification',
                $email
                    ? "Student created successfully! Student number: {$student->student_number}. Their one-time login password is:"
                    : "Student created successfully! Student number: {$student->student_number}."
            );

            return $email ? $redirect->with('temporary_password', $temporary) : $redirect;
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create student: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific student (Resource: show)
     */
    public function show(Student $student)
    {
        $student->load(
            'schoolClass',
            'grades.classSubject.subject',
            'grades.recordedByTeacher',
            'attendance.classSubject.subject',
            'attendance.recordedByTeacher',
            'fees',
            'user'
        );

        return view('admin.students.show', compact('student'));
    }

    /**
     * Show the form for editing a student (Resource: edit)
     */
    public function edit(Student $student)
    {
        // Relations used by the view: class (with homeroom teacher) for the
        // class dropdown, linked accounts for the read-only info panel, and
        // attendance records to compute the attendance-rate stat.
        $student->load('schoolClass.teacher', 'user', 'parentUser');

        $classes = SchoolClass::with('teacher')->get();

        // Attendance rate = (Present + Late) / total recorded sessions.
        // Late still counts as attended. Returns null when there are no
        // records yet so the view can show an em-dash instead of "0%".
        $total = $student->attendance()->count();
        $attendanceRate = null;

        if ($total > 0) {
            $attended = $student->attendance()->whereIn('status', ['Present', 'Late'])->count();
            $attendanceRate = round(($attended / $total) * 100, 1);
        }

        return view('admin.students.edit', compact('student', 'classes', 'attendanceRate'));
    }

    /**
     * Update a student (Resource: update)
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'class_id' => 'required|exists:school_classes,class_id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'enrolment_date' => 'nullable|date',
        ]);

        try {
            $student->update($validated);
            return redirect()->route('admin.students.show', $student)
                ->with('notification', 'Student updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update student: ' . $e->getMessage());
        }
    }

    /**
     * Delete a student (Resource: destroy)
     */
    public function destroy(Student $student)
    {
        try {
            $student->delete();
            return redirect()->route('admin.students.index')
                ->with('notification', 'Student deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete student: ' . $e->getMessage());
        }
    }

    /**
     * Manually refund a student's entire available account credit.
     *
     * Overpayment credit is normally carried forward and applied
     * automatically to the student's next fee (see Student::applyAvailableCredit()).
     * This is the exception path: a withdrawing or graduating student with
     * credit left over and no future fee to carry it into. Kept deliberately
     * simple — one button, no form — it refunds the full available balance;
     * how the money actually gets back to the family (cash, bank transfer,
     * etc.) happens outside the system, same as it did when the credit was
     * first created from an out-of-band overpayment.
     */
    public function refundCredit(Student $student)
    {
        $amount = (float) $student->credit_balance;

        if ($amount <= 0) {
            return back()->withErrors('This student has no account credit to refund.');
        }

        try {
            $student->refundCredit(
                $amount,
                'Full credit balance refunded by admin.',
                auth()->id()
            );

            return back()->with('notification', 'ZMW ' . number_format($amount, 2) . ' credit refunded successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors('Failed to refund credit: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of teachers and staff.
     */
    public function teachers()
    {
        $teachers = Teacher::with(['user', 'classSubjects.subject', 'classSubjects.schoolClass'])
            ->paginate(20);

        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Display a listing of classes and subjects.
     */
    public function classes()
    {
        $classes = SchoolClass::with(['teacher', 'subjects'])
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Display the administration settings page.
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Display the examinations page.
     */
    public function examinations()
    {
        return view('admin.examinations');
    }
}
