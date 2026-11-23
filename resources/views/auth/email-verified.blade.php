@extends('layouts.auth')
@section('title', 'Email Verified')
@section('main-class', 'auth-narrow')
@section('eyebrow', 'Email confirmed')
@section('heading', 'Your email is verified.')
@section('intro', 'Thank you for confirming your membership email address.')
@section('content')
    <section class="panel">
        <p class="section-copy">You can sign in to view your membership, tasks, transactions, and payments. Your membership still requires separate administrator approval.</p>
        <a class="button full-button" href="{{ route('member.login') }}">Continue to member login &rarr;</a>
    </section>
@endsection
