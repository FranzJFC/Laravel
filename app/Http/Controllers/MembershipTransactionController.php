<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MembershipTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = MembershipTransaction::with('member')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->string('search')->trim()->value();
                $q->where(fn ($q) => $q->where('reference_number', 'like', "%{$term}%")
                    ->orWhereHas('member', fn ($q) => $q->search($term)));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('transaction_date')->paginate(15)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    public function create(Request $request)
    {
        return view('transactions.create', [
            'members' => Member::with('level')->where('status', '!=', 'inactive')->orderBy('last_name')->get(),
            'selectedMember' => $request->integer('member'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'type' => ['required', Rule::in(['application', 'renewal', 'payment'])],
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_method' => ['nullable', Rule::in(['cash', 'card', 'bank_transfer', 'e_wallet'])],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated) {
            $validated['reference_number'] = 'TXN-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
            $transaction = MembershipTransaction::create($validated);
            $this->applyCompletedRenewal($transaction);
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded successfully.');
    }

    public function updateStatus(Request $request, MembershipTransaction $transaction)
    {
        $validated = $request->validate(['status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])]]);

        if ($transaction->status === 'completed') {
            return back()->with('error', 'Completed transactions are locked to protect the audit trail.');
        }

        DB::transaction(function () use ($transaction, $validated) {
            $transaction->update($validated);
            $this->applyCompletedRenewal($transaction);
        });

        return back()->with('success', 'Transaction status updated.');
    }

    private function applyCompletedRenewal(MembershipTransaction $transaction): void
    {
        if ($transaction->type !== 'renewal' || $transaction->status !== 'completed') {
            return;
        }

        $member = $transaction->member()->with('level')->lockForUpdate()->firstOrFail();
        $start = $member->expires_at && $member->expires_at->isFuture() ? $member->expires_at : today();
        $member->update([
            'expires_at' => $start->copy()->addMonths($member->level->validity_months),
            'status' => 'active',
        ]);
    }
}
