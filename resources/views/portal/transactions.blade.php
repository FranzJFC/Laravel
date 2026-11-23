@extends('layouts.membership')
@section('title', $payments ? 'My Payments' : 'My Transactions')
@section('heading', $payments ? 'My payments' : 'My transactions')
@section('content')
    <p class="section-copy">{{ $payments ? 'Payments recorded for your membership.' : 'Your application, renewal, and payment records.' }} These records are read-only. Contact an administrator for corrections.</p>
    <section class="panel">
        <div class="table-wrap"><table>
            <thead><tr><th>Reference</th><th>Type</th><th>Date</th><th>Amount</th><th>Payment method</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td><strong>{{ $transaction->reference_number }}</strong></td>
                    <td>{{ ucfirst($transaction->type) }}</td>
                    <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                    <td>&#8369;{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ $transaction->payment_method ? ucfirst(str_replace('_', ' ', $transaction->payment_method)) : 'Not recorded' }}</td>
                    <td><span class="status {{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">{{ $payments ? 'No payments have been recorded yet.' : 'No transactions have been recorded yet.' }}</div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="pagination">{{ $transactions->links() }}</div>
    </section>
@endsection
