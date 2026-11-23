@extends('layouts.membership')
@section('title', 'Verify Email')
@section('heading', 'Check your inbox.')
@section('content')
    <section class="panel form-panel">
        <h2>Verify your membership email</h2>
        <p class="section-copy" style="margin-top: 16px">We sent a verification email to <strong>{{ auth()->user()->email }}</strong>. Click <strong>Verify email address</strong> in that message. The link expires in 24 hours.</p>
        <p class="section-copy">Check your spam folder too. Email verification confirms your address; membership approval is handled separately by an administrator.</p>
        @if($application)
            <p class="section-copy">Application reference: <strong>{{ $application->reference_no }}</strong></p>
            <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button" type="submit">Resend verification email</button></form>
        @else
            <p class="section-copy">No matching signup application is linked to this email. Please contact an administrator.</p>
        @endif
    </section>
@endsection
