<nav class="navigation" aria-label="Main navigation">
    <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
    <a href="{{ route('tasks.index') }}" @if(request()->routeIs('tasks.*')) aria-current="page" @endif>Tasks</a>
    <a href="{{ route('membership.dashboard') }}">Membership</a>
    <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
</nav>
