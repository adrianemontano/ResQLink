<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dispatcher.dashboard', [
            'activeIncidents' => Incident::query()->whereIn('status', ['received', 'dispatched'])->count(),
            'pendingIncidents' => Incident::query()->where('status', 'pending')->count(),
        ]);
    }
}
