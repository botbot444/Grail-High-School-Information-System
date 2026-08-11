<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Fee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Fee $fee)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($fee, $validated) {
            $payment = $fee->payments()->create([
                'amount'          => $validated['amount'],
                'payment_method'  => $validated['payment_method'],
                'reference_number'=> $validated['reference_number'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'payment_date'    => $validated['payment_date'],
                'recorded_by'     => auth()->id(),
            ]);

            // Reuse the existing state machine so balance/status/last_updated stay consistent.
            $fee->recordPayment((float) $validated['amount']);
        });

        // A payment changes fee balance/status shown on the index — refresh it.
        FeeController::clearFeeCache();

        return redirect()->route('admin.fees.show', $fee->fee_id)
            ->with('notification', 'Payment recorded successfully.');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['fee.student', 'fee.feeItems', 'recordedBy']);

        return view('admin.fees.receipt', compact('payment'));
    }
}
