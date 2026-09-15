<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreParentRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_first_name' => ['required', 'string', 'max:255'],
            'parent_last_name'  => ['required', 'string', 'max:255'],
            'parent_email'      => [
                'required', 'email', 'max:255',
                'unique:users,email',
                // A soft-deleted parent's email is free to reuse.
                Rule::unique('parents', 'email')->whereNull('deleted_at'),
                // A rejected request doesn't lock the email out — only block
                // while a submission for it is still awaiting review.
                Rule::unique('registration_requests', 'parent_email')->where('status', 'pending'),
            ],
            'parent_password'      => ['required', 'confirmed', Password::defaults()],
            'parent_phone'         => ['nullable', 'string', 'max:20'],
            'parent_address'       => ['nullable', 'string', 'max:500'],
            'parent_occupation'    => ['nullable', 'string', 'max:255'],
            'parent_national_id'   => ['nullable', 'string', 'max:50'],

            'child_first_name'    => ['required', 'string', 'max:255'],
            'child_last_name'     => ['required', 'string', 'max:255'],
            'child_date_of_birth' => ['required', 'date', 'before:today'],
            'child_gender'        => ['required', 'in:Male,Female'],
            'child_email'         => [
                'required', 'email', 'max:255',
                'unique:users,email',
                Rule::unique('registration_requests', 'child_email')->where('status', 'pending'),
            ],
        ];
    }
}
