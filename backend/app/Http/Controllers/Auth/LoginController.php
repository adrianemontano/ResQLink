<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $login = $credentials['login'];

        $user = User::query()
            ->with('role')
            ->where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'These credentials do not match our records.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'This account is inactive. Please contact an administrator.',
            ]);
        }

        if ($user->hasRole('volunteer')) {
            throw ValidationException::withMessages([
                'login' => 'Volunteer accounts cannot access the web application.',
            ]);
        }

        if (! $user->canAccessWeb()) {
            throw ValidationException::withMessages([
                'login' => 'This account is not authorized for web access.',
            ]);
        }

        Auth::login($user, (bool) ($credentials['remember'] ?? false));
        $request->session()->regenerate();

        return redirect($this->redirectPathFor($user));
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }

    private function redirectPathFor(User $user): string
    {
        return match ($user->role?->slug) {
            'admin' => route('admin.dashboard'),
            'dispatcher' => route('dispatcher.dashboard'),
            default => route('login'),
        };
    }
}
