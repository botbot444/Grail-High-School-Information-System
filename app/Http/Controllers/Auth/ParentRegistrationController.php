<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParentRegistrationRequest;
use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Public sign-up: a parent registers themselves and one new child together.
 * Nothing here is real — no `users` row, no `students` row — until an admin
 * approves it (Admin\RegistrationRequestController). This always represents
 * a brand-new admission; linking a parent to an already-enrolled student
 * stays a separate, admin-only action.
 */
class ParentRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.parent-register');
    }

    public function store(StoreParentRegistrationRequest $request)
    {
        $validated = $request->validated();

        RegistrationRequest::create([
            'parent_first_name'  => $validated['parent_first_name'],
            'parent_last_name'   => $validated['parent_last_name'],
            'parent_email'       => $validated['parent_email'],
            // The parent's own choice, hashed now — never regenerated or emailed.
            'parent_password'    => Hash::make($validated['parent_password']),
            'parent_phone'       => $validated['parent_phone'] ?? null,
            'parent_address'     => $validated['parent_address'] ?? null,
            'parent_occupation'  => $validated['parent_occupation'] ?? null,
            'parent_national_id' => $validated['parent_national_id'] ?? null,
            'child_first_name'    => $validated['child_first_name'],
            'child_last_name'     => $validated['child_last_name'],
            'child_date_of_birth' => $validated['child_date_of_birth'],
            'child_gender'        => $validated['child_gender'],
            'child_email'         => $validated['child_email'],
            'status' => RegistrationRequest::STATUS_PENDING,
        ]);

        return redirect()->route('registration.submitted');
    }

    public function submitted(): View
    {
        return view('auth.registration-submitted');
    }
}
