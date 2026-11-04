<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IncidentController as AdminIncidentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VolunteerDocumentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Dispatcher\DashboardController as DispatcherDashboardController;
use App\Http\Controllers\Dispatcher\IncidentController as DispatcherIncidentController;
use App\Http\Controllers\Dispatcher\IncidentPhotoController;
use App\Http\Controllers\Dispatcher\NotificationController;
use App\Http\Controllers\Volunteer\DashboardController as VolunteerDashboardController;
use App\Http\Controllers\Volunteer\IncidentController as VolunteerIncidentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/password/change', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::put('/password/change', [PasswordChangeController::class, 'update'])->name('password.update');
});

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
        Route::post('/users/{user}/documents', [VolunteerDocumentController::class, 'store'])->name('users.documents.store');
        Route::get('/users/{user}/documents/{document}', [VolunteerDocumentController::class, 'show'])->name('users.documents.show');
        Route::patch('/users/{user}/documents/{document}/review', [VolunteerDocumentController::class, 'review'])->name('users.documents.review');
        Route::patch('/users/{user}/verification', [UserController::class, 'toggleVerification'])->name('users.verification');
        Route::get('/incidents', [AdminIncidentController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{incident}', [AdminIncidentController::class, 'show'])->name('incidents.show');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/incidents/{incident}/photos')
    ->name('admin.incidents.photo.')
    ->group(function (): void {
        Route::get('/{photo}', [IncidentPhotoController::class, 'show'])->name('show');
        Route::get('/{photo}/download', [IncidentPhotoController::class, 'download'])->name('download');
    });

Route::middleware(['auth', 'role:dispatcher'])
    ->prefix('dispatcher')
    ->name('dispatcher.')
    ->group(function (): void {
        Route::get('/dashboard', DispatcherDashboardController::class)->name('dashboard');
        Route::get('/incidents', [DispatcherIncidentController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{incident}', [DispatcherIncidentController::class, 'show'])->name('incidents.show');
        Route::patch('/incidents/{incident}/status', [DispatcherIncidentController::class, 'updateStatus'])->name('incidents.status');
        Route::get('/map', [DispatcherIncidentController::class, 'map'])->name('map');
        Route::get('/map/incidents', [DispatcherIncidentController::class, 'mapIncidents'])->name('map.incidents');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/incidents/{incident}/photos/{photo}', [IncidentPhotoController::class, 'show'])->name('incidents.photo.show');
        Route::get('/incidents/{incident}/photos/{photo}/download', [IncidentPhotoController::class, 'download'])->name('incidents.photo.download');
        Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    });

Route::middleware(['auth', 'role:volunteer'])
    ->prefix('volunteer')
    ->name('volunteer.')
    ->group(function (): void {
        Route::get('/dashboard', VolunteerDashboardController::class)->name('dashboard');
        Route::get('/incidents/create', [VolunteerIncidentController::class, 'create'])->name('incidents.create');
        Route::post('/incidents', [VolunteerIncidentController::class, 'store'])->name('incidents.store');
        Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    });
