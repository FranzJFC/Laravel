@extends('layouts.membership')
@section('title', 'Record Transaction')
@section('heading', 'Record a transaction')
@section('content')
    <section class="panel form-panel"><p class="section-copy">Log an application, renewal, or payment. Completing a renewal automatically extends the member's validity using their assigned level.</p><form method="POST" action="{{ route('transactions.store') }}">@csrf<div class="form-grid">
        <div class="field full"><label for="member_id">Member *</label><select id="member_id" name="member_id" required><option value="">Choose a member</option>@foreach($members as $member)<option value="{{ $member->id }}" @selected(old('member_id',$selectedMember)==$member->id)>{{ $member->last_name }}, {{ $member->first_name }} — {{ $member->member_number }} ({{ $member->level->name }})</option>@endforeach</select>@error('member_id')<small class="error">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="type">Type *</label><select id="type" name="type" required>@foreach(['application','renewal','payment'] as $type)<option value="{{ $type }}" @selected(old('type')===$type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
        <div class="field"><label for="amount">Amount (₱) *</label><input type="number" min="0" step="0.01" id="amount" name="amount" value="{{ old('amount',0) }}" required>@error('amount')<small class="error">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="payment_method">Payment method</label><select id="payment_method" name="payment_method"><option value="">Not specified</option>@foreach(['cash'=>'Cash','card'=>'Card','bank_transfer'=>'Bank transfer','e_wallet'=>'E-wallet'] as $value=>$label)<option value="{{ $value }}" @selected(old('payment_method')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="status">Status *</label><select id="status" name="status" required>@foreach(['pending','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status','completed')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="field"><label for="transaction_date">Transaction date *</label><input type="date" id="transaction_date" name="transaction_date" value="{{ old('transaction_date',now()->format('Y-m-d')) }}" required></div>
        <div class="field full"><label for="notes">Notes</label><textarea id="notes" name="notes" rows="4">{{ old('notes') }}</textarea></div>
    </div><div class="form-actions"><a class="button secondary" href="{{ route('transactions.index') }}">Cancel</a><button class="button">Save transaction</button></div></form></section>
@endsection
