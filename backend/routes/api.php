<?php

use App\Http\Controllers\Api\IncidentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->post('/incidents', [IncidentController::class, 'store'])
    ->name('api.incidents.store');
