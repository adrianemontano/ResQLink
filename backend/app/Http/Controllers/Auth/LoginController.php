<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            return back()
                ->withInput($request->only('login', 'password', 'remember'))
                ->with('error', 'Incorrect username or password.');
        }

        if (! $user->is_active) {
            return back()
                ->withInput($request->only('login', 'password', 'remember'))
                ->with('error', 'Your account has been deactivated. Please contact your administrator.');
        }

        if (! $user->canAccessWeb()) {
            return back()
                ->withInput($request->only('login', 'password', 'remember'))
                ->with('error', 'This account is not authorized for web access.');
        }

        Auth::login($user, (bool) ($credentials['remember'] ?? false));
        $request->session()->regenerate();

        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

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
        return match ($user->role?->name) {
            'admin' => route('admin.dashboard'),
            'dispatcher' => route('dispatcher.dashboard'),
            'volunteer' => route('volunteer.dashboard'),
            default => route('login'),
        };
    }
}
