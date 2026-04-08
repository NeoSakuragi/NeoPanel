<?php

use App\Http\Controllers\Api\InstanceStatusController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthBridgeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\DeploymentHistoryController;
use App\Http\Controllers\InstanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('applications', ApplicationController::class)->except(['show']);
    Route::resource('instances', InstanceController::class)->except(['show']);
    Route::get('instances/{instance}/login/{profile}', [AuthBridgeController::class, 'login'])->name('instances.login');
    Route::post('api/generate-secret', [AuthBridgeController::class, 'generateSecret'])->name('api.generate-secret');

    // Deployment
    Route::get('api/instances/{instance}/tags', [DeployController::class, 'tags'])->name('api.instances.tags');
    Route::post('api/instances/{instance}/deploy', [DeployController::class, 'deploy'])->name('api.instances.deploy');
    Route::get('api/deployments/{deployment}/status', [DeployController::class, 'status'])->name('api.deployments.status');
    Route::get('api/instances/{instance}/deployments', [DeployController::class, 'history'])->name('api.instances.deployments');

    Route::get('/deployments', [DeploymentHistoryController::class, 'index'])->name('deployments.index');
    Route::get('/deployments/{deployment}', [DeploymentHistoryController::class, 'show'])->name('deployments.show');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API for AJAX status polling
    Route::prefix('api')->group(function () {
        Route::get('instances/status', [InstanceStatusController::class, 'index']);
        Route::get('instances/{instance}/status', [InstanceStatusController::class, 'show']);
    });
});

require __DIR__.'/auth.php';
