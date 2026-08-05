<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminParentController extends Controller
{
    public function index()
    {
        $parents = ParentProfile::with('user')->paginate(20);

        return view('admin.parents.index', compact('parents'));
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
            'email' => 'required|email|unique:users,email|unique:parents,email',
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
            $user = User::create([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
                'password' => Hash::make('Parent@1234'),
                'role_id' => $roleId,
                'email_verified_at' => now(),
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

            // Link selected students to this parent
            if (!empty($validated['student_ids'])) {
                Student::whereIn('student_id', $validated['student_ids'])
                    ->update(['parent_user_id' => $user->id]);
            }

            return redirect()->route('admin.parents.index')
                ->with('notification', 'Parent created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create parent: ' . $e->getMessage());
        }
    }

    public function show(ParentProfile $parent)
    {
        $parent->load('user');

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
            'email' => 'required|email|unique:users,email,' . $parent->user_id . '|unique:parents,email,' . $parent->parent_id . ',parent_id',
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

            // Update linked students: clear previous links not in this set
            $new = $validated['student_ids'] ?? [];
            Student::where('parent_user_id', $parent->user_id)
                ->whereNotIn('student_id', $new)
                ->update(['parent_user_id' => null]);
            if (!empty($new)) {
                Student::whereIn('student_id', $new)->update(['parent_user_id' => $parent->user_id]);
            }

            return redirect()->route('admin.parents.show', $parent)
                ->with('notification', 'Parent updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update parent: ' . $e->getMessage());
        }
    }

    public function destroy(ParentProfile $parent)
    {
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
