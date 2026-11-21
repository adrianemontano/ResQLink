<?php

use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\DispatchPointController;
use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [MobileAuthController::class, 'login'])->middleware('throttle:5,1')->name('api.login');
Route::middleware('api.token')->group(function (): void {
    Route::get('/user', [MobileAuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [MobileAuthController::class, 'logout'])->name('api.logout');
    Route::put('/password/change', [MobileAuthController::class, 'changePassword'])->name('api.password.change');
});

Route::middleware(['auth', 'role:dispatcher,admin'])->get('/dispatch-points', [DispatchPointController::class, 'index'])
    ->name('api.dispatch-points.index');

Route::middleware('api.token')->post('/incidents', [IncidentController::class, 'store'])
    ->name('api.incidents.store');
