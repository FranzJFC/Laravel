@extends('layouts.membership')

@section('title', 'Overview')
@section('heading', 'Good to see you.')
@section('actions')
    <a class="button secondary" href="{{ route('transactions.create') }}">Record transaction</a>
    <a class="button" href="{{ route('members.create') }}">+ Add member</a>
@endsection

@section('content')
    <section class="stats" aria-label="Membership summary">
        <article class="stat"><span>Total members</span><strong>{{ number_format($totalMembers) }}</strong><small>All registered profiles</small></article>
        <article class="stat"><span>Active members</span><strong>{{ number_format($activeMembers) }}</strong><small>Currently in good standing</small></article>
        <article class="stat"><span>Expiring soon</span><strong>{{ number_format($expiringSoon) }}</strong><small>Within the next 30 days</small></article>
        <article class="stat accent"><span>Payments received</span><strong>₱{{ number_format($paymentsTotal, 2) }}</strong><small>Completed payment records</small></article>
    </section>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-head"><div><p class="kicker">People</p><h2>Newest members</h2></div><a href="{{ route('members.index') }}">View all</a></div>
            <div class="list">
                @forelse ($recentMembers as $member)
                    <a class="list-row" href="{{ route('members.show', $member) }}">
                        <span class="avatar">{{ strtoupper(substr($member->first_name, 0, 1).substr($member->last_name, 0, 1)) }}</span>
                        <span class="grow"><strong>{{ $member->full_name }}</strong><small>{{ $member->member_number }} · {{ $member->category->name }}</small></span>
                        <span class="badge">{{ $member->level->name }}</span>
                    </a>
                @empty
                    <div class="empty">No members yet. Add your first member to get started.</div>
                @endforelse
            </div>
        </section>
        <section class="panel">
            <div class="panel-head"><div><p class="kicker">Activity</p><h2>Recent transactions</h2></div><a href="{{ route('transactions.index') }}">View all</a></div>
            <div class="list">
                @forelse ($recentTransactions as $transaction)
                    <div class="list-row">
                        <span class="transaction-icon">{{ strtoupper(substr($transaction->type, 0, 1)) }}</span>
                        <span class="grow"><strong>{{ ucfirst($transaction->type) }} · {{ $transaction->member->full_name }}</strong><small>{{ $transaction->reference_number }} · {{ $transaction->transaction_date->format('M d, Y') }}</small></span>
                        <span class="amount">₱{{ number_format($transaction->amount, 2) }}</span>
                    </div>
                @empty
                    <div class="empty">No transactions have been recorded.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
