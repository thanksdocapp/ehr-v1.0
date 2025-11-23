<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TransactionController;

/*
|--------------------------------------------------------------------------
| Admin Routes (Working Version)
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
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Profile Management
    Route::get('profile', [AdminAuthController::class, 'profile'])->name('profile');
    Route::put('profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('password', [AdminAuthController::class, 'changePassword'])->name('password.change');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
    
    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('{user}/transactions', [UserController::class, 'transactions'])->name('transactions');
    });
    
    // Transaction Management
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::delete('{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
        Route::post('{transaction}/approve', [TransactionController::class, 'approve'])->name('approve');
        Route::post('{transaction}/reject', [TransactionController::class, 'reject'])->name('reject');
        Route::get('export/{format}', [TransactionController::class, 'export'])->name('export');
    });
    
    // Placeholder routes for features under development
    // Virtual Cards
    Route::prefix('virtual-cards')->name('virtual-cards.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Virtual Cards']); })->name('index');
    });
    
    // Deposits
    Route::prefix('deposits')->name('deposits.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Deposits']); })->name('index');
    });
    
    // Loans
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Loans']); })->name('index');
        Route::get('{id}', function($id) { return view('admin.coming-soon', ['feature' => 'Loan Details']); })->name('show');
    });
    
    // KYC
    Route::prefix('kyc')->name('kyc.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'KYC Management']); })->name('index');
        Route::get('{id}', function($id) { return view('admin.coming-soon', ['feature' => 'KYC Details']); })->name('show');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Settings']); })->name('index');
    });
    
    // SEO
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'SEO Management']); })->name('index');
    });
    
    // Frontend Content Placeholders
    Route::prefix('homepage-sections')->name('homepage-sections.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Homepage Sections']); })->name('index');
    });
    
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Services']); })->name('index');
    });
    
    Route::prefix('faqs')->name('faqs.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'FAQs']); })->name('index');
    });
    
    Route::prefix('testimonials')->name('testimonials.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Testimonials']); })->name('index');
    });
    
    // Email/SMS Config placeholders
    Route::get('email-config', function() { return view('admin.coming-soon', ['feature' => 'Email Config']); })->name('email-config');
    Route::get('sms-config', function() { return view('admin.coming-soon', ['feature' => 'SMS Config']); })->name('sms-config');
    
    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', function() { return view('admin.coming-soon', ['feature' => 'Email Templates']); })->name('index');
    });
    

    // API Routes for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
        Route::get('quick-stats', [DashboardController::class, 'getQuickStats'])->name('quick-stats');
        Route::get('transactions/chart-data', [TransactionController::class, 'getChartData'])->name('transactions.chart-data');
    });
});

