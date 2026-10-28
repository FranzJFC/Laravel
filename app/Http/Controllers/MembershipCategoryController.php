<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MembershipCategoryController extends Controller
{
    public function index()
    {
        return view('membership.categories', ['categories' => MembershipCategory::withCount('members')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:membership_categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        MembershipCategory::create($validated);

        return back()->with('success', 'Membership category added.');
    }

    public function update(Request $request, MembershipCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $category->update($validated);

        return back()->with('success', 'Membership category updated.');
    }

    public function destroy(MembershipCategory $category)
    {
        if ($category->members()->exists()) {
            return back()->with('error', 'This category is in use. Mark it inactive instead.');
        }

        $category->delete();

        return back()->with('success', 'Membership category deleted.');
    }
}
