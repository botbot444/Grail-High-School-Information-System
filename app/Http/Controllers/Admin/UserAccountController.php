<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Phase 12 — account management.
 *
 * Three guards run through everything here, because every action on this screen
 * can lock someone out of the system:
 *   • an admin cannot act on their own account
 *   • the last active admin cannot be deactivated or demoted
 *   • changing a role warns about the records already attached to the old one
 */
class UserAccountController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('role')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->integer('role_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users'       => $users,
            'roles'       => Role::orderBy('name')->get(),
            'activeAdmins'=> $this->activeAdminCount(),
            'counts'      => [
                'total'    => User::count(),
                'active'   => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    /** Activate or deactivate an account. */
    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($error = $this->guardSelf($user, 'change the status of')) {
            return back()->withErrors(['user' => $error]);
        }

        // Turning the last admin off would leave nobody able to turn it back on.
        if ($user->is_active && $this->isLastActiveAdmin($user)) {
            return back()->withErrors([
                'user' => 'This is the only active administrator — deactivating it would lock everyone out of the admin portal.',
            ]);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with(
            'notification',
            $user->is_active
                ? "{$user->name} can sign in again."
                : "{$user->name} has been deactivated and signed out."
        );
    }

    /**
     * Issue a temporary password.
     *
     * Shown once, in the flash message, for the admin to pass on in person or by
     * phone — the school cannot rely on every parent having a working email.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        if ($error = $this->guardSelf($user, 'reset the password for')) {
            return back()->withErrors(['user' => $error]);
        }

        // Ambiguous characters left out so it can be read aloud without confusion.
        $temporary = Str::upper(Str::random(3)) . '-' . random_int(1000, 9999) . '-' . Str::upper(Str::random(3));
        $temporary = str_replace(['I', 'O', 'L'], ['X', 'Y', 'Z'], $temporary);

        $user->password = Hash::make($temporary);
        $user->save();

        return back()
            ->with('notification', "Temporary password for {$user->name}: {$temporary}")
            ->with('temporary_password_for', $user->id);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', Rule::exists('roles', 'id')],
        ]);

        if ($error = $this->guardSelf($user, 'change the role of')) {
            return back()->withErrors(['user' => $error]);
        }

        $role = Role::findOrFail((int) $validated['role_id']);

        if ($user->role_id === $role->id) {
            return back()->with('notification', "{$user->name} already has the {$role->name} role.");
        }

        if ($this->isLastActiveAdmin($user) && $role->name !== 'admin') {
            return back()->withErrors([
                'user' => 'This is the only active administrator — change someone else to admin first.',
            ]);
        }

        $attached = $this->attachedRecords($user);

        $user->role_id = $role->id;
        $user->role = $role->name; // keep the legacy string column in step
        $user->save();

        $message = "{$user->name} is now a {$role->name}.";

        if ($attached !== []) {
            $message .= ' Note: ' . implode(', ', $attached) . ' still linked to this account.';
        }

        return back()->with('notification', $message);
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    private function guardSelf(User $user, string $action): ?string
    {
        return $user->id === auth()->id()
            ? "You cannot {$action} your own account — ask another administrator."
            : null;
    }

    private function activeAdminCount(): int
    {
        return User::where('is_active', true)
            ->whereHas('role', fn ($q) => $q->where('name', 'admin'))
            ->count();
    }

    private function isLastActiveAdmin(User $user): bool
    {
        return $user->hasRole('admin')
            && $user->is_active
            && $this->activeAdminCount() <= 1;
    }

    /**
     * What a role change would leave dangling — shown as a warning, not a block,
     * because the admin may well be correcting a mis-assigned account.
     *
     * @return array<int, string>
     */
    private function attachedRecords(User $user): array
    {
        $notes = [];

        if ($user->teacher) {
            $classes = SchoolClass::where('teacher_id', $user->teacher->teacher_id)->count();
            $subjects = ClassSubject::where('teacher_id', $user->teacher->teacher_id)->count();

            if ($classes) {
                $notes[] = "{$classes} homeroom class(es)";
            }
            if ($subjects) {
                $notes[] = "{$subjects} subject assignment(s)";
            }
        }

        $children = Student::where('parent_user_id', $user->id)->count();
        if ($children) {
            $notes[] = "{$children} linked child(ren)";
        }

        if (Student::where('user_id', $user->id)->exists()) {
            $notes[] = 'a student record';
        }

        return $notes;
    }
}
