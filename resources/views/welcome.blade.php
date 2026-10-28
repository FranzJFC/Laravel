<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Portal for tasks and membership administration.">
    <title>Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
</head>
<body>
    <div class="page">
        <header class="site-header">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5 9.25 17 19 7"></path></svg>
                </span>
                <span>Student Portal</span>
            </a>
            <nav class="navigation" aria-label="Main navigation">
                <a href="{{ route('home') }}" aria-current="page">Home</a>
                <a href="{{ route('tasks.index') }}">Tasks</a>
                <a href="{{ route('membership.dashboard') }}">Membership</a>
                <a href="{{ route('about') }}">About</a>
            </nav>
        </header>

        <main>
            <section class="intro portal-intro" aria-labelledby="page-title">
                <p class="eyebrow"><span class="eyebrow-dot"></span> One connected workspace</p>
                <h1 id="page-title">Your portal, <span>all in one place.</span></h1>
                <p class="intro-copy">Plan everyday work, manage member records, and process membership transactions from a single connected portal.</p>
            </section>

            <section class="portal-grid" aria-label="Portal sections">
                <a class="surface portal-card" href="{{ route('tasks.index') }}">
                    <span class="portal-icon" aria-hidden="true">✓</span>
                    <p class="section-kicker">Productivity</p>
                    <h2 class="section-heading">Task management</h2>
                    <p class="section-copy">Create and review your task list in a calm, focused workspace.</p>
                    <span class="portal-link">Open tasks →</span>
                </a>
                <a class="surface portal-card" href="{{ route('membership.dashboard') }}">
                    <span class="portal-icon membership-icon" aria-hidden="true">M</span>
                    <p class="section-kicker">Administration</p>
                    <h2 class="section-heading">Membership management</h2>
                    <p class="section-copy">Manage members, categories, levels, renewals, and payments.</p>
                    <span class="portal-link">Open membership →</span>
                </a>
            </section>
        </main>

        <footer class="page-footer">Student Portal · Tasks and membership, connected</footer>
    </div>
</body>
</html>
