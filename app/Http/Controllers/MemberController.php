<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::with(['category', 'level'])
            ->search($request->string('search')->trim()->value())
            ->when($request->filled('category'), fn ($q) => $q->where('membership_category_id', $request->integer('category')))
            ->when($request->filled('level'), fn ($q) => $q->where('membership_level_id', $request->integer('level')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()->paginate(12)->withQueryString();

        return view('members.index', [
            'members' => $members,
            'categories' => MembershipCategory::orderBy('name')->get(),
            'levels' => MembershipLevel::orderBy('fee')->get(),
        ]);
    }

    public function create()
    {
        return view('members.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $this->validateMember($request);

        $member = DB::transaction(function () use ($validated) {
            $level = MembershipLevel::findOrFail($validated['membership_level_id']);
            $validated['member_number'] = $this->nextMemberNumber();
            $validated['expires_at'] = Carbon::parse($validated['joined_at'])->addMonths($level->validity_months);
            $member = Member::create($validated);

            MembershipTransaction::create([
                'reference_number' => $this->nextReferenceNumber(),
                'member_id' => $member->id,
                'type' => 'application',
                'amount' => $level->fee,
                'status' => 'completed',
                'transaction_date' => $member->joined_at,
                'notes' => 'Initial membership application',
            ]);

            return $member;
        });

        return redirect()->route('members.show', $member)->with('success', 'Member registered successfully.');
    }

    public function show(Member $member)
    {
        $member->load(['category', 'level', 'transactions' => fn ($q) => $q->latest('transaction_date')]);

        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('members.edit', ['member' => $member, ...$this->formOptions()]);
    }

    public function update(Request $request, Member $member)
    {
        $validated = $this->validateMember($request, $member);
        $member->update($validated);

        return redirect()->route('members.show', $member)->with('success', 'Member details updated.');
    }

    public function destroy(Member $member)
    {
        $member->update(['status' => 'inactive']);

        return redirect()->route('members.index')->with('success', 'Member deactivated. Their records were retained.');
    }

    public function forceDelete(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member and transaction history permanently deleted.');
    }

    public function activate(Member $member)
    {
        $member->update(['status' => 'active']);

        return back()->with('success', 'Member reactivated.');
    }

    private function validateMember(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('members')->ignore($member)],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'organization' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'joined_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
        ]);
    }

    private function formOptions(): array
    {
        return [
            'categories' => MembershipCategory::where('is_active', true)->orderBy('name')->get(),
            'levels' => MembershipLevel::where('is_active', true)->orderBy('fee')->get(),
        ];
    }

    private function nextMemberNumber(): string
    {
        do {
            $number = 'MEM-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Member::where('member_number', $number)->exists());

        return $number;
    }

    private function nextReferenceNumber(): string
    {
        return 'TXN-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
    }
}
