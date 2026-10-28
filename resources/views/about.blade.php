<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
</head>
<body>
    <div class="page">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">i</span><span>Student Portal</span></a>
            <nav class="navigation" aria-label="Main navigation">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('tasks.index') }}">Tasks</a>
                <a href="{{ route('membership.dashboard') }}">Membership</a>
                <a href="{{ route('about') }}" aria-current="page">About</a>
            </nav>
        </header>
        <main class="narrow-page">
            <section class="surface content-card">
                <p class="section-kicker">About this project</p>
                <h1 class="content-title">{{ $course }}</h1>
                <p class="intro-copy">{{ $module }}</p>
                <p class="content-copy">This connected Laravel portal combines personal task tracking with membership registration, configuration, renewals, and payment processing.</p>
                <div class="content-actions"><a class="portal-button" href="{{ route('tasks.index') }}">View tasks</a><a class="portal-button secondary-button" href="{{ route('membership.dashboard') }}">Manage memberships</a></div>
            </section>
        </main>
    </div>
</body>
</html>
