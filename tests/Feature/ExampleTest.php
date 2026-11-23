<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('the welcome page is available', function () {
    $this->get(route('home'))->assertSuccessful();
});

test('the about page displays the course and module', function () {
    $this->get('/about')
        ->assertSuccessful()
        ->assertSee('Web System Technologies')
        ->assertSee('Laravel Foundations');
});

test('the task page displays its title and route parameter', function () {
    $this->get('/tasks/15')
        ->assertSuccessful()
        ->assertSee('Buy groceries')
        ->assertSee('Task ID: 15');
});

test('the tasks page displays the form and saved tasks', function () {
    Task::create([
        'task_title' => 'Review Laravel models',
        'description' => 'Check the model and migration connection.',
    ]);

    $this->get('/tasks')
        ->assertSuccessful()
        ->assertSee('Add a new task')
        ->assertSee('Review Laravel models')
        ->assertSee('Not completed');
});

test('a valid task can be saved', function () {
    $response = $this->from(route('tasks.index'))->post('/tasks', [
        'task_title' => 'Finish the database activity',
        'description' => 'Verify the saved record in the task list.',
    ]);

    $response
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('success', 'Task created successfully.');

    $this->assertDatabaseHas('tasks', [
        'task_title' => 'Finish the database activity',
        'completed' => false,
    ]);
});

test('a task title is required', function () {
    $this->post('/tasks', ['description' => 'Missing a title.'])
        ->assertSessionHasErrors('task_title');
});
