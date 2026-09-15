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
            // The Payment row always reflects the actual amount received —
            // accounting accuracy over convenience. If it's more than this
            // fee owes, only the balance is applied to this fee; the rest
            // becomes account credit (Student::grantCredit()) rather than
            // being silently dropped. See Fee::applyPayment().
            $fee->applyPayment(
                (float) $validated['amount'],
                $validated['payment_method'],
                $validated['reference_number'] ?? null,
                $validated['notes'] ?? null,
                $validated['payment_date'],
                auth()->id()
            );
        });

        // A payment changes fee balance/status shown on the index — refresh it.
        FeeController::clearFeeCache();

        return redirect()->route('admin.fees.show', $fee->fee_id)
            ->with('notification', 'Payment recorded successfully.');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['fee.student', 'fee.feeItems', 'recordedBy']);

        return view('admin.fees.receipt', [
            'payment'   => $payment,
            'backUrl'   => route('admin.fees.show', $payment->fee->fee_id),
            'backLabel' => 'Back to Fee',
        ]);
    }
}
