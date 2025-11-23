<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DoctorApiController;
use App\Http\Controllers\Api\V1\DepartmentApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\PatientApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;

/*
|--------------------------------------------------------------------------
| API Routes V1
|--------------------------------------------------------------------------
|
| Mobile App API routes for Hospital Management System
|
*/

// Public routes (no authentication required) - with rate limiting
Route::prefix('v1')->group(function () {
    
    // Authentication routes - strict rate limiting to prevent brute force
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('/register/patient', [AuthController::class, 'registerPatient']);
        Route::post('/login/patient', [AuthController::class, 'loginPatient']);
        Route::post('/login/staff', [AuthController::class, 'loginStaff']);
    });

    // Public information routes - rate limited to prevent abuse
    Route::prefix('public')->middleware('throttle:public-api')->group(function () {
        Route::get('/departments', [DepartmentApiController::class, 'index']);
        Route::get('/departments/{id}', [DepartmentApiController::class, 'show']);
        Route::get('/departments/search', [DepartmentApiController::class, 'search']);
        Route::get('/departments/popular', [DepartmentApiController::class, 'getPopular']);
        
        Route::get('/doctors', [DoctorApiController::class, 'index']);
        Route::get('/doctors/{id}', [DoctorApiController::class, 'show']);
        Route::get('/doctors/search', [DoctorApiController::class, 'search']);
        Route::get('/doctors/specializations', [DoctorApiController::class, 'getSpecializations']);
        Route::get('/doctors/department/{departmentId}', [DoctorApiController::class, 'getByDepartment']);
    });
    
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Authentication management - sensitive operations with rate limiting
    Route::prefix('auth')->middleware('throttle:sensitive')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    });

    // Patient-specific routes
    Route::prefix('patient')->middleware('patient.api')->group(function () {
        Route::get('/profile', [PatientApiController::class, 'profile']);
        Route::put('/profile', [PatientApiController::class, 'updateProfile']);
        Route::post('/profile/photo', [PatientApiController::class, 'uploadPhoto']);
        Route::post('/change-password', [PatientApiController::class, 'changePassword']);
        
        // Medical information
        Route::get('/medical-history', [PatientApiController::class, 'getMedicalHistory']);
        Route::get('/vital-stats', [PatientApiController::class, 'getVitalStats']);
        Route::put('/emergency-contact', [PatientApiController::class, 'updateEmergencyContact']);
        Route::put('/medical-info', [PatientApiController::class, 'updateMedicalInfo']);
        
        // Account management - sensitive operation
        Route::delete('/account', [PatientApiController::class, 'deleteAccount'])->middleware('throttle:sensitive');
    });

    // Appointment management
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentApiController::class, 'index']);
        Route::get('/{id}', [AppointmentApiController::class, 'show']);
        Route::post('/', [AppointmentApiController::class, 'store']);
        Route::put('/{id}', [AppointmentApiController::class, 'update']);
        Route::post('/{id}/cancel', [AppointmentApiController::class, 'cancel']);
        Route::get('/slots/available', [AppointmentApiController::class, 'getAvailableSlots']);
    });

    // Doctor information (authenticated access for detailed info)
    Route::prefix('doctors')->group(function () {
        Route::get('/{id}/schedule', [DoctorApiController::class, 'getSchedule']);
    });

    // Department information (authenticated access for detailed stats)
    Route::prefix('departments')->group(function () {
        Route::get('/{id}/statistics', [DepartmentApiController::class, 'getStatistics']);
        Route::get('/booking/available', [DepartmentApiController::class, 'getForBooking']);
    });

    // Notification management
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationApiController::class, 'index']);
        Route::get('/unread-count', [NotificationApiController::class, 'getUnreadCount']);
        Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationApiController::class, 'delete']);
        
        // Specific notification types
        Route::get('/reminders', [NotificationApiController::class, 'getAppointmentReminders']);
        Route::get('/announcements', [NotificationApiController::class, 'getAnnouncements']);
        
        // Notification preferences
        Route::get('/preferences', [NotificationApiController::class, 'getPreferences']);
        Route::put('/preferences', [NotificationApiController::class, 'updatePreferences']);
    });
});

// Admin/Staff specific routes
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin.api'])->group(function () {
    
    // Patient management for admin/staff
    Route::prefix('patients')->group(function () {
        Route::get('/', [PatientApiController::class, 'index']); // List all patients
        Route::get('/{id}', [PatientApiController::class, 'show']); // View patient details
        Route::put('/{id}/status', [PatientApiController::class, 'updateStatus']); // Activate/deactivate
    });

    // Appointment management for admin/staff
    Route::prefix('appointments')->group(function () {
        Route::get('/all', [AppointmentApiController::class, 'adminIndex']); // All appointments
        Route::put('/{id}/status', [AppointmentApiController::class, 'updateStatus']); // Confirm/complete
        Route::get('/statistics', [AppointmentApiController::class, 'getStatistics']);
    });

    // System notifications management
    Route::prefix('notifications')->group(function () {
        Route::post('/broadcast', [NotificationApiController::class, 'broadcastNotification']);
        Route::post('/announcement', [NotificationApiController::class, 'createAnnouncement']);
    });

});
