<?php

use App\Http\Controllers\Api\DispatchPointController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->name('api.mobile.')->group(function (): void {
    Route::post('/login', [MobileAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', [MobileAuthController::class, 'user'])
            ->middleware('abilities:profile:read')
            ->name('user');
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
        Route::put('/password', [MobileAuthController::class, 'changePassword'])
            ->middleware('abilities:password:update')
            ->name('password.update');
    });
});

Route::middleware(['auth', 'role:dispatcher,admin'])->get('/dispatch-points', [DispatchPointController::class, 'index'])
    ->name('api.dispatch-points.index');

Route::middleware(['auth:sanctum', 'mobile.password.changed', 'abilities:incidents:create'])
    ->post('/incidents', [IncidentController::class, 'store'])
    ->name('api.incidents.store');
