<?php

namespace App\Http\Requests;

use App\Models\Fee;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fee = $this->route('fee');

        return [
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:' . $this->sanityCeiling($fee)],
            'payment_method' => ['required', 'in:cash,bank_transfer,cheque,mobile_money,card'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'payment_date'   => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Not a "can't pay more than you owe" cap — a real bank deposit or
     * mobile money transfer doesn't round to the exact balance, and
     * rejecting a genuine overpayment used to silently drop real cash from
     * the ledger (the excess had nowhere to go). See
     * Student::grantCredit() — money over the fee's balance is now carried
     * forward as account credit instead of being blocked outright. This is
     * only a fat-finger guard: an amount many times the fee itself is
     * almost certainly a typo, not a legitimate payment.
     */
    private function sanityCeiling(?Fee $fee): float
    {
        $due = $fee ? (float) $fee->amount_due : 0.0;

        return max($due * 5, 10000);
    }
}
