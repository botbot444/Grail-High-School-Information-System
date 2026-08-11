<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeRequest;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\FeeCategory;
use App\Models\FeeItem;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    protected static function feeCacheKey(): string
    {
        return 'fees_index_' . md5(serialize(auth()->id()) . request()->fullUrl());
    }

    public static function clearFeeCache(): void
    {
        $prefix = config('cache.prefix', 'laravel_cache_');

        // Always clear the current request's index key (covers the plain index).
        Cache::forget(static::feeCacheKey());

        // Wipe every other cached fees_index_* variant (filters / pagination),
        // because writes are issued on different URLs than the index view.
        try {
            $stored = DB::table('cache')
                ->where('key', 'like', $prefix . 'fees_index_%')
                ->pluck('key');

            foreach ($stored as $fullKey) {
                Cache::forget(mb_substr($fullKey, mb_strlen($prefix)));
            }
        } catch (\Throwable $e) {
            // Non-database cache store (file/redis/array): the forget() above
            // already covers the current index view; nothing else to purge.
        }
    }

    public function index(Request $request)
    {
        $query = Fee::with([
            'student.schoolClass',
            'student.user:id,name',
            'feeItems:fee_item_id,fee_id,item_name,amount',
        ]);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fees = Cache::remember(static::feeCacheKey(), 300, function () use ($query) {
            return $query->paginate(15);
        });

        $academicYears = AcademicYear::orderByDesc('start_date')->get(['year_id', 'label']);
        $terms         = Term::orderBy('start_date')->get(['term_id', 'name', 'academic_year_id']);

        return view('admin.fees.index', compact('fees', 'academicYears', 'terms'));
    }

    public function create()
    {
        $students      = Student::with('user', 'schoolClass')->orderBy('first_name')->get();
        $categories    = FeeCategory::orderBy('sort_order')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get(['year_id', 'label']);
        $terms         = Term::orderBy('start_date')->get(['term_id', 'name', 'academic_year_id']);

        return view('admin.fees.create', compact('students', 'categories', 'academicYears', 'terms'));
    }

    public function store(StoreFeeRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                $termModel = Term::find($validated['term_id']);
                $yearModel = AcademicYear::find($validated['academic_year_id']);

                // Build fee header. amount_due is supplied by the form but will be
                // recalculated from the items below.
                $fee = Fee::create([
                    'student_id'       => $validated['student_id'],
                    'description'      => $validated['description'] ?? null,
                    'amount_due'       => 0,
                    'amount_paid'      => 0.00,
                    'balance'          => 0.00,
                    'due_date'         => $validated['due_date'],
                    'status'           => 'Pending',
                    'term'             => $termModel?->name ?? $validated['term'],
                    'academic_year'    => $yearModel?->label ?? $validated['academic_year'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'term_id'          => $validated['term_id'],
                ]);

                foreach ($validated['fee_items'] as $item) {
                    FeeItem::create([
                        'fee_id'    => $fee->fee_id,
                        'item_name' => $item['item_name'],
                        'category'  => $item['category'],
                        'amount'    => $item['amount'],
                    ]);
                }

                // Re-sync the header from the items we just persisted.
                $fee->load('feeItems');
                $fee->recalculateAmountDue();
                $fee->save();
            });
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->withErrors([
                'term_id' => 'A fee already exists for this student, term and academic year.',
            ]);
        }

        static::clearFeeCache();

        return redirect()->route('admin.fees.index')
            ->with('notification', 'Fee created successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action'  => 'required|in:mark_cleared,mark_overdue,send_reminder,export_selected,delete_selected',
            'fee_ids' => ['required', 'array', 'min:1'],
            'fee_ids.*' => ['exists:fees,fee_id'],
        ]);

        $feeIds = $validated['fee_ids'];
        $fees   = Fee::whereIn('fee_id', $feeIds)->get();

        if ($validated['action'] === 'export_selected') {
            return $this->exportSelected($fees);
        }

        DB::transaction(function () use ($fees, $validated, $feeIds) {
            switch ($validated['action']) {
                case 'mark_cleared':
                    foreach ($fees as $fee) {
                        $fee->amount_paid = $fee->amount_due;
                        $fee->balance     = 0.00;
                        $fee->status      = 'Cleared';
                        $fee->last_updated = \Carbon\Carbon::now();
                        $fee->save();
                    }
                    break;

                case 'mark_overdue':
                    $fees->update(['due_date' => \Carbon\Carbon::today()]);
                    foreach ($fees as $fee) {
                        $fee->updateStatus();
                    }
                    break;

                case 'send_reminder':
                    $this->sendBulkReminders($fees);
                    break;

                case 'delete_selected':
                    $fees->each->delete();
                    break;
            }

            \App\Models\AuditLog::create([
                'user_id'      => auth()->id(),
                'auditable_type' => Fee::class,
                'auditable_id'   => null,
                'action'         => 'bulk_' . $validated['action'],
                'new_values'     => ['fee_ids' => $feeIds],
                'reason'         => 'Bulk operation performed',
            ]);
        });

        static::clearFeeCache();

        return redirect()->route('admin.fees.index')
            ->with('notification', 'Bulk action completed successfully.');
    }

    public function sendReminder(Fee $fee)
    {
        $student = $fee->student;
        $guardian = $student->guardian;

        if ($guardian && $guardian->email) {
            $guardian->notify(new \App\Notifications\FeeReminderNotification($fee));

            return back()->with('notification', 'Reminder sent to ' . $guardian->email);
        }

        return back()->with('error', 'No guardian email found for this student.');
    }

    protected function sendBulkReminders($fees): void
    {
        $sent = 0;
        $failed = 0;

        foreach ($fees as $fee) {
            try {
                $student = $fee->student;
                if ($student->guardian && $student->guardian->email) {
                    $student->guardian->notify(new \App\Notifications\FeeReminderNotification($fee));
                    $sent++;
                }
            } catch (\Throwable $e) {
                $failed++;
                \Log::error('Failed to send fee reminder', ['fee' => $fee->fee_id, 'error' => $e->getMessage()]);
            }
        }

        if ($sent > 0) {
            session()->flash('notification', "Reminders sent: {$sent}, Failed: {$failed}");
        } else {
            session()->flash('error', 'No reminders could be sent. Ensure guardians have email addresses.');
        }
    }

    protected function exportSelected($fees)
    {
        $filename = 'fees_export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($fees) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Fee ID', 'Student', 'Class', 'Term', 'Year', 'Amount Due', 'Paid', 'Balance', 'Status', 'Due Date']);

            foreach ($fees as $fee) {
                fputcsv($handle, [
                    $fee->fee_id,
                    $fee->student->full_name ?? 'N/A',
                    $fee->student->schoolClass->class_name ?? 'N/A',
                    $fee->term,
                    $fee->academic_year,
                    number_format($fee->amount_due, 2),
                    number_format($fee->amount_paid, 2),
                    number_format($fee->balance, 2),
                    $fee->status,
                    \Carbon\Carbon::parse($fee->due_date)->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Fee $fee)
    {
        $fee->load('feeItems', 'payments.recordedBy', 'student.schoolClass', 'student.user');

        return view('admin.fees.show', compact('fee'));
    }

    public function edit(Fee $fee)
    {
        $fee->load('feeItems');
        $students      = Student::with('user', 'schoolClass')->orderBy('first_name')->get();
        $categories    = FeeCategory::orderBy('sort_order')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get(['year_id', 'label']);
        $terms         = Term::orderBy('start_date')->get(['term_id', 'name', 'academic_year_id']);

        return view('admin.fees.edit', compact('fee', 'students', 'categories', 'academicYears', 'terms'));
    }

    public function update(StoreFeeRequest $request, Fee $fee)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($fee, $validated) {
            $termModel = Term::find($validated['term_id']);
            $yearModel = AcademicYear::find($validated['academic_year_id']);

            $fee->update([
                'student_id'       => $validated['student_id'],
                'description'      => $validated['description'] ?? null,
                'due_date'         => $validated['due_date'],
                'term'             => $termModel?->name ?? $validated['term'],
                'academic_year'    => $yearModel?->label ?? $validated['academic_year'],
                'academic_year_id' => $validated['academic_year_id'],
                'term_id'          => $validated['term_id'],
            ]);

            // Sync items: remove those not in the incoming list, add/update the rest.
            $incoming = collect($validated['fee_items'])->mapWithKeys(function ($item, $index) {
                return [$index => $item];
            });

            $fee->feeItems()->whereNotIn('item_name', $incoming->pluck('item_name'))
                ->whereNotIn('amount', $incoming->pluck('amount'))
                ->delete();

            foreach ($incoming as $item) {
                FeeItem::updateOrCreate(
                    [
                        'fee_id'    => $fee->fee_id,
                        'item_name' => $item['item_name'],
                        'category'  => $item['category'],
                    ],
                    ['amount' => $item['amount']]
                );
            }

            $fee->load('feeItems');
            $fee->recalculateAmountDue();
            $fee->save();
        });

        static::clearFeeCache();

        return redirect()->route('admin.fees.index')
            ->with('notification', 'Fee updated successfully.');
    }

    public function destroy(Fee $fee)
    {
        try {
            $fee->delete();
            static::clearFeeCache();

            return redirect()->route('admin.fees.index')
                ->with('notification', 'Fee deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete fee: ' . $e->getMessage());
        }
    }
}
