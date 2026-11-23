@extends('layouts.membership')
@section('title', 'Tasks')
@section('heading', 'Tasks')
@section('content')
    <p class="section-copy">{{ auth()->user()->isAdmin() ? 'Create tasks and share updates with your members.' : 'View the tasks shared by your administrator. These tasks are read-only.' }}</p>
    <div class="tasks-layout {{ auth()->user()->isAdmin() ? '' : 'tasks-readonly' }}">
        @if(auth()->user()->isAdmin())
                <section class="panel" aria-labelledby="add-task-heading">
                    <p class="kicker">New entry</p>
                    <h2 class="" id="add-task-heading">Add a new task</h2>
                    <p class="section-copy">Write down what matters now. You can always return to it later.</p>

                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf

                        <div class="field">
                            <label class="" for="task_title">
                                <span>Task title</span>
                                <span class="subtext">Required</span>
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
                            <label class="" for="description">
                                <span>Description</span>
                                <span class="subtext">Optional</span>
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

                        <button class="button" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14"></path>
                            </svg>
                            <span>Add Task</span>
                        </button>
                    </form>
                </section>

        @endif
                <section class="panel" aria-labelledby="saved-tasks-heading">
                    <header class="panel-head">
                        <div>
                            <p class="kicker">Your list</p>
                            <h2 class="" id="saved-tasks-heading">Saved Tasks</h2>
                            <p class="section-copy">Small steps, collected in one calm space.</p>
                        </div>
                        <span class="badge">{{ $tasks->count() }} {{ Str::plural('task', $tasks->count()) }}</span>
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
                                    <p>{{ auth()->user()->isAdmin() ? 'Add your first task to get started.' : 'Tasks shared by your administrator will appear here.' }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </section>
    </div>
@endsection
