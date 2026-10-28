<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
</head>
<body>
    <div class="page">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">✓</span><span>Student Portal</span></a>
            <nav class="navigation" aria-label="Main navigation">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('tasks.index') }}" aria-current="page">Tasks</a>
                <a href="{{ route('membership.dashboard') }}">Membership</a>
                <a href="{{ route('about') }}">About</a>
            </nav>
        </header>
        <main class="narrow-page">
            <article class="surface content-card">
                <p class="section-kicker">Task detail</p>
                <h1 class="content-title">{{ $title }}</h1>
                <p class="intro-copy">Task ID: {{ $id }}</p>
                <div class="content-actions"><a class="portal-button" href="{{ route('tasks.index') }}">← Back to tasks</a></div>
            </article>
        </main>
    </div>
</body>
</html>
