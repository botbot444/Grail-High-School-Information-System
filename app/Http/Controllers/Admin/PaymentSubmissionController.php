<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSubmission;
use App\Notifications\PaymentSubmissionReviewedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', PaymentSubmission::STATUS_PENDING);

        $submissions = PaymentSubmission::with(['fee.student', 'submittedBy'])
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => PaymentSubmission::pending()->count(),
            'approved' => PaymentSubmission::approved()->count(),
            'rejected' => PaymentSubmission::rejected()->count(),
        ];

        return view('admin.payment-submissions.index', [
            'submissions' => $submissions,
            'status'      => $status,
            'counts'      => $counts,
        ]);
    }

    public function show(PaymentSubmission $submission)
    {
        $submission->load(['fee.student', 'fee.feeItems', 'submittedBy', 'reviewedBy', 'payment']);

        return view('admin.payment-submissions.show', ['submission' => $submission]);
    }

    /**
     * Verified outside the system by the admin — this is the moment the
     * claim becomes a real Payment. Reuses Fee::applyPayment(), the exact
     * same ledger logic the bursar's direct entry uses.
     */
    public function approve(PaymentSubmission $submission)
    {
        abort_unless($submission->isPending(), 422, 'This submission has already been reviewed.');

        DB::transaction(function () use ($submission) {
            $fee = $submission->fee;

            $payment = $fee->applyPayment(
                (float) $submission->amount,
                $submission->payment_method,
                $submission->reference_number,
                $submission->notes,
                $submission->payment_date,
                auth()->id()
            );

            $submission->update([
                'status'       => PaymentSubmission::STATUS_APPROVED,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
                'payment_id'   => $payment->payment_id,
            ]);
        });

        $submission->refresh()->fee->student->guardian?->notify(
            new PaymentSubmissionReviewedNotification($submission)
        );

        return redirect()->route('admin.payment-submissions.index')
            ->with('notification', 'Payment approved and recorded against the fee.');
    }

    public function reject(Request $request, PaymentSubmission $submission)
    {
        abort_unless($submission->isPending(), 422, 'This submission has already been reviewed.');

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:500'],
        ]);

        $submission->update([
            'status'       => PaymentSubmission::STATUS_REJECTED,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        $submission->fee->student->guardian?->notify(
            new PaymentSubmissionReviewedNotification($submission)
        );

        return redirect()->route('admin.payment-submissions.index')
            ->with('notification', 'Submission rejected. The parent has been notified.');
    }
}
