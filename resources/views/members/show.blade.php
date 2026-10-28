@extends('layouts.membership')
@section('title', $member->full_name)
@section('heading', $member->full_name)
@section('actions')<a class="button secondary" href="{{ route('transactions.create', ['member' => $member->id]) }}">Record transaction</a><a class="button" href="{{ route('members.edit', $member) }}">Edit profile</a>@endsection
@section('content')
    <div class="profile-grid">
        <section class="panel profile-card">
            <div class="profile-avatar">{{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}</div>
            <h2>{{ $member->full_name }}</h2><p>{{ $member->member_number }}</p><span class="status {{ $member->status }}">{{ ucfirst($member->status) }}</span>
            <dl class="details"><div><dt>Category</dt><dd>{{ $member->category->name }}</dd></div><div><dt>Level</dt><dd>{{ $member->level->name }}</dd></div><div><dt>Joined</dt><dd>{{ $member->joined_at->format('M d, Y') }}</dd></div><div><dt>Valid until</dt><dd>{{ $member->expires_at?->format('M d, Y') ?? 'Not set' }}</dd></div><div><dt>Email</dt><dd>{{ $member->email ?: 'Not provided' }}</dd></div><div><dt>Phone</dt><dd>{{ $member->phone ?: 'Not provided' }}</dd></div><div><dt>Organization</dt><dd>{{ $member->organization ?: 'Not provided' }}</dd></div><div><dt>Address</dt><dd>{{ $member->address ?: 'Not provided' }}</dd></div></dl>
            @if($member->status === 'inactive')<form method="POST" action="{{ route('members.activate', $member) }}">@csrf @method('PATCH')<button class="button full-button">Reactivate member</button></form>@else<form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Deactivate this member? Their records will be retained.')">@csrf @method('DELETE')<button class="button danger-button full-button">Deactivate member</button></form>@endif
        </section>
        <section class="panel"><div class="panel-head"><div><p class="kicker">History</p><h2>Transactions</h2></div></div><div class="table-wrap"><table><thead><tr><th>Reference</th><th>Type</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>@forelse($member->transactions as $transaction)<tr><td>{{ $transaction->reference_number }}</td><td>{{ ucfirst($transaction->type) }}</td><td>{{ $transaction->transaction_date->format('M d, Y') }}</td><td>₱{{ number_format($transaction->amount,2) }}</td><td><span class="status {{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span></td></tr>@empty<tr><td colspan="5"><div class="empty">No transactions recorded.</div></td></tr>@endforelse</tbody></table></div></section>
    </div>
@endsection
