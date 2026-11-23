@extends('layouts.membership')
@section('title', 'My Membership')
@section('heading', 'Welcome, '.auth()->user()->name.'.')
@section('content')
    <p class="section-copy">Your membership and records, all in one place. Contact an administrator if anything needs to be updated.</p>
    @if($application)
        <section class="panel membership-reference" aria-label="Email verification">
            <h2>{{ auth()->user()->hasVerifiedEmail() ? 'Email verified' : 'Verify your email address' }}</h2>
            @if(auth()->user()->hasVerifiedEmail())
                <p class="section-copy">Your email address has been confirmed.</p>
            @else
                <p class="section-copy">We sent you a verification link valid for 24 hours. Check your inbox and spam folder.</p>
                <div class="form-actions"><a class="text-link" href="{{ route('verification.notice') }}">Email verification details</a><form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button secondary" type="submit">Resend verification email</button></form></div>
            @endif
        </section>
    @endif
    @if($member)
        <section class="stats" aria-label="My membership">
            <article class="stat"><span>Member number</span><strong class="member-reference">{{ $member->member_number }}</strong><small>{{ $member->category?->name }}</small></article>
            <article class="stat"><span>Membership level</span><strong>{{ $member->level?->name }}</strong><small>{{ $member->level?->validity_months }} months</small></article>
            <article class="stat accent"><span>Membership status</span><strong>{{ ucfirst($member->status) }}</strong><small>{{ $member->expires_at ? 'Valid until '.$member->expires_at->format('M d, Y') : 'Awaiting administrator verification' }}</small></article>
            <article class="stat"><span>Subscription fee</span><strong>&#8369;{{ number_format($member->level?->fee ?? 0, 2) }}</strong><small>Your selected membership level</small></article>
        </section>
    @else
        <div class="alert danger">Your account is not linked to a membership profile. Please contact an administrator.</div>
    @endif
    @if($application)
        <section class="panel membership-reference"><h2>Application received</h2><p class="section-copy">Reference: <strong>{{ $application->reference_no }}</strong>. Your membership is {{ $member?->status ?? 'pending' }}.</p></section>
    @endif
    <div class="member-shortcuts">
        <a class="panel member-shortcut" href="{{ route('tasks.index') }}"><span class="kicker">Stay informed</span><h2>View tasks &rarr;</h2><p>Browse the tasks shared by your administrator.</p></a>
        <a class="panel member-shortcut" href="{{ route('member.transactions') }}"><span class="kicker">Your records</span><h2>My transactions &rarr;</h2><p>Review your membership applications, renewals, and payments.</p></a>
        <a class="panel member-shortcut" href="{{ route('member.payments') }}"><span class="kicker">Payment history</span><h2>My payments &rarr;</h2><p>Check payment amounts and their current status.</p></a>
    </div>
@endsection
