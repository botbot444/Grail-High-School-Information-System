<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentSubmissionRequest;
use App\Models\Fee;
use App\Models\PaymentSubmission;
use App\Models\Student;

class PaymentSubmissionController extends Controller
{
    /**
     * A parent's claim of a payment made outside the system, with proof
     * attached. This never touches the fee balance — an admin has to review
     * and approve it first (Admin\PaymentSubmissionController@approve).
     */
    public function store(StorePaymentSubmissionRequest $request)
    {
        $validated = $request->validated();
        $fee = Fee::findOrFail($validated['fee_id']);

        // A parent may only submit proof against one of their own children's fees.
        $owned = Student::where('parent_user_id', auth()->id())
            ->where('student_id', $fee->student_id)
            ->exists();

        abort_unless($owned, 403);

        $path = $request->file('proof')->store("payment-proofs/{$fee->fee_id}", 'public');

        PaymentSubmission::create([
            'fee_id'                  => $fee->fee_id,
            'amount'                  => $validated['amount'],
            'payment_method'          => $validated['payment_method'],
            'reference_number'        => $validated['reference_number'] ?? null,
            'payment_date'            => $validated['payment_date'],
            'proof_path'              => $path,
            'proof_original_filename' => $request->file('proof')->getClientOriginalName(),
            'notes'                   => $validated['notes'] ?? null,
            'status'                  => PaymentSubmission::STATUS_PENDING,
            'submitted_by'            => auth()->id(),
        ]);

        return redirect()->route('parent.fees', ['child_id' => $fee->student_id])
            ->with('notification', 'Proof of payment submitted — an admin will review it shortly.');
    }
}
