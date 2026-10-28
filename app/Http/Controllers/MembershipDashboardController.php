<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipTransaction;

class MembershipDashboardController extends Controller
{
    public function index()
    {
        return view('membership.dashboard', [
            'totalMembers' => Member::count(),
            'activeMembers' => Member::where('status', 'active')->count(),
            'expiringSoon' => Member::where('status', 'active')
                ->whereBetween('expires_at', [today(), today()->addDays(30)])->count(),
            'paymentsTotal' => MembershipTransaction::where('type', 'payment')
                ->where('status', 'completed')->sum('amount'),
            'recentMembers' => Member::with(['category', 'level'])->latest()->take(5)->get(),
            'recentTransactions' => MembershipTransaction::with('member')->latest('transaction_date')->take(5)->get(),
        ]);
    }
}
