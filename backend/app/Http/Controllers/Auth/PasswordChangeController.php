<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    /**
     * Show the mandatory change password screen.
     */
    public function show(Request $request): View
    {
        return view('auth.change-password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the temporary password and remove the mandatory flag.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Please enter your current temporary password.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Your new password must be at least 8 characters long.',
            'password.confirmed' => 'The new password confirmation does not match.',
            'password.different' => 'Your new password must be different from your temporary password.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided temporary password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Password successfully updated. Welcome to ResQLink!');
    }
}
