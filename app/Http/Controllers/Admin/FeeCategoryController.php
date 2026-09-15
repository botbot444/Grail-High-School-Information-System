<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FeeCategoryController extends Controller
{
    public function index()
    {
        $categories = FeeCategory::withCount('feeItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.settings.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // A soft-deleted category's name is free to reuse — see destroy().
            'name' => ['required', 'string', 'max:255', Rule::unique('fee_categories', 'name')->whereNull('deleted_at')],
        ]);

        $category = FeeCategory::create($validated);

        return back()->with('notification', 'Category created: ' . $category->name);
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',
                Rule::unique('fee_categories', 'name')->ignore($feeCategory->id)->whereNull('deleted_at')],
        ]);

        $feeCategory->update($validated);

        return back()->with('notification', 'Category updated.');
    }

    public function destroy(FeeCategory $feeCategory)
    {
        // Only allow soft-delete when no fee items reference this category.
        if ($feeCategory->feeItems()->exists()) {
            return back()->withErrors('Cannot delete a category that is already in use.');
        }

        $feeCategory->delete();

        return back()->with('notification', 'Category deleted.');
    }
}
