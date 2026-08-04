<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalVolunteers' => User::query()->whereRelation('role', 'slug', 'volunteer')->count(),
            'totalDispatchers' => User::query()->whereRelation('role', 'slug', 'dispatcher')->count(),
            'totalIncidents' => Incident::query()->count(),
        ]);
    }
}
