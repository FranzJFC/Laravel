@extends('layouts.auth')
@section('title', 'Verification Link Unavailable')
@section('main-class', 'auth-narrow')
@section('eyebrow', 'Email verification')
@section('heading', 'This link is unavailable.')
@section('intro', 'The verification link is invalid or has expired. Links are valid for 24 hours.')
@section('content')
    <section class="panel">
        <p class="section-copy">Sign in to your member account and request a new verification email from your membership page.</p>
        <a class="button full-button" href="{{ auth()->user()?->role === 'member' ? route('verification.notice') : route('member.login') }}">Request a new link &rarr;</a>
    </section>
@endsection
