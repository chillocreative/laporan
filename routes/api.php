<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OpenAILogController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportAttachmentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SecurityLogController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SystemHealthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:3,1');

Route::get('/settings/public', [SettingsController::class, 'publicSettings']);
Route::get('/roles/assignable', [RoleController::class, 'assignable']);

// Password Reset
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Categories (active list for all authenticated users)
    Route::get('/categories/active', [CategoryController::class, 'active']);

    // Reports
    Route::apiResource('reports', ReportController::class)
        ->middleware('throttle:60,1');
    Route::post('/reports/{report}/analyze', [ReportController::class, 'triggerAnalysis']);
    Route::get('/reports/{report}/analysis-status', [ReportController::class, 'analysisStatus']);
    Route::delete('/attachments/{attachment}', [ReportAttachmentController::class, 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Admin Routes
    |----------------------------------------------------------------------
    */

    Route::middleware('role:super-admin,admin')->group(function () {
        Route::get('/users/pending-count', [UserController::class, 'pendingCount']);
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
        Route::post('/users/{user}/approve', [UserController::class, 'approve']);

        // Categories management
        Route::apiResource('categories', CategoryController::class)->except(['show']);
    });

    /*
    |----------------------------------------------------------------------
    | Admin & Super Admin Routes
    |----------------------------------------------------------------------
    */

    // Roles (read-only for Admins, full CRUD for Super Admins)
    Route::middleware('role:super-admin,admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
    });

    /*
    |----------------------------------------------------------------------
    | Super Admin Routes
    |----------------------------------------------------------------------
    */

    Route::middleware('role:super-admin')->group(function () {
        // Roles & Permissions (management - Super Admin only)
        Route::apiResource('roles', RoleController::class)->except(['index']);
        Route::get('/permissions', [RoleController::class, 'permissions']);

        // Settings
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::put('/settings/{group}', [SettingsController::class, 'update']);

        // Monitoring Logs
        Route::get('/logs/activity', [ActivityLogController::class, 'index']);
        Route::get('/logs/security', [SecurityLogController::class, 'index']);
        Route::get('/logs/ai', [OpenAILogController::class, 'index']);
        Route::get('/logs/ai/today-usage', [OpenAILogController::class, 'todayUsage']);

        // System Health
        Route::get('/system-health', [SystemHealthController::class, 'index']);
    });
});

/*
|--------------------------------------------------------------------------
| Signed Routes (outside auth middleware)
|--------------------------------------------------------------------------
*/

Route::get('/attachments/{attachment}/download', [ReportAttachmentController::class, 'download'])
    ->name('attachments.download')
    ->middleware('signed');

Route::get('/attachments/{attachment}/view', [ReportAttachmentController::class, 'view'])
    ->name('attachments.view')
    ->middleware('signed');
