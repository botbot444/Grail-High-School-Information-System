<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminParentController extends Controller
{
    public function index(Request $request)
    {
        $parents = ParentProfile::with(['user', 'students'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . $request->search . '%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($request->linkage === 'linked', fn ($query) => $query->has('students'))
            ->when($request->linkage === 'unlinked', fn ($query) => $query->doesntHave('students'))
            ->when($request->status === 'reset', function ($query) {
                $query->whereHas('user', fn ($u) => $u->where('must_change_password', true));
            })
            ->when($request->status === 'active', function ($query) {
                $query->whereHas('user', fn ($u) => $u->where('must_change_password', false)->where('is_active', true));
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        $totalGuardians = ParentProfile::count();
        $portalVerified = ParentProfile::whereHas('user', fn ($q) => $q->where('must_change_password', false))->count();
        $linkedCount = ParentProfile::has('students')->count();
        $unassignedCount = $totalGuardians - $linkedCount;

        return view('admin.parents.index', compact(
            'parents',
            'totalGuardians',
            'portalVerified',
            'linkedCount',
            'unassignedCount'
        ));
    }

    public function create()
    {
        // Provide list of students that can be linked to this parent
        $students = Student::whereNull('parent_user_id')->get();
        return view('admin.parents.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // A soft-deleted parent's email is free to reuse — see destroy().
            'email' => ['required', 'email', 'unique:users,email',
                Rule::unique('parents', 'email')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:50',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,student_id',
        ]);

        $roleId = Role::where('name', 'parent')->value('id');

        if (!$roleId) {
            return back()->withErrors('Parent role is not defined.');
        }

        try {
            // A random one-time password, not a fixed default — every parent
            // getting the same hardcoded password would mean anyone who's
            // ever seen this codebase knows every parent's starting login.
            // must_change_password forces them to set their own on first
            // login (enforced by the EnsurePasswordIsChanged middleware).
            $temporaryPassword = Str::password(12);

            $user = User::create([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role_id' => $roleId,
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]);

            ParentProfile::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
            ]);

            // Link selected students to this parent — saved one at a time
            // (not a bulk query-builder update) so Student's Auditable trait
            // actually fires and this shows up in the audit log.
            if (!empty($validated['student_ids'])) {
                Student::whereIn('student_id', $validated['student_ids'])->get()
                    ->each(function (Student $student) use ($user) {
                        $student->parent_user_id = $user->id;
                        $student->save();
                    });
            }

            return redirect()->route('admin.parents.index')
                ->with('notification', "Parent created successfully! Share the temporary password below with them — they'll be required to set a new one on first login.")
                ->with('temporary_password', $temporaryPassword);
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create parent: ' . $e->getMessage());
        }
    }

    public function show(ParentProfile $parent)
    {
        $parent->load(['user', 'students.schoolClass']);

        return view('admin.parents.show', compact('parent'));
    }

    public function edit(ParentProfile $parent)
    {
        $students = Student::all();
        // students currently linked to this parent
        $linked = Student::where('parent_user_id', $parent->user_id)->pluck('student_id')->toArray();
        return view('admin.parents.edit', compact('parent', 'students', 'linked'));
    }

    public function update(Request $request, ParentProfile $parent)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email,' . $parent->user_id,
                Rule::unique('parents', 'email')->ignore($parent->parent_id, 'parent_id')->whereNull('deleted_at')],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:50',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,student_id',
        ]);

        try {
            $parent->user->update([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
            ]);

            $parent->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'national_id' => $validated['national_id'] ?? null,
            ]);

            // Update linked students: clear previous links not in this set.
            // Saved one at a time (not a bulk query-builder update) so
            // Student's Auditable trait actually fires for each change.
            $new = $validated['student_ids'] ?? [];

            Student::where('parent_user_id', $parent->user_id)
                ->whereNotIn('student_id', $new)
                ->get()
                ->each(function (Student $student) {
                    $student->parent_user_id = null;
                    $student->save();
                });

            if (!empty($new)) {
                Student::whereIn('student_id', $new)->get()
                    ->each(function (Student $student) use ($parent) {
                        $student->parent_user_id = $parent->user_id;
                        $student->save();
                    });
            }

            return redirect()->route('admin.parents.show', $parent)
                ->with('notification', 'Parent updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update parent: ' . $e->getMessage());
        }
    }

    public function destroy(ParentProfile $parent)
    {
        // Soft-deleting a parent who still has linked children leaves those
        // students pointed at a now-invisible guardian instead of clearing
        // the link — same shape of bug as deleting a class with students
        // still enrolled in it.
        if ($parent->students()->exists()) {
            return back()->withErrors('Cannot delete a parent with children still linked. Unlink or reassign their children first.');
        }

        try {
            $parent->delete();
            $parent->user?->delete();

            return redirect()->route('admin.parents.index')
                ->with('notification', 'Parent deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete parent: ' . $e->getMessage());
        }
    }
}
