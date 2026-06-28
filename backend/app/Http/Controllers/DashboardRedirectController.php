<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match ($user?->role?->slug) {
            'admin' => redirect()->route('admin.dashboard'),
            'dispatcher' => redirect()->route('dispatcher.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
