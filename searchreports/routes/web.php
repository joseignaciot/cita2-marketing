<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect()->route('login'));

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// Public shared report
Route::get('/shared/{token}', [ReportController::class, 'showShared'])->name('reports.shared');

Route::middleware(['auth', 'verified'])->group(function () {
    // SPA pages
    Route::get('/dashboard', fn () => Inertia::render('Dashboard/Index'))->name('dashboard');
    Route::get('/properties', fn () => Inertia::render('Properties/Index'))->name('properties.index');
    Route::get('/properties/{id}', fn () => Inertia::render('Properties/Show'))->name('properties.show');
    Route::get('/templates', fn () => Inertia::render('Templates/Index'))->name('templates.index');
    Route::get('/templates/builder', fn () => Inertia::render('Templates/Builder'))->name('templates.builder');
    Route::get('/templates/{id}/edit', fn () => Inertia::render('Templates/Builder'))->name('templates.edit');
    Route::get('/reports', fn () => Inertia::render('Reports/Index'))->name('reports.index');
    Route::get('/reports/create', fn () => Inertia::render('Reports/Create'))->name('reports.create');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API endpoints
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');

        Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::post('/properties/sync', [PropertyController::class, 'sync'])
            ->middleware('throttle:5,60')
            ->name('properties.sync');
        Route::get('/properties/{property}/metrics', [PropertyController::class, 'metrics'])->name('properties.metrics');
        Route::get('/properties/{property}/queries', [PropertyController::class, 'queries'])->name('properties.queries');
        Route::get('/properties/{property}/pages', [PropertyController::class, 'pages'])->name('properties.pages');

        Route::apiResource('templates', TemplateController::class);
        Route::post('/templates/{template}/duplicate', [TemplateController::class, 'duplicate'])->name('templates.duplicate');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports', [ReportController::class, 'store'])
            ->middleware('throttle:20,1440')
            ->name('reports.store');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
        Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
        Route::post('/reports/{report}/share', [ReportController::class, 'share'])->name('reports.share');
    });
});

require __DIR__ . '/auth.php';
