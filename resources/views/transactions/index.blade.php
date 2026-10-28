@extends('layouts.membership')
@section('title', 'Transactions')
@section('heading', 'Transactions')
@section('actions')<a class="button" href="{{ route('transactions.create') }}">+ Record transaction</a>@endsection
@section('content')
    <section class="panel"><form class="filters" method="GET"><input name="search" value="{{ request('search') }}" placeholder="Search reference or member…"><select name="type"><option value="">All types</option>@foreach(['application','renewal','payment'] as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ ucfirst($type) }}</option>@endforeach</select><select name="status"><option value="">All statuses</option>@foreach(['pending','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="button">Filter</button></form>
        <div class="table-wrap"><table><thead><tr><th>Reference</th><th>Member</th><th>Type</th><th>Date</th><th>Amount</th><th>Status / action</th></tr></thead><tbody>
        @forelse($transactions as $transaction)<tr><td><strong>{{ $transaction->reference_number }}</strong><small class="subtext">{{ $transaction->payment_method ? str_replace('_',' ',ucfirst($transaction->payment_method)) : 'No payment method' }}</small></td><td><a class="text-link" href="{{ route('members.show',$transaction->member) }}">{{ $transaction->member->full_name }}</a></td><td>{{ ucfirst($transaction->type) }}</td><td>{{ $transaction->transaction_date->format('M d, Y') }}</td><td>₱{{ number_format($transaction->amount,2) }}</td><td><form class="status-form" method="POST" action="{{ route('transactions.status',$transaction) }}">@csrf @method('PATCH')<select name="status" aria-label="Transaction status">@foreach(['pending','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($transaction->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="text-button">Save</button></form></td></tr>
        @empty<tr><td colspan="6"><div class="empty">No transactions match your search.</div></td></tr>@endforelse
        </tbody></table></div><div class="pagination">{{ $transactions->links() }}</div>
    </section>
@endsection
