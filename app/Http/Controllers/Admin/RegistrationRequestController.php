<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\RegistrationRequest;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Notifications\RegistrationRequestReviewedNotification;
use App\Traits\GeneratesTemporaryPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class RegistrationRequestController extends Controller
{
    use GeneratesTemporaryPassword;

    public function index(Request $request)
    {
        $status = $request->query('status', RegistrationRequest::STATUS_PENDING);

        $requests = RegistrationRequest::query()
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => RegistrationRequest::pending()->count(),
            'approved' => RegistrationRequest::approved()->count(),
            'rejected' => RegistrationRequest::rejected()->count(),
        ];

        return view('admin.registration-requests.index', [
            'requests' => $requests,
            'status'   => $status,
            'counts'   => $counts,
        ]);
    }

    public function show(RegistrationRequest $registrationRequest)
    {
        $registrationRequest->load(['reviewedBy', 'createdParentUser', 'createdStudent']);

        return view('admin.registration-requests.show', ['registrationRequest' => $registrationRequest]);
    }

    /**
     * Admits the child and activates the parent's account. This is a real
     * admission decision, not a rubber-stamp — we only hold what the parent
     * submitted; verifying it is on whoever's approving, same as any other
     * school enrollment.
     */
    public function approve(RegistrationRequest $registrationRequest)
    {
        abort_unless($registrationRequest->isPending(), 422, 'This request has already been reviewed.');

        // Belt-and-suspenders re-check: submission-time validation blocks a
        // duplicate national ID against approved parents and other *pending*
        // requests, but two requests can still both be pending with the same
        // ID (one submitted before that check existed, or approved between
        // this page loading and this click) — catch it here with a message
        // an admin can act on instead of a raw constraint-violation crash.
        if ($registrationRequest->parent_national_id
            && ParentProfile::where('national_id', $registrationRequest->parent_national_id)->exists()) {
            return back()->withErrors(
                'Another parent already has national ID "'.$registrationRequest->parent_national_id.'" on file. '
                .'Reject this request and ask the applicant to correct it, or clear the ID before approving.'
            );
        }

        // Generated up front so it can be emailed once the transaction
        // commits — never stored anywhere in readable form.
        $childTemporary = $this->temporaryPassword();

        DB::transaction(function () use ($registrationRequest, $childTemporary) {
            $parentUser = User::create([
                'name'              => $registrationRequest->parent_full_name,
                'email'             => $registrationRequest->parent_email,
                // Already hashed at submission — the parent's own choice, never regenerated.
                'password'          => $registrationRequest->parent_password,
                'role_id'           => Role::where('name', 'parent')->value('id'),
                'email_verified_at' => now(),
            ]);

            ParentProfile::create([
                'user_id'      => $parentUser->id,
                'first_name'   => $registrationRequest->parent_first_name,
                'last_name'    => $registrationRequest->parent_last_name,
                'email'        => $registrationRequest->parent_email,
                'phone'        => $registrationRequest->parent_phone,
                'address'      => $registrationRequest->parent_address,
                'occupation'   => $registrationRequest->parent_occupation,
                'national_id'  => $registrationRequest->parent_national_id,
            ]);

            $childUser = User::create([
                'name'                 => $registrationRequest->child_full_name,
                'email'                => $registrationRequest->child_email,
                'password'             => Hash::make($childTemporary),
                'role_id'              => Role::where('name', 'student')->value('id'),
                'email_verified_at'    => now(),
                // Forces the student onto their settings page at first
                // login until they choose their own password.
                'must_change_password' => true,
            ]);

            $student = Student::createWithGeneratedNumber([
                'user_id'        => $childUser->id,
                'parent_user_id' => $parentUser->id,
                'first_name'     => $registrationRequest->child_first_name,
                'last_name'      => $registrationRequest->child_last_name,
                'date_of_birth'  => $registrationRequest->child_date_of_birth,
                'gender'         => $registrationRequest->child_gender,
                // Not placed in a class yet — admin does that separately,
                // same as any other unplaced student (students.class_id is
                // nullable precisely for this).
                'class_id'       => null,
                'enrolment_date' => now(),
            ]);

            $registrationRequest->update([
                'status'                 => RegistrationRequest::STATUS_APPROVED,
                'reviewed_by'            => auth()->id(),
                'reviewed_at'            => now(),
                'created_parent_user_id' => $parentUser->id,
                'created_student_id'     => $student->student_id,
            ]);
        });

        $registrationRequest->refresh()->createdParentUser->notify(
            new RegistrationRequestReviewedNotification($registrationRequest, $childTemporary)
        );

        return redirect()->route('admin.registration-requests.index')
            ->with('notification', 'Registration approved — parent and student accounts created.');
    }

    public function reject(Request $request, RegistrationRequest $registrationRequest)
    {
        abort_unless($registrationRequest->isPending(), 422, 'This request has already been reviewed.');

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:500'],
        ]);

        $registrationRequest->update([
            'status'       => RegistrationRequest::STATUS_REJECTED,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        // No User was ever created for a rejected request, so route the
        // notification to the raw address instead of ->notify() on a model.
        Notification::route('mail', $registrationRequest->parent_email)
            ->notify(new RegistrationRequestReviewedNotification($registrationRequest));

        return redirect()->route('admin.registration-requests.index')
            ->with('notification', 'Registration rejected. The applicant has been notified.');
    }
}
