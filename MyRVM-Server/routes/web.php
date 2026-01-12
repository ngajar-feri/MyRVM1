<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VoucherController;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Voucher Management Routes (Tenant)
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // API Documentation Iframe (Role restricted)
    Route::get('/dashboard/api-docs', function () {
        $allowedRoles = ['super_admin', 'admin', 'operator', 'teknisi'];
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            // Return styled 403 page for better UX
            $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - API Documentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            max-width: 500px;
        }
        .error-code { font-size: 80px; font-weight: bold; opacity: 0.8; }
        .error-title { font-size: 24px; margin: 20px 0 10px; }
        .error-message { font-size: 14px; opacity: 0.9; line-height: 1.6; }
        .icon { font-size: 60px; margin-bottom: 20px; }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <div class="error-message">Your role does not have permission to access API Documentation. This page is only available for Super Admin, Admin, Operator, and Technician roles.</div>
        <a href="' . route('dashboard') . '" class="btn">Back to Dashboard</a>
    </div>
</body>
</html>';
            return response($html, 403);
        }
        return view('dashboard.api-docs');
    })->name('dashboard.api-docs');

    // Dashboard Management Modules
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // User & Tenants Management
        Route::get('/users', [App\Http\Controllers\Dashboard\UserController::class, 'index'])->name('users');
        Route::get('/users/content', [App\Http\Controllers\Dashboard\UserController::class, 'indexContent']);

        // RVM Machines Management
        Route::get('/machines', [App\Http\Controllers\Dashboard\MachineController::class, 'index'])->name('machines');
        Route::get('/machines/content', [App\Http\Controllers\Dashboard\MachineController::class, 'indexContent']);

        // Edge Devices Management
        Route::get('/devices', [App\Http\Controllers\Dashboard\DeviceController::class, 'index'])->name('devices');
        Route::get('/devices/content', [App\Http\Controllers\Dashboard\DeviceController::class, 'indexContent']);

        // CV Servers Management
        Route::get('/cv-servers', [App\Http\Controllers\Dashboard\CVServerController::class, 'index'])->name('cv-servers');
        Route::get('/cv-servers/content', [App\Http\Controllers\Dashboard\CVServerController::class, 'indexContent']);

        // Logs Management
        Route::get('/logs', [App\Http\Controllers\Dashboard\LogsController::class, 'index'])->name('logs');
        Route::get('/logs/content', [App\Http\Controllers\Dashboard\LogsController::class, 'content']);

        // Assignment Management
        Route::get('/assignments', [App\Http\Controllers\Dashboard\AssignmentController::class, 'index'])->name('assignments');
        Route::get('/assignments/content', [App\Http\Controllers\Dashboard\AssignmentController::class, 'indexContent']);
    });

    // Dashboard API endpoints (use web auth instead of Sanctum)
    Route::middleware(['web', 'auth'])->prefix('api/v1/dashboard')->group(function () {
        Route::get('/users', [App\Http\Controllers\Api\UserController::class, 'getAllUsers']);
        Route::get('/users/{id}/stats', [App\Http\Controllers\Api\UserController::class, 'getUserStats']);
        Route::get('/machines', [App\Http\Controllers\Api\RvmMachineController::class, 'index']);
        Route::get('/machines/{id}', [App\Http\Controllers\Api\RvmMachineController::class, 'show']);
        Route::get('/devices', [App\Http\Controllers\Api\EdgeDeviceController::class, 'index']);
    });
});
