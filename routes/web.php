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
        Route::post('/fees/{fee}/payments', [PaymentController::class, 'store'])->name('fees.payments.store');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::post('/fees/bulk-action', [FeeController::class, 'bulkAction'])->name('fees.bulk-action');
        Route::post('/fees/{fee}/send-reminder', [FeeController::class, 'sendReminder'])->name('fees.send-reminder');

        // ── Fee Categories (settings) ────────────────────────────────────────
        Route::get('/settings/categories', [FeeCategoryController::class, 'index'])->name('categories.index');
        Route::post('/settings/categories', [FeeCategoryController::class, 'store'])->name('categories.store');
        Route::put('/settings/categories/{feeCategory}', [FeeCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/settings/categories/{feeCategory}', [FeeCategoryController::class, 'destroy'])->name('categories.destroy');

        // ── Audit Logs ───────────────────────────────────────────────────────
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // ── Parent fee view (for notification CTAs) ──────────────────────────
        Route::middleware(['auth', 'role:parent'])
            ->prefix('parent')
            ->name('parent.')
            ->group(function () {
                Route::get('/fees/{fee}', [ParentController::class, 'showFee'])->name('fees.show');
            });
    });

// Teacher Routes
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/marks', [TeacherController::class, 'marks'])->name('marks');
        Route::post('/marks', [TeacherController::class, 'storeMarks'])->name('marks.store');
    });

// Parent Routes
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
    });

// Student Routes
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    });

require __DIR__.'/auth.php';
