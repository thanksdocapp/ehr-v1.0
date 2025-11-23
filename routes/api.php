<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Legacy user endpoint
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Transfer Code Verification API (Web-based)
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/transfer-codes/verify', [App\Http\Controllers\Api\TransferCodeController::class, 'verifyCode']);
    Route::get('/transfer-codes/active', [App\Http\Controllers\Api\TransferCodeController::class, 'getActiveCodes']);
});

// Mobile App API Routes
require __DIR__.'/api_v1.php';
