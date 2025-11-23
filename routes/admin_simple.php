<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KeyFeatureController;
use App\Http\Controllers\Admin\NotificationController;

/*
|--------------------------------------------------------------------------
| Admin Routes (Simplified for Testing)
|--------------------------------------------------------------------------
*/

// Admin Authentication Routes (No Middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::get('forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('forgot-password.post');
    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetPassword'])->name('reset-password');
    Route::post('reset-password', [AdminAuthController::class, 'resetPassword'])->name('reset-password.post');
});

// Protected Admin Routes
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Profile Management
    Route::get('profile', [AdminAuthController::class, 'showProfile'])->name('profile');
    Route::put('profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('password', [AdminAuthController::class, 'changePassword'])->name('password.change');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Key Features Management
    Route::resource('key-features', KeyFeatureController::class);
    Route::post('key-features/{key_feature}/toggle-status', [KeyFeatureController::class, 'toggleStatus'])->name('key-features.toggle-status');
    Route::post('key-features-section/update', [KeyFeatureController::class, 'updateSection'])->name('key-features-section.update');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('admin-notifications', [NotificationController::class, 'getAdminNotifications'])->name('admin');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::post('bulk', [NotificationController::class, 'sendBulk'])->name('bulk');
        Route::get('stats', [NotificationController::class, 'getStats'])->name('stats');
        Route::get('{notification}', [NotificationController::class, 'show'])->name('show');
        Route::get('{notification}/view', [NotificationController::class, 'view'])->name('view');
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    
    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('quick-stats', [DashboardController::class, 'getQuickStats'])->name('quick-stats');
    });
});

