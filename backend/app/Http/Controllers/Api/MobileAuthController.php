<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:80'],
        ]);
        $user = User::query()->with('role', 'volunteerProfile')
            ->where(fn ($query) => $query->where('username', $data['login'])->orWhere('email', $data['login']))
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password) || ! $user->canAccessWeb()) {
            throw ValidationException::withMessages(['login' => 'The provided credentials are invalid or the account cannot access the app.']);
        }
        abort_unless($user->hasRole('volunteer'), 403, 'The mobile app is for Barangay Responders.');

        $token = $user->createToken(
            $data['device_name'] ?? 'volunteer-mobile',
            ['profile:read', 'password:update', 'incidents:create'],
            now()->addDays(30),
        );

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
                'must_change_password' => (bool) $user->must_change_password,
                'user' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role?->name],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
                'barangay' => $user->volunteerProfile?->barangay,
                'role' => $user->role?->name,
                'must_change_password' => (bool) $user->must_change_password,
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)],
        ]);
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current temporary password is incorrect.']);
        }

        $user->update([
            'password' => $data['password'],
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return response()->json(['message' => 'Password changed successfully.', 'must_change_password' => false]);
    }
}
