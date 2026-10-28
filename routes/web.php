<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipCategoryController;
use App\Http\Controllers\MembershipDashboardController;
use App\Http\Controllers\MembershipLevelController;
use App\Http\Controllers\MembershipTransactionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/about', [PageController::class, 'about'])->name('about');

Route::get('/tasks', [TaskController::class, 'index'])
    ->name('tasks.index');

Route::post('/tasks', [TaskController::class, 'store'])
    ->name('tasks.store');

Route::get('/tasks/{id}', [PageController::class, 'task'])->name('tasks.show');

Route::prefix('membership')->group(function () {
    Route::get('/', [MembershipDashboardController::class, 'index'])->name('membership.dashboard');
    Route::resource('members', MemberController::class);
    Route::patch('members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');

    Route::get('categories', [MembershipCategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [MembershipCategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [MembershipCategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [MembershipCategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('levels', [MembershipLevelController::class, 'index'])->name('levels.index');
    Route::post('levels', [MembershipLevelController::class, 'store'])->name('levels.store');
    Route::put('levels/{level}', [MembershipLevelController::class, 'update'])->name('levels.update');
    Route::delete('levels/{level}', [MembershipLevelController::class, 'destroy'])->name('levels.destroy');

    Route::get('transactions', [MembershipTransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/create', [MembershipTransactionController::class, 'create'])->name('transactions.create');
    Route::post('transactions', [MembershipTransactionController::class, 'store'])->name('transactions.store');
    Route::patch('transactions/{transaction}/status', [MembershipTransactionController::class, 'updateStatus'])->name('transactions.status');
});
