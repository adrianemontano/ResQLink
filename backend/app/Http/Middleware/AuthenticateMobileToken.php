<?php

namespace App\Http\Middleware;

use App\Models\MobileAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();
        abort_unless($plainToken, 401, 'A mobile bearer token is required.');

        $token = MobileAccessToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('user.role', 'user.volunteerProfile')
            ->first();
        abort_unless($token && $token->user?->canAccessWeb(), 401, 'The mobile token is invalid or expired.');

        $token->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $token->user);

        if ($token->user->must_change_password && ! $request->routeIs('api.password.change', 'api.logout', 'api.user')) {
            return response()->json([
                'message' => 'You must change your temporary password before submitting reports.',
                'must_change_password' => true,
            ], 403);
        }

        return $next($request);
    }
}
