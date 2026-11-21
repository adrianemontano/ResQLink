<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.edit', [
            'user' => $request->user(),
            'routePrefix' => $request->user()->role->name,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'contact_number' => ['nullable', 'string', 'max:30'],
        ]);
        $user->update($data);

        return redirect()->route($user->role->name.'.account.edit')
            ->with('status', 'Your profile was updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (Hash::check($data['password'], $request->user()->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => 'The new password must be different from your current password.',
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route($request->user()->role->name.'.account.edit')
            ->with('status', 'Your password was changed.');
    }
}
