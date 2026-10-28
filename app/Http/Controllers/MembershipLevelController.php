<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MembershipLevelController extends Controller
{
    public function index()
    {
        return view('membership.levels', ['levels' => MembershipLevel::withCount('members')->orderBy('fee')->get()]);
    }

    public function store(Request $request)
    {
        MembershipLevel::create($this->validated($request));

        return back()->with('success', 'Membership level added.');
    }

    public function update(Request $request, MembershipLevel $level)
    {
        $level->update($this->validated($request, $level));

        return back()->with('success', 'Membership level updated.');
    }

    public function destroy(MembershipLevel $level)
    {
        if ($level->members()->exists()) {
            return back()->with('error', 'This level is in use. Mark it inactive instead.');
        }

        $level->delete();

        return back()->with('success', 'Membership level deleted.');
    }

    private function validated(Request $request, ?MembershipLevel $level = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_levels')->ignore($level)],
            'fee' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'validity_months' => ['required', 'integer', 'min:1', 'max:120'],
            'benefits' => ['nullable', 'string', 'max:2000'],
        ]);
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
