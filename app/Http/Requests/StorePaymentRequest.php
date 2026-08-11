<?php

namespace App\Http\Requests;

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
            'amount'         => ['required', 'numeric', 'min:0.01', 'max:' . ($fee?->balance ?? 999999999)],
            'payment_method' => ['required', 'in:cash,bank_transfer,cheque,mobile_money,card'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:500'],
            'payment_date'   => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
