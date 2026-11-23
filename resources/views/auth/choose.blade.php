@extends('layouts.auth')
@section('title', 'Welcome')
@section('content')
    <div class="auth-choices">
        <section class="panel auth-choice">
            <span class="application-step" aria-hidden="true">M</span>
            <p class="kicker">For members</p><h2>Your member portal</h2>
            <p>View tasks, follow your transactions, and check your payment history in one place.</p>
            <a class="button full-button" href="{{ route('member.login') }}">Member login &rarr;</a>
            <p class="auth-switch">New here? <a href="{{ route('register') }}">Sign up for membership</a></p>
        </section>
        <section class="panel auth-choice">
            <span class="application-step admin-mark" aria-hidden="true">A</span>
            <p class="kicker">For administrators</p><h2>Manage your community</h2>
            <p>Manage members, membership categories and levels, tasks, transactions, and payments.</p>
            <a class="button secondary full-button" href="{{ route('admin.login') }}">Admin login &rarr;</a>
            <p class="auth-switch">For authorized administrators.</p>
        </section>
    </div>
@endsection
