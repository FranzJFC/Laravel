<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Welcome') | MemberFlow</title>
    <link rel="stylesheet" href="{{ asset('css/membership.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body class="auth-page">
    <header class="auth-header">
        <a class="brand" href="{{ route('login') }}"><span class="brand-mark">M</span><span><strong>MemberFlow</strong><small>Your membership, connected</small></span></a>
        <nav aria-label="Account navigation">
            <a href="{{ route('member.login') }}" @if(request()->routeIs('member.login')) aria-current="page" @endif>Member login</a>
            <a href="{{ route('admin.login') }}" @if(request()->routeIs('admin.login')) aria-current="page" @endif>Admin login</a>
            <a class="button" href="{{ route('register') }}">Sign up</a>
        </nav>
    </header>
    <main class="auth-main @yield('main-class')">
        <header class="auth-intro">
            <p class="eyebrow">@yield('eyebrow', 'Welcome to MemberFlow')</p>
            <h1>@yield('heading', 'A place for every member.')</h1>
            <p>@yield('intro', 'Choose how you would like to continue.')</p>
        </header>
        @if(session('success')) <div class="alert success" role="status">{{ session('success') }}</div> @endif
        @if($errors->any())
            <div class="alert danger" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
    <footer class="auth-footer">MemberFlow &middot; Your membership, connected.</footer>
    @stack('scripts')
</body>
</html>
