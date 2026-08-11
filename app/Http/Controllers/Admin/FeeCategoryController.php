<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'name' => ['required', 'string', 'max:255', 'unique:fee_categories,name'],
        ]);

        $category = FeeCategory::create($validated);

        return back()->with('notification', 'Category created: ' . $category->name);
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:fee_categories,name,' . $feeCategory->id],
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
