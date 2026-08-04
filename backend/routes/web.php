<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IncidentController as AdminIncidentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Dispatcher\DashboardController as DispatcherDashboardController;
use App\Http\Controllers\Dispatcher\IncidentController as DispatcherIncidentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');
        Route::patch('/users/{user}/activation', [UserController::class, 'toggleActivation'])->name('users.activation');
        Route::get('/incidents', [AdminIncidentController::class, 'index'])->name('incidents.index');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    });

Route::middleware(['auth', 'role:dispatcher'])
    ->prefix('dispatcher')
    ->name('dispatcher.')
    ->group(function (): void {
        Route::get('/dashboard', DispatcherDashboardController::class)->name('dashboard');
        Route::get('/incidents', [DispatcherIncidentController::class, 'index'])->name('incidents.index');
        Route::get('/map', [DispatcherIncidentController::class, 'map'])->name('map');
    });
