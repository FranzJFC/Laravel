<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function choose(): View
    {
        return view('auth.choose');
    }

    public function create(Request $request): View
    {
        return view('auth.login', ['role' => $request->route('login_role')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $role = $request->route('login_role');
        $key = 'login:'.$role.':'.hash('sha256', Str::lower($data['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Too many login attempts. Try again in '.RateLimiter::availableIn($key).' seconds.']);
        }

        if (! Auth::attempt(['email' => Str::lower($data['email']), 'password' => $data['password'], 'role' => $role])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'These credentials do not match this login.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        // Always use the role's landing page; never follow a cross-role intended URL.
        $request->session()->forget('url.intended');

        return redirect()->route($role === 'admin' ? 'membership.dashboard' : 'member.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
