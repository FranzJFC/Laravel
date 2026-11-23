<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Membership management and transaction processing system">
    <title>@yield('title', 'Dashboard') | MemberFlow</title>
    <link rel="stylesheet" href="{{ asset('css/membership.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route(auth()->user()->isAdmin() ? 'membership.dashboard' : 'member.dashboard') }}">
                <span class="brand-mark">M</span>
                <span><strong>MemberFlow</strong><small>Membership system</small></span>
            </a>
            <nav class="nav" aria-label="Membership navigation">
                @if(auth()->user()->isAdmin())
                <a class="{{ request()->routeIs('membership.dashboard') ? 'active' : '' }}" href="{{ route('membership.dashboard') }}">Overview</a>
                <a class="{{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}">Members</a>
                <a class="{{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">Transactions</a>
                <span class="nav-label">Configuration</span>
                <a class="{{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Categories</a>
                <a class="{{ request()->routeIs('levels.*') ? 'active' : '' }}" href="{{ route('levels.index') }}">Membership levels</a>
                <span class="nav-label">Student portal</span>
                <a href="{{ route('home') }}">Portal home</a>
                <a href="{{ route('tasks.index') }}">Tasks</a>
                <a href="{{ route('about') }}">About</a>
                @else
                <a class="{{ request()->routeIs('member.dashboard') ? 'active' : '' }}" href="{{ route('member.dashboard') }}">My membership</a>
                <a class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">Tasks</a>
                <a class="{{ request()->routeIs('member.transactions') ? 'active' : '' }}" href="{{ route('member.transactions') }}">My transactions</a>
                <a class="{{ request()->routeIs('member.payments') ? 'active' : '' }}" href="{{ route('member.payments') }}">My payments</a>
                @endif
            </nav>
            <div class="sidebar-account">
                <span class="account-role">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Member account' }}</span>
                <strong>{{ auth()->user()->name }}</strong>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">Sign out &rarr;</button></form>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">@yield('eyebrow', auth()->user()->isAdmin() ? 'Membership administration' : 'Member portal')</p>
                    <h1>@yield('heading', 'Overview')</h1>
                </div>
                <div class="top-actions">@yield('actions')</div>
            </header>

            @if (session('success'))
                <div class="alert success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert danger" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert danger" role="alert">Please correct the highlighted fields.</div>
            @endif

            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
