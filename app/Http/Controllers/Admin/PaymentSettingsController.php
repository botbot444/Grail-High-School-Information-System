<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The bank and mobile money details parents are shown on the "How to pay"
 * panel. Kept in settings rather than config so the bursar can correct an
 * account number without a deploy.
 */
class PaymentSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.payment-instructions', [
            'settings' => SchoolSetting::all_settings(),
            'keys'     => SchoolSetting::PAYMENT_KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_bank_name'           => ['nullable', 'string', 'max:120'],
            'payment_bank_account_name'   => ['nullable', 'string', 'max:120'],
            'payment_bank_account_number' => ['nullable', 'string', 'max:40'],
            'payment_bank_branch'         => ['nullable', 'string', 'max:120'],
            'payment_momo_mtn'            => ['nullable', 'string', 'max:30'],
            'payment_momo_airtel'         => ['nullable', 'string', 'max:30'],
            'payment_note'                => ['nullable', 'string', 'max:1000'],
        ]);

        foreach (array_keys(SchoolSetting::PAYMENT_KEYS) as $key) {
            SchoolSetting::set($key, $validated[$key] ?? null);
        }

        return back()->with('notification', 'Payment details updated. Parents will see them on their fees page.');
    }
}
