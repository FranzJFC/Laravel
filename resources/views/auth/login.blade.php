@extends('layouts.auth')
@section('title', $role === 'admin' ? 'Admin Login' : 'Member Login')
@section('main-class', 'auth-narrow')
@section('eyebrow', $role === 'admin' ? 'Administration' : 'Your member portal')
@section('heading', $role === 'admin' ? 'Admin login' : 'Welcome back, member.')
@section('intro', $role === 'admin' ? 'Sign in to manage your membership community.' : 'Sign in to view tasks, transactions, and payments.')
@section('content')
    <section class="panel auth-login">
        <form method="POST" action="{{ route($role.'.login.store') }}">
            @csrf
            <div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus @error('email') aria-invalid="true" @enderror></div>
            <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
            <button type="submit" class="button full-button">Sign in as {{ $role === 'admin' ? 'admin' : 'member' }} &rarr;</button>
        </form>
        @if($role === 'member')
            <p class="auth-switch">Don't have an account? <a href="{{ route('register') }}">Sign up and choose a membership</a></p>
        @else
            <p class="auth-switch">Looking for your membership? <a href="{{ route('member.login') }}">Use member login</a></p>
        @endif
    </section>
@endsection
