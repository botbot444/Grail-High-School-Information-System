<?php

namespace App\Http\Requests;

use App\Models\Fee;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fee = Fee::find($this->input('fee_id'));

        return [
            'fee_id'            => ['required', 'integer', 'exists:fees,fee_id'],
            'amount'            => ['required', 'numeric', 'min:0.01', 'max:' . $this->sanityCeiling($fee)],
            'payment_method'    => ['required', 'in:cash,bank_transfer,cheque,mobile_money,card'],
            'reference_number'  => ['nullable', 'string', 'max:100'],
            'payment_date'      => ['required', 'date', 'before_or_equal:today'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'proof'             => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ];
    }

    /** Same fat-finger guard used for direct bursar entry — see StorePaymentRequest. */
    private function sanityCeiling(?Fee $fee): float
    {
        $due = $fee ? (float) $fee->amount_due : 0.0;

        return max($due * 5, 10000);
    }
}
