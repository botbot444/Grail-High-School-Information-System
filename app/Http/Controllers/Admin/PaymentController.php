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
            $amount  = (float) $validated['amount'];
            $applied = min($amount, (float) $fee->balance);
            $overage = round($amount - $applied, 2);

            // The Payment row always reflects the actual amount received —
            // accounting accuracy over convenience. If it's more than this
            // fee owes, only $applied goes toward this fee's balance; the
            // rest becomes account credit (Student::grantCredit()) rather
            // than being silently dropped.
            $payment = $fee->payments()->create([
                'amount'          => $amount,
                'payment_method'  => $validated['payment_method'],
                'reference_number'=> $validated['reference_number'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'payment_date'    => $validated['payment_date'],
                'recorded_by'     => auth()->id(),
            ]);

            // Reuse the existing state machine so balance/status/last_updated stay consistent.
            if ($applied > 0) {
                $fee->recordPayment($applied);
            }

            if ($overage > 0) {
                $fee->student->grantCredit($overage, [
                    'source_payment_id' => $payment->payment_id,
                    'recorded_by'       => auth()->id(),
                    'notes'             => "Overpayment on fee #{$fee->fee_id} ({$fee->term} {$fee->academic_year}).",
                ]);
            }
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
