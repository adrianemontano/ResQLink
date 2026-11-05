<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     * Redirects users who must change their temporary password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $exemptRoutes = ['password.change', 'password.update', 'logout'];

            if (! in_array($request->route()?->getName(), $exemptRoutes, true)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'You must change your temporary password before accessing the system.',
                        'must_change_password' => true,
                    ], 403);
                }

                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
