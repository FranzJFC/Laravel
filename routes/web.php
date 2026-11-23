<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\MembershipCategoryController;
use App\Http\Controllers\MembershipDashboardController;
use App\Http\Controllers\MembershipLevelController;
use App\Http\Controllers\MembershipTransactionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/welcome', function () {
        return view('welcome');
    })->name('home');

    Route::get('/about', [PageController::class, 'about'])->name('about');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');

    Route::prefix('membership')->group(function () {
        Route::get('/', [MembershipDashboardController::class, 'index'])->name('membership.dashboard');
        Route::resource('members', MemberController::class);
        Route::patch('members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');
        Route::delete('members/{member}/permanent', [MemberController::class, 'forceDelete'])->name('members.force-delete');

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

});

Route::get('/', fn () => auth()->check()
    ? redirect()->route(auth()->user()->isAdmin() ? 'membership.dashboard' : 'member.dashboard')
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'choose'])->name('login');
    Route::get('/admin/login', [AuthController::class, 'create'])->defaults('login_role', 'admin')->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'store'])->defaults('login_role', 'admin')->middleware('throttle:20,1,admin-login')->name('admin.login.store');
    Route::get('/member/login', [AuthController::class, 'create'])->defaults('login_role', 'member')->name('member.login');
    Route::post('/member/login', [AuthController::class, 'store'])->defaults('login_role', 'member')->middleware('throttle:20,1,member-login')->name('member.login.store');
    Route::get('/signup', [MembershipApplicationController::class, 'create'])->name('register');
    Route::post('/signup', [MembershipApplicationController::class, 'store'])->middleware('throttle:5,1,membership-submit')->name('register.store');
    Route::get('/membership/apply', fn () => redirect()->route('register'))->name('membership.create');
    Route::post('/membership/apply', [MembershipApplicationController::class, 'store'])->middleware('throttle:5,1,membership-submit')->name('membership.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [PageController::class, 'task'])->name('tasks.show');
});

Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/', [MemberPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions', [MemberPortalController::class, 'transactions'])->name('transactions');
    Route::get('/payments', [MemberPortalController::class, 'transactions'])->name('payments');
});

Route::get('/membership/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:30,1,email-verification'])->name('verification.verify');
Route::middleware(['auth', 'role:member'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:3,10,email-verification-resend')->name('verification.send');
});

Route::get('/weather', [WeatherController::class, 'show'])->middleware('throttle:60,1,weather')->name('weather.show');
