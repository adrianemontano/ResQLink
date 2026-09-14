<?php

use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\DispatchPointController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:dispatcher,admin'])->get('/dispatch-points', [DispatchPointController::class, 'index'])
    ->name('api.dispatch-points.index');

Route::middleware('auth')->post('/incidents', [IncidentController::class, 'store'])
    ->name('api.incidents.store');
