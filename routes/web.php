<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminParentController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\Admin\AdminClassController;
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\FeeCategoryController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\TermController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Teacher\ReportCardController as TeacherReportCardController;
use App\Http\Controllers\Admin\ReportCardController as AdminReportCardController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\Parent\ParentController;
use App\Http\Controllers\Student\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/welcome', 'welcome')->name('welcome');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('/examinations', [AdminController::class, 'examinations'])->name('examinations');
        Route::resource('teachers', AdminTeacherController::class);
        Route::resource('parents', AdminParentController::class);
        Route::resource('classes', AdminClassController::class);
        Route::resource('subjects', AdminSubjectController::class);
        Route::resource('students', AdminController::class);

        // ── Fees ──────────────────────────────────────────────────────────────
        Route::resource('fees', FeeController::class);
        // Declared ahead of the /fees/{fee} routes so "lookup" is not read as a fee id.
        Route::get('/fees-lookup', [FeeController::class, 'lookup'])->name('fees.lookup');
        Route::post('/fees/{fee}/payments', [PaymentController::class, 'store'])->name('fees.payments.store');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        // Report cards (Phase 11) — browse, print, and the unfinalize override.
        Route::get('/report-cards', [AdminReportCardController::class, 'index'])->name('report-cards.index');
        Route::get('/report-cards/{student}', [AdminReportCardController::class, 'show'])->name('report-cards.show');
        Route::post('/report-cards/{class}/unfinalize', [AdminReportCardController::class, 'unfinalize'])->name('report-cards.unfinalize');
        Route::post('/fees/bulk-action', [FeeController::class, 'bulkAction'])->name('fees.bulk-action');
        Route::post('/fees/{fee}/send-reminder', [FeeController::class, 'sendReminder'])->name('fees.send-reminder');

        // ── Fee Categories (settings) ────────────────────────────────────────
        Route::get('/settings/categories', [FeeCategoryController::class, 'index'])->name('categories.index');
        Route::post('/settings/categories', [FeeCategoryController::class, 'store'])->name('categories.store');
        Route::put('/settings/categories/{feeCategory}', [FeeCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/settings/categories/{feeCategory}', [FeeCategoryController::class, 'destroy'])->name('categories.destroy');

        // Payment instructions shown to parents (bank / mobile money details).
        Route::get('/settings/payments', [PaymentSettingsController::class, 'edit'])->name('settings.payments');
        Route::put('/settings/payments', [PaymentSettingsController::class, 'update'])->name('settings.payments.update');

        // ── Audit Logs ───────────────────────────────────────────────────────
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // --- School Calendar (Phase 3) ---
        Route::resource('academic-years', AcademicYearController::class);
        Route::resource('terms', TermController::class);
        Route::resource('holidays', HolidayController::class);
        Route::resource('grade-levels', GradeLevelController::class);
        Route::resource('periods', PeriodController::class);
        Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
        Route::post('/timetable/slots', [TimetableController::class, 'store'])->name('timetable.slots.store');
        Route::post('/timetable/copy', [TimetableController::class, 'copyToTerm'])->name('timetable.copy');
        Route::post('/timetable/clear', [TimetableController::class, 'clear'])->name('timetable.clear');


        // ── Reports & Analytics (Phase 4) ────────────────────────────────────
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/fee-collection', [ReportController::class, 'feeCollection'])->name('fee-collection');
            Route::get('/fee-collection/export', [ReportController::class, 'exportFeeCollection'])->name('fee-collection.export');
        });

        // ── Student financials & statements (Phase 4) ───────────────────────
        Route::get('/students/{student}/financials', [ReportController::class, 'studentFinancials'])->name('students.financials');
        Route::get('/students/{student}/statement', [ReportController::class, 'statement'])->name('students.statement');

    });

// Teacher Routes
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/marks', [TeacherController::class, 'marks'])->name('marks');
        Route::post('/marks', [TeacherController::class, 'storeMarks'])->name('marks.store');

        // Placeholder destinations wired to the teacher dashboard sidebar/links.
        // Each renders the shared teacher layout with a "coming soon" card until
        // its own page is integrated from the teacher portal HTML.
        Route::get('/classes', [TeacherController::class, 'classes'])->name('classes');
        Route::get('/timetable', [TeacherController::class, 'timetable'])->name('timetable');
        Route::view('/attendance', 'teacher.placeholder')->defaults('placeholder', 'Record Attendance')->name('attendance');
        Route::view('/performance', 'teacher.placeholder')->defaults('placeholder', 'Class Performance')->name('performance');
        Route::view('/announcements', 'teacher.placeholder')->defaults('placeholder', 'Announcements')->name('announcements');
        Route::view('/settings', 'teacher.placeholder')->defaults('placeholder', 'Settings')->name('settings');

        // Assignments — authoring and marking.
        Route::get('/assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])->name('assignments.edit');
        Route::put('/assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::get('/assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::put('/assignments/{assignment}/submissions/{submission}', [TeacherAssignmentController::class, 'grade'])->name('assignments.grade');

        // Report cards (Phase 11) — comments and the finalize workflow.
        Route::get('/report-cards', [TeacherReportCardController::class, 'index'])->name('report-cards.index');
        Route::get('/report-cards/class/{class}', [TeacherReportCardController::class, 'show'])->name('report-cards.show');
        Route::post('/report-cards/class/{class}/finalize', [TeacherReportCardController::class, 'finalize'])->name('report-cards.finalize');
        Route::post('/report-cards/class/{class}/students/{student}/comment', [TeacherReportCardController::class, 'saveOverallComment'])->name('report-cards.comment');
        Route::get('/report-cards/class/{class}/students/{student}/preview', [TeacherReportCardController::class, 'preview'])->name('report-cards.preview');
        Route::get('/report-cards/subjects/{classSubject}', [TeacherReportCardController::class, 'subjectComments'])->name('report-cards.subjects');
        Route::post('/report-cards/subjects/{classSubject}', [TeacherReportCardController::class, 'saveSubjectComments'])->name('report-cards.subjects.save');
    });

// Parent Routes
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
        Route::get('/children', [ParentController::class, 'children'])->name('children');
        Route::post('/children/switch', [ParentController::class, 'switchChild'])->name('switch-child');
        Route::get('/attendance', [ParentController::class, 'attendance'])->name('attendance');
        Route::get('/performance', [ParentController::class, 'performance'])->name('performance');
        Route::get('/reports', [ParentController::class, 'reports'])->name('reports');
        Route::get('/timetable', [ParentController::class, 'timetable'])->name('timetable');
        Route::get('/assignments', [ParentController::class, 'assignments'])->name('assignments');
        Route::get('/fees', [ParentController::class, 'fees'])->name('fees');
        Route::get('/settings', [ParentController::class, 'settings'])->name('settings');
        Route::patch('/settings', [ParentController::class, 'updateSettings'])->name('settings.update');
        Route::get('/fees/{fee}', [ParentController::class, 'showFee'])->name('fees.show');
        Route::get('/payments/{payment}/receipt', [ParentController::class, 'receipt'])->name('payments.receipt');
        Route::get('/children/{student}/report-cards/{term}', [ParentController::class, 'reportCard'])->name('report-card');
    });

// Student Routes
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/results', [StudentController::class, 'results'])->name('results');
        Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
        Route::get('/timetable', [StudentController::class, 'timetable'])->name('timetable');
        Route::get('/settings', [StudentController::class, 'settings'])->name('settings');

        // Assignments — student view and submission.
        Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
        Route::post('/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');

        // Awaiting their own phases: report cards (Phase 11), announcements (Phase 5).
        Route::get('/report-cards', [StudentController::class, 'reportCards'])->name('report-cards');
        Route::get('/report-cards/{term}', [StudentController::class, 'reportCard'])->name('report-card');
        Route::get('/announcements', [StudentController::class, 'announcements'])->name('announcements');
    });

require __DIR__.'/auth.php';
