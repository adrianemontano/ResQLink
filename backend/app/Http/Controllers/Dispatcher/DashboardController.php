<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dispatcher.dashboard', [
            'activeIncidents' => 0,
            'pendingIncidents' => 0,
        ]);
    }
}
