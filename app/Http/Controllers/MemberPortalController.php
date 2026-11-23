<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('portal.dashboard', [
            'member' => $request->user()->member()->with(['category', 'level'])->first(),
            'application' => MembershipApplication::where('user_id', $request->user()->id)->latest()->first(),
        ]);
    }

    public function transactions(Request $request): View
    {
        // Scope by the authenticated relationship; never accept a member ID from the browser.
        $memberId = $request->user()->member?->id;
        $payments = $request->routeIs('member.payments');
        $transactions = MembershipTransaction::query()
            ->where('member_id', $memberId ?? 0)
            ->when($payments, fn ($query) => $query->where('type', 'payment'))
            ->latest('transaction_date')->latest('id')->paginate(15);

        return view('portal.transactions', compact('transactions', 'payments'));
    }
}
