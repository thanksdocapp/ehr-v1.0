<?php

namespace App\Routing;

use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\Staff\ServiceOrdersController;
use Illuminate\Support\Facades\Route;

/**
 * Re-register routes that may be missing when bootstrap/cache/routes-v7.php
 * was built before newer features were deployed.
 */
class StaleRouteCacheFallbacks
{
    public static function register(): void
    {
        static::registerPublicNonConsultationBookingRoutes();
        static::registerStaffServiceOrderRoutes();
        static::registerAdminSettingsDeploymentRoute();
    }

    private static function registerAdminSettingsDeploymentRoute(): void
    {
        if (! Route::has('admin.settings.apply-deployment-updates')) {
            Route::middleware(['web', 'installed', 'auth:admin', 'admin', 'require.2fa', 'log.activity'])
                ->prefix('admin')
                ->name('admin.')
                ->group(function () {
                    Route::post('/settings/apply-deployment-updates', [SettingsController::class, 'applyDeploymentUpdates'])
                        ->name('settings.apply-deployment-updates');
                });
        }

        if (Route::has('admin.settings.restore-beynon-364')) {
            return;
        }

        Route::middleware(['web', 'installed', 'auth:admin', 'admin', 'require.2fa', 'log.activity'])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::post('/settings/restore-beynon-364', [SettingsController::class, 'restoreRobertBeynonMedicalRecord364'])
                    ->name('settings.restore-beynon-364');
            });
    }

    private static function registerPublicNonConsultationBookingRoutes(): void
    {
        if (Route::has('public.booking.non-consultation.patient-details')) {
            return;
        }

        Route::middleware(['web', 'installed', 'booking.embed'])
            ->prefix('book')
            ->name('public.booking.')
            ->group(function () {
                Route::get('/service-order-success/{orderNumber}', [PublicBookingController::class, 'nonConsultationSuccess'])
                    ->name('non-consultation.success');
                Route::get('/non-consultation/patient-details', [PublicBookingController::class, 'showNonConsultationPatientDetails'])
                    ->name('non-consultation.patient-details');
                Route::get('/non-consultation/review', [PublicBookingController::class, 'showNonConsultationReview'])
                    ->name('non-consultation.review');
                Route::post('/non-consultation/patient-details', [PublicBookingController::class, 'nonConsultationReview'])
                    ->name('non-consultation.review.post');
                Route::post('/non-consultation/confirm', [PublicBookingController::class, 'nonConsultationConfirm'])
                    ->name('non-consultation.confirm');
                Route::post('/non-consultation/preview-doctor-discount', [PublicBookingController::class, 'previewNonConsultationDoctorDiscount'])
                    ->name('non-consultation.preview-doctor-discount');
                Route::post('/non-consultation/preview-clinic-discount', [PublicBookingController::class, 'previewNonConsultationClinicDiscount'])
                    ->name('non-consultation.preview-clinic-discount');
            });
    }

    private static function registerStaffServiceOrderRoutes(): void
    {
        if (Route::has('staff.service-orders.index')) {
            return;
        }

        Route::middleware(['web', 'installed', 'auth', 'staff', 'require.2fa', 'log.activity'])
            ->prefix('staff')
            ->name('staff.')
            ->group(function () {
                Route::get('/service-orders', [ServiceOrdersController::class, 'index'])->name('service-orders.index');
                Route::get('/service-orders/{serviceOrder}', [ServiceOrdersController::class, 'show'])->name('service-orders.show');
                Route::post('/service-orders/{serviceOrder}/contacted', [ServiceOrdersController::class, 'markContacted'])
                    ->name('service-orders.contacted');
                Route::post('/service-orders/{serviceOrder}/completed', [ServiceOrdersController::class, 'markCompleted'])
                    ->name('service-orders.completed');
            });
    }
}
