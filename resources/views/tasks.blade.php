<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A calm and simple space for managing your tasks.">
    <title>My Tasks | Student Portal</title>
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
</head>
<body>
    <div class="page">
        <header class="site-header">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12.5 9.25 17 19 7"></path>
                    </svg>
                </span>
                <span>Student Portal</span>
            </a>

            <nav class="navigation" aria-label="Main navigation">
                <a href="{{ url('/about') }}">About</a>
                <a href="{{ route('tasks.index') }}" aria-current="page">Tasks</a>
            </nav>
        </header>

        <main>
            <section class="intro" aria-labelledby="page-title">
                <p class="eyebrow"><span class="eyebrow-dot"></span> A quiet place to focus</p>
                <h1 id="page-title">My Tasks, <span>made simple.</span></h1>
                <p class="intro-copy">Keep your plans clear, your thoughts organized, and your next step easy to see.</p>
            </section>

            @if (session('success'))
                <p class="flash-message" role="status">
                    <span class="flash-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7 12 3 3 7-7"></path>
                        </svg>
                    </span>
                    {{ session('success') }}
                </p>
            @endif

            <div class="workspace">
                <section class="surface composer" aria-labelledby="add-task-heading">
                    <p class="section-kicker">New entry</p>
                    <h2 class="section-heading" id="add-task-heading">Add a new task</h2>
                    <p class="section-copy">Write down what matters now. You can always return to it later.</p>

                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf

                        <div class="field">
                            <label class="field-label" for="task_title">
                                <span>Task title</span>
                                <span class="field-hint">Required</span>
                            </label>
                            <input
                                class="@error('task_title') is-invalid @enderror"
                                type="text"
                                id="task_title"
                                name="task_title"
                                value="{{ old('task_title') }}"
                                placeholder="What would you like to do?"
                                maxlength="255"
                                autocomplete="off"
                                @error('task_title') aria-invalid="true" aria-describedby="task-title-error" @enderror
                                required
                                autofocus
                            >
                            @error('task_title')
                                <p class="error-message" id="task-title-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="field-label" for="description">
                                <span>Description</span>
                                <span class="field-hint">Optional</span>
                            </label>
                            <textarea
                                class="@error('description') is-invalid @enderror"
                                id="description"
                                name="description"
                                placeholder="Add a few helpful details..."
                                @error('description') aria-invalid="true" aria-describedby="description-error" @enderror
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="error-message" id="description-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button class="submit-button" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14"></path>
                            </svg>
                            <span>Add Task</span>
                        </button>
                    </form>
                </section>

                <section class="surface task-board" aria-labelledby="saved-tasks-heading">
                    <header class="board-header">
                        <div>
                            <p class="section-kicker">Your list</p>
                            <h2 class="section-heading" id="saved-tasks-heading">Saved Tasks</h2>
                            <p class="section-copy">Small steps, collected in one calm space.</p>
                        </div>
                        <span class="task-count">{{ $tasks->count() }} {{ Str::plural('task', $tasks->count()) }}</span>
                    </header>

                    <div class="task-list">
                        @forelse ($tasks as $task)
                            <article class="task-card">
                                <span class="task-number" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="task-content">
                                    <h3 class="task-title">{{ $task->task_title }}</h3>
                                    <p class="task-description">{{ $task->description ?: 'No description provided.' }}</p>
                                    <p class="task-meta">Added {{ $task->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="status {{ $task->completed ? 'completed' : '' }}">
                                    <span class="status-dot"></span>
                                    {{ $task->completed ? 'Completed' : 'Not completed' }}
                                </span>
                            </article>
                        @empty
                            <div class="empty-state">
                                <div>
                                    <span class="empty-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 11.5 11 13.5 15.5 9"></path>
                                            <path d="M6.5 3.5h11a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z"></path>
                                        </svg>
                                    </span>
                                    <h3>No tasks have been added yet.</h3>
                                    <p>Add your first task and it will appear here, ready when you are.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </main>

        <footer class="page-footer">Student Portal · A simple space for thoughtful progress</footer>
    </div>
</body>
</html>
