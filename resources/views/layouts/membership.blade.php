<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Membership management and transaction processing system">
    <title>@yield('title', 'Dashboard') | MemberFlow</title>
    <link rel="stylesheet" href="{{ asset('css/membership.css') }}">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('membership.dashboard') }}">
                <span class="brand-mark">M</span>
                <span><strong>MemberFlow</strong><small>Membership system</small></span>
            </a>
            <nav class="nav" aria-label="Membership navigation">
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
            </nav>
            <div class="sidebar-note"><span></span> System online</div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Membership administration</p>
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
</body>
</html>
