<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Dynamic CSS Route (must be accessible without middleware)
Route::get('/css/dynamic-theme.css', [\App\Http\Controllers\ThemeController::class, 'dynamicCss'])->name('theme.css');

// Root Route Handler - Patient Booking Page
Route::get('/', function () {
    if (!File::exists(storage_path('installed'))) {
        return redirect()->route('install.index');
    }
    
    // Check if frontend is enabled
    $frontendEnabled = \App\Models\Setting::get('enable_frontend', '1');
    if ($frontendEnabled != '1') {
        return redirect()->route('login');
    }
    
    // Homepage is now the patient booking page
    return app(AppointmentController::class)->create();
})->name('homepage');

// Installation Routes (only accessible if not installed)
Route::group(['prefix' => 'install', 'middleware' => 'install.check'], function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::get('/{step}', [InstallController::class, 'step'])->name('install.step');
    Route::post('/{step}', [InstallController::class, 'process'])->name('install.process');
    Route::post('/cleanup', [InstallController::class, 'cleanup'])->name('install.cleanup');
    Route::get('/status', [InstallController::class, 'status'])->name('install.status');
    Route::get('/progress/{step}', [InstallController::class, 'checkProgress'])->name('install.progress');
});

// Password Reset Routes (for admin/staff - must be outside middleware to work)
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\AdminPasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset/update', [\App\Http\Controllers\Auth\AdminPasswordResetController::class, 'reset'])->name('password.reset.update');

// Main Application Routes (only accessible if installed)
Route::group(['middleware' => 'installed'], function () {
    
    // Public Routes - Protected by frontend.enabled middleware
    Route::middleware('frontend.enabled')->group(function () {
        // Home page removed - homepage is now the booking page
        // Route::get('/home', [HomepageController::class, 'index'])->name('home');
        
        // Website content pages removed - keeping patient booking only
        // Route::get('/about', [HomepageController::class, 'about'])->name('about');
        // Route::get('/contact', [ContactController::class, 'index'])->name('contact');
        // Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
        // Route::get('/faq', [HomepageController::class, 'faq'])->name('faq');
        // Route::get('/departments', [HomepageController::class, 'departments'])->name('departments');
        // Route::get('/departments/{id}', [HomepageController::class, 'departmentDetail'])->name('departments.show');
        // Route::get('/services', [HomepageController::class, 'services'])->name('services');
        // Route::get('/services/{id}', [HomepageController::class, 'serviceDetail'])->name('services.show');
        // Route::get('/doctors', [HomepageController::class, 'doctors'])->name('doctors');
        // Route::get('/doctors/{id}', [HomepageController::class, 'doctorDetail'])->name('doctors.show');
        
        // Appointment Routes - KEPT (Patient Booking)
        Route::get('/appointments/book', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/confirmation/{appointmentNumber}', [AppointmentController::class, 'confirmation'])->name('appointments.confirmation');
    });
    
    // AJAX Routes for appointment booking (matching frontend expectations)
    Route::get('/appointments/doctors/{departmentId}', [AppointmentController::class, 'getDoctorsByDepartment'])->name('appointments.doctors');
    Route::get('/appointments/slots/{doctorId}', [AppointmentController::class, 'getAvailableSlots'])->name('appointments.slots');
    
    // Public Booking Routes (with unique doctor/clinic links)
    // IMPORTANT: Specific routes must come BEFORE parameterized routes to avoid conflicts
    Route::prefix('book')->name('public.booking.')->group(function () {
        // GET routes with specific paths (must come before POST to avoid conflicts)
        Route::get('/clinic/{slug}', [\App\Http\Controllers\PublicBookingController::class, 'showClinicBooking'])->name('clinic');
        Route::get('/success/{appointmentNumber}', [\App\Http\Controllers\PublicBookingController::class, 'success'])->name('success');
        // GET route for review page (handles direct access/refresh)
        Route::get('/review', [\App\Http\Controllers\PublicBookingController::class, 'showReview'])->name('review.show');
        
        // POST routes (specific paths)
        Route::post('/select-datetime', [\App\Http\Controllers\PublicBookingController::class, 'selectDateTime'])->name('select-datetime');
        Route::post('/patient-details', [\App\Http\Controllers\PublicBookingController::class, 'patientDetails'])->name('patient-details');
        Route::post('/review', [\App\Http\Controllers\PublicBookingController::class, 'review'])->name('review');
        Route::post('/confirm', [\App\Http\Controllers\PublicBookingController::class, 'confirm'])->name('confirm');
        
        // Parameterized route last (catches /book/{slug})
        Route::get('/{slug}', [\App\Http\Controllers\PublicBookingController::class, 'showDoctorBooking'])->name('doctor');
    });
    
    // Public Booking API Routes
    Route::prefix('api')->name('public.api.')->group(function () {
        Route::get('/doctor/{id}/available-slots', [\App\Http\Controllers\PublicBookingController::class, 'getAvailableSlots'])->name('available-slots');
    });
    
    // Patient Management API Routes
    Route::get('/api/patients/stats', [AppointmentController::class, 'getPatientStats'])->name('api.patients.stats');
    Route::get('/api/patients/search', [AppointmentController::class, 'searchPatients'])->name('api.patients.search');
    
    // Appointment Management Routes
    Route::get('/appointments/dashboard/{patientId}', [AppointmentController::class, 'dashboard'])->name('appointments.dashboard');
    Route::get('/api/appointments', [AppointmentController::class, 'getPatientAppointments'])->name('api.appointments');
    Route::patch('/api/appointments/{appointmentId}/status', [AppointmentController::class, 'updateStatus'])->name('api.appointments.status');
    Route::patch('/api/appointments/{appointmentId}/reschedule', [AppointmentController::class, 'reschedule'])->name('api.appointments.reschedule');
    Route::patch('/api/appointments/{appointmentId}/cancel', [AppointmentController::class, 'cancel'])->name('api.appointments.cancel');
    
    // Staff Authentication Routes (main login for all staff)
    Route::get('/login', [\App\Http\Controllers\Auth\StaffAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\StaffAuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Auth\StaffAuthController::class, 'logout'])->name('logout');
    
    // Staff Two-Factor Authentication Routes (public - during login flow)
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/two-factor/verify', [\App\Http\Controllers\Staff\TwoFactorController::class, 'showVerify'])->name('two-factor.verify');
        Route::post('/two-factor/verify', [\App\Http\Controllers\Staff\TwoFactorController::class, 'verify'])->name('two-factor.verify.post');
        Route::post('/two-factor/verify-recovery', [\App\Http\Controllers\Staff\TwoFactorController::class, 'verifyRecovery'])->name('two-factor.verify.recovery');
        Route::post('/two-factor/resend', [\App\Http\Controllers\Staff\TwoFactorController::class, 'resendCode'])->name('two-factor.resend');
        Route::get('/two-factor/setup', [\App\Http\Controllers\Staff\TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    });
    
    // Unified Dashboard (for all authenticated staff)
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware(['auth'])->name('dashboard');
    Route::get('/api/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'getStats'])
        ->middleware(['auth'])->name('dashboard.stats');
    
    // Profile Management (for all authenticated users)
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        // Password Management
        Route::get('/change-password', [\App\Http\Controllers\Auth\StaffAuthController::class, 'showChangePassword'])->name('change-password');
        Route::post('/change-password', [\App\Http\Controllers\Auth\StaffAuthController::class, 'changePassword']);
        
        // Payment Routes
        Route::get('/payment/select-gateway', [PaymentController::class, 'selectGateway'])->name('payment.select-gateway');
        Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
        Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/payment/cancelled', [PaymentController::class, 'paymentCancelled'])->name('payment.cancelled');
        Route::get('/payment/transactions', [PaymentController::class, 'listTransactions'])->name('payment.transactions');
        Route::get('/payment/transaction/{transaction}', [PaymentController::class, 'showTransaction'])->name('payment.transaction');
        Route::get('/payment/status/{transaction}', [PaymentController::class, 'checkStatus'])->name('payment.status');
        
        
        // Stripe Payment Routes
        Route::get('/payment/stripe/{intent}', function($intent) {
            // Find transaction by payment intent
            $transaction = \App\Models\PaymentTransaction::with('paymentGateway')->where('gateway_transaction_id', $intent)->first();
            $clientSecret = $transaction ? $transaction->gateway_response['client_secret'] ?? '' : '';
            return view('payment.stripe-checkout', compact('transaction', 'clientSecret'));
        })->name('payment.stripe-checkout');
        
        Route::get('/payment/mock-stripe/{intent}', function($intent) {
            return view('payment.mock-stripe');
        })->name('payment.mock-stripe');

    });
    
    // Payment Webhooks (public, no auth required)
    Route::post('/payment/webhook/{provider}', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');
    
    // Paystack callback route (public, no auth required)
    Route::get('/payment/paystack/callback', [PaymentController::class, 'paystackCallback'])->name('payment.paystack.callback');
    
    // Flutterwave callback route (public, no auth required)
    Route::get('/payment/flutterwave/callback', [PaymentController::class, 'flutterwaveCallback'])->name('payment.flutterwave.callback');
    
    // Public Billing Routes (no authentication required - uses secure token)
    Route::prefix('pay')->name('public.billing.')->group(function () {
        Route::get('/{token}', [\App\Http\Controllers\PublicBillingController::class, 'showInvoice'])->name('pay');
        Route::post('/{token}/select-gateway', [\App\Http\Controllers\PublicBillingController::class, 'showPaymentForm'])->name('select-gateway');
        Route::post('/{token}/process-payment', [\App\Http\Controllers\PublicBillingController::class, 'processPayment'])->name('process-payment');
        Route::get('/{token}/success', [\App\Http\Controllers\PublicBillingController::class, 'paymentSuccess'])->name('success');
        Route::get('/invalid', [\App\Http\Controllers\PublicBillingController::class, 'invalid'])->name('invalid');
    });
    
    // CoinGate callback route (public, no auth required)
    Route::get('/payment/coingate/callback', [PaymentController::class, 'coinGateCallback'])->name('payment.coingate.callback');
    
    // BTCPay Server callback route (public, no auth required)
    Route::get('/payment/btcpay/callback', [PaymentController::class, 'btcPayCallback'])->name('payment.btcpay.callback');
    
    
    // Staff Two-Factor Authentication Management Routes (protected but exempt from require.2fa to allow setup)
    Route::middleware(['auth', 'staff', 'log.activity'])->prefix('staff')->name('staff.')->group(function () {
        // Two-Factor Authentication Management (exempt from require.2fa middleware)
        Route::post('/two-factor/enable', [\App\Http\Controllers\Staff\TwoFactorController::class, 'enable'])->name('two-factor.enable');
        Route::post('/two-factor/disable', [\App\Http\Controllers\Staff\TwoFactorController::class, 'disable'])->name('two-factor.disable');
        Route::post('/two-factor/regenerate-codes', [\App\Http\Controllers\Staff\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-codes');
    });
    
    // Staff Routes (using dedicated Staff controllers with limited functionality)
    Route::middleware(['auth', 'staff', 'require.2fa', 'log.activity'])->prefix('staff')->name('staff.')->group(function () {
        // Staff Dashboard
        Route::get('/', [\App\Http\Controllers\Staff\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\Staff\DashboardController::class, 'index']);
        Route::get('/api/stats', [\App\Http\Controllers\Staff\DashboardController::class, 'getStats'])->name('api.stats');
        Route::post('/toggle-dark-mode', [\App\Http\Controllers\Staff\DashboardController::class, 'toggleDarkMode'])->name('toggle-dark-mode');
        
        // Auto-complete suggestions API
        Route::get('/api/suggestions/diagnosis', [\App\Http\Controllers\Api\SuggestionController::class, 'getDiagnosisSuggestions'])->name('api.suggestions.diagnosis');
        Route::get('/api/suggestions/medication', [\App\Http\Controllers\Api\SuggestionController::class, 'getMedicationSuggestions'])->name('api.suggestions.medication');
        
        // Patient Search API (for quick search)
        Route::get('/api/patients/search', [\App\Http\Controllers\AppointmentController::class, 'searchPatients'])->name('api.patients.search');
        
        // Patients Management (limited functionality)
        Route::get('/patients', [\App\Http\Controllers\Staff\PatientsController::class, 'index'])->name('patients.index');
        Route::get('/patients/create', [\App\Http\Controllers\Staff\PatientsController::class, 'create'])->name('patients.create');
        Route::post('/patients', [\App\Http\Controllers\Staff\PatientsController::class, 'store'])->name('patients.store');
        Route::get('/patients/{patient}', [\App\Http\Controllers\Staff\PatientsController::class, 'show'])->name('patients.show');
        Route::get('/patients/{patient}/download-document/{type}', [\App\Http\Controllers\Staff\PatientsController::class, 'downloadDocument'])->name('patients.download-document');
        Route::get('/patients/{patient}/edit', [\App\Http\Controllers\Staff\PatientsController::class, 'edit'])->name('patients.edit');
        Route::put('/patients/{patient}', [\App\Http\Controllers\Staff\PatientsController::class, 'update'])->name('patients.update');
        Route::get('/patients/{patient}/gp-email', [\App\Http\Controllers\Staff\PatientsController::class, 'showGpEmailForm'])->name('patients.gp-email');
        Route::post('/patients/{patient}/gp-email', [\App\Http\Controllers\Staff\PatientsController::class, 'sendGpEmail'])->name('patients.gp-email.send');
        // Note: Staff cannot delete patients
        
        // Patient Alerts - All Alerts List
        Route::get('/alerts', [\App\Http\Controllers\Staff\AlertsController::class, 'index'])->name('alerts.index');
        
        // Patient Alerts Management
        Route::get('/patients/{patient}/alerts', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'index'])->name('patients.alerts.index');
        Route::get('/patients/{patient}/alerts/create', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'create'])->name('patients.alerts.create');
        Route::post('/patients/{patient}/alerts', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'store'])->name('patients.alerts.store');
        Route::get('/patients/{patient}/alerts/{alert}', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'show'])->name('patients.alerts.show');
        Route::get('/patients/{patient}/alerts/{alert}/edit', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'edit'])->name('patients.alerts.edit');
        Route::put('/patients/{patient}/alerts/{alert}', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'update'])->name('patients.alerts.update');
        Route::post('/patients/{patient}/alerts/{alert}/toggle-active', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'toggleActive'])->name('patients.alerts.toggle-active');
        Route::delete('/patients/{patient}/alerts/{alert}', [\App\Http\Controllers\Staff\PatientAlertsController::class, 'destroy'])->name('patients.alerts.destroy');
        
        // Appointments Management (limited functionality)
        Route::get('/appointments', [\App\Http\Controllers\Staff\AppointmentsController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/calendar', [\App\Http\Controllers\Staff\AppointmentsController::class, 'calendar'])->name('appointments.calendar');
        Route::get('/appointments/create', [\App\Http\Controllers\Staff\AppointmentsController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [\App\Http\Controllers\Staff\AppointmentsController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/{id}', [\App\Http\Controllers\Staff\AppointmentsController::class, 'show'])->name('appointments.show');
        Route::get('/appointments/{id}/edit', [\App\Http\Controllers\Staff\AppointmentsController::class, 'edit'])->name('appointments.edit');
        Route::put('/appointments/{id}', [\App\Http\Controllers\Staff\AppointmentsController::class, 'update'])->name('appointments.update');
        Route::post('/appointments/{id}/confirm', [\App\Http\Controllers\Staff\AppointmentsController::class, 'confirm'])->name('appointments.confirm');
        Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\Staff\AppointmentsController::class, 'cancel'])->name('appointments.cancel');
        Route::post('/appointments/{id}/reschedule', [\App\Http\Controllers\Staff\AppointmentsController::class, 'reschedule'])->name('appointments.reschedule');
        Route::patch('/appointments/{id}/status', [\App\Http\Controllers\Staff\AppointmentsController::class, 'updateStatus'])->name('appointments.update-status');
        
        // AJAX Routes for Calendar
        Route::get('/api/appointments/calendar-data', [\App\Http\Controllers\Staff\AppointmentsController::class, 'getCalendarData'])->name('api.appointments.calendar-data');
        // Note: Staff cannot delete appointments or access advanced features
        
        // Doctors - Read Only Access
        Route::get('/doctors', [\App\Http\Controllers\Admin\DoctorsController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorsController::class, 'show'])->name('doctors.show');
        // Note: Staff cannot create, edit, or delete doctors
        
        // Medical Records - Role-based Access (doctors and nurses can create/edit, others view only)
        Route::get('/medical-records', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'index'])->name('medical-records.index');
        Route::get('/medical-records/create', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'create'])->name('medical-records.create');
        Route::post('/medical-records', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'store'])->name('medical-records.store');
        Route::get('/medical-records/{medical_record}', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'show'])->name('medical-records.show');
        Route::get('/medical-records/{medical_record}/edit', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'edit'])->name('medical-records.edit');
        Route::put('/medical-records/{medical_record}', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'update'])->name('medical-records.update');
        Route::delete('/medical-records/{medical_record}', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'destroy'])->name('medical-records.destroy');
        Route::get('medical-records/create-from-appointment/{appointment}', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'createFromAppointment'])->name('medical-records.create-from-appointment');
        Route::get('api/appointments-by-patient', [\App\Http\Controllers\Staff\MedicalRecordsController::class, 'getAppointmentsByPatient'])->name('api.appointments-by-patient');
        
        // Medical Record Attachments
        Route::get('/medical-record-attachments/{attachment}/view', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'view'])->name('medical-record-attachments.view');
        Route::get('/medical-record-attachments/{attachment}/download', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'download'])->name('medical-record-attachments.download');
        Route::get('/medical-record-attachments/{attachment}/signed-url', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'getSignedUrl'])->name('medical-record-attachments.signed-url');
        Route::delete('/medical-record-attachments/{attachment}', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'destroy'])->name('medical-record-attachments.destroy');
        // Note: Only doctors can delete medical records they created, others can view/create based on role
        
        // Prescriptions - Role-based Access (doctors and pharmacists can create/edit, others view only)
        Route::get('/prescriptions', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'index'])->name('prescriptions.index');
        Route::get('/prescriptions/create', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'create'])->name('prescriptions.create');
        Route::post('/prescriptions', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'store'])->name('prescriptions.store');
        Route::get('/prescriptions/{prescription}', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'show'])->name('prescriptions.show');
        Route::get('/prescriptions/{prescription}/edit', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'edit'])->name('prescriptions.edit');
        Route::put('/prescriptions/{prescription}', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'update'])->name('prescriptions.update');
        Route::patch('/prescriptions/{prescription}/status', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'updateStatus'])->name('prescriptions.update-status');
        Route::get('/prescriptions/{prescription}/print', [\App\Http\Controllers\Staff\PrescriptionsController::class, 'print'])->name('prescriptions.print');
        // Note: Doctors can create/edit prescriptions, pharmacists can update status
        
        // Lab Reports - Role-based Access (doctors and technicians can create/edit, others view only)
        Route::get('/lab-reports', [\App\Http\Controllers\Staff\LabReportsController::class, 'index'])->name('lab-reports.index');
        Route::get('/lab-reports/create', [\App\Http\Controllers\Staff\LabReportsController::class, 'create'])->name('lab-reports.create');
        Route::post('/lab-reports', [\App\Http\Controllers\Staff\LabReportsController::class, 'store'])->name('lab-reports.store');
        Route::get('/lab-reports/{labReport}', [\App\Http\Controllers\Staff\LabReportsController::class, 'show'])->name('lab-reports.show');
        Route::get('/lab-reports/{labReport}/edit', [\App\Http\Controllers\Staff\LabReportsController::class, 'edit'])->name('lab-reports.edit');
        Route::put('/lab-reports/{labReport}', [\App\Http\Controllers\Staff\LabReportsController::class, 'update'])->name('lab-reports.update');
        Route::patch('/lab-reports/{labReport}/status', [\App\Http\Controllers\Staff\LabReportsController::class, 'updateStatus'])->name('lab-reports.update-status');
        Route::get('lab-reports/{labReport}/download', [\App\Http\Controllers\Staff\LabReportsController::class, 'download'])->name('lab-reports.download');
        // Note: Doctors can order lab reports, technicians can create/edit/complete them
        
        // Billing Management - Limited access for staff
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Staff\BillingsController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Staff\BillingsController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Staff\BillingsController::class, 'store'])->name('store');
            Route::get('{billing}', [\App\Http\Controllers\Staff\BillingsController::class, 'show'])->name('show');
            Route::get('{billing}/edit', [\App\Http\Controllers\Staff\BillingsController::class, 'edit'])->name('edit');
            Route::put('{billing}', [\App\Http\Controllers\Staff\BillingsController::class, 'update'])->name('update');
            Route::post('{billing}/send-to-patient', [\App\Http\Controllers\Staff\BillingsController::class, 'sendToPatient'])->name('send-to-patient');
        });
        
        // Doctor Services Management
        Route::prefix('doctor-services')->name('doctor-services.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'store'])->name('store');
            Route::get('/{bookingService}/edit', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'edit'])->name('edit');
            Route::put('/{bookingService}', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'update'])->name('update');
            Route::post('/{bookingService}/toggle-status', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{bookingService}', [\App\Http\Controllers\Staff\DoctorServicesController::class, 'destroy'])->name('destroy');
        });
        
        // Staff Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Staff\NotificationController::class, 'index'])->name('index');
            Route::get('/api/staff-notifications', [\App\Http\Controllers\Staff\NotificationController::class, 'getStaffNotifications'])->name('api.staff');
            Route::get('/{notification}', [\App\Http\Controllers\Staff\NotificationController::class, 'show'])->name('show');
            Route::post('/mark-as-read', [\App\Http\Controllers\Staff\NotificationController::class, 'markAsRead'])->name('markAsRead');
            Route::post('/mark-all-as-read', [\App\Http\Controllers\Staff\NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
            Route::delete('/{notification}', [\App\Http\Controllers\Staff\NotificationController::class, 'destroy'])->name('destroy');
        });
        
        // Document Templates
        Route::resource('document-templates', \App\Http\Controllers\Staff\DocumentTemplatesController::class);
        Route::post('/document-templates/{documentTemplate}/deactivate', [\App\Http\Controllers\Staff\DocumentTemplatesController::class, 'deactivate'])->name('document-templates.deactivate');
        
        // Patient Documents
        Route::get('/patients/{patient}/documents', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'index'])->name('patients.documents.index');
        Route::get('/patients/{patient}/documents/create', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'create'])->name('patients.documents.create');
        Route::post('/patients/{patient}/documents', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'store'])->name('patients.documents.store');
        Route::get('/patients/{patient}/documents/{document}', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'show'])->name('patients.documents.show');
        Route::get('/patients/{patient}/documents/{document}/edit', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'edit'])->name('patients.documents.edit');
        Route::put('/patients/{patient}/documents/{document}', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'update'])->name('patients.documents.update');
        Route::post('/patients/{patient}/documents/{document}/finalise', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'finalise'])->name('patients.documents.finalise');
        Route::post('/patients/{patient}/documents/{document}/void', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'void'])->name('patients.documents.void');
        Route::get('/patients/{patient}/documents/{document}/download', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'download'])->name('patients.documents.download');
        Route::post('/patients/{patient}/documents/bulk-action', [\App\Http\Controllers\Staff\PatientDocumentsController::class, 'bulkAction'])->name('patients.documents.bulk-action');
        
        // Document Deliveries
        Route::get('/patients/{patient}/documents/{document}/deliveries', [\App\Http\Controllers\Staff\DocumentDeliveriesController::class, 'index'])->name('patients.documents.deliveries.index');
        Route::post('/patients/{patient}/documents/{document}/deliveries', [\App\Http\Controllers\Staff\DocumentDeliveriesController::class, 'store'])->name('patients.documents.deliveries.store');
        
        // Note: Two-Factor Authentication management routes (enable/disable/regenerate) are defined above
        // (outside require.2fa middleware) to allow users to set up 2FA when it's required
        
        // No access to:
        // - User Management
        // - System Settings
        // - Financial/Billing data
        // - Website Content Management
        // - SEO Settings
        // - Advanced Admin Features
    });
    
    // Admin Authentication Routes (public) - Must be defined BEFORE protected admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Public admin routes (login/register) - No middleware applied
        Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login')->withoutMiddleware(['auth', 'admin', 'admin.auth']);
        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->withoutMiddleware(['auth', 'admin', 'admin.auth']);
        Route::get('/register', [\App\Http\Controllers\Admin\AuthController::class, 'showRegister'])->name('register')->withoutMiddleware(['auth', 'admin', 'admin.auth']);
        Route::post('/register', [\App\Http\Controllers\Admin\AuthController::class, 'register'])->withoutMiddleware(['auth', 'admin', 'admin.auth']);
        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
        
        // Two-Factor Authentication Routes (public - during login flow)
        Route::get('/two-factor/verify', [\App\Http\Controllers\Admin\TwoFactorController::class, 'showVerify'])->name('two-factor.verify');
        Route::post('/two-factor/verify', [\App\Http\Controllers\Admin\TwoFactorController::class, 'verify'])->name('two-factor.verify.post');
        Route::post('/two-factor/verify-recovery', [\App\Http\Controllers\Admin\TwoFactorController::class, 'verifyRecovery'])->name('two-factor.verify.recovery');
        Route::post('/two-factor/resend', [\App\Http\Controllers\Admin\TwoFactorController::class, 'resendCode'])->name('two-factor.resend');
    });
    
    // Patient Authentication Routes (public)
    Route::prefix('patient')->name('patient.')->group(function () {
        // Public patient routes (login/register)
        Route::get('/login', [\App\Http\Controllers\Patient\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Patient\AuthController::class, 'login']);
        Route::get('/register', [\App\Http\Controllers\Patient\AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [\App\Http\Controllers\Patient\AuthController::class, 'register']);
        Route::post('/logout', [\App\Http\Controllers\Patient\AuthController::class, 'logout'])->name('logout');
        
        // Password Reset Routes
        Route::get('password/reset', [\App\Http\Controllers\Patient\PasswordResetLinkController::class, 'create'])
            ->middleware('guest:patient')
            ->name('password.request');

        Route::post('password/email', [\App\Http\Controllers\Patient\PasswordResetLinkController::class, 'store'])
            ->middleware('guest:patient')
            ->name('password.email');

        Route::get('password/reset/{token}', [\App\Http\Controllers\Patient\NewPasswordController::class, 'create'])
            ->middleware('guest:patient')
            ->name('password.reset'); // Will become 'patient.password.reset' due to prefix group

        Route::post('password/store', [\App\Http\Controllers\Patient\NewPasswordController::class, 'store'])
            ->middleware('guest:patient')
            ->name('password.store');
    });

    // Patient Protected Routes
    Route::prefix('patient')->name('patient.')->middleware(['auth:patient'])->group(function () {
        // Patient Dashboard
        Route::get('/', [\App\Http\Controllers\Patient\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\Patient\DashboardController::class, 'index']);
        
        // Patient Profile
        Route::get('/profile', [\App\Http\Controllers\Patient\ProfileController::class, 'index'])->name('profile');
        Route::get('/profile/edit', [\App\Http\Controllers\Patient\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Patient\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile/photo', [\App\Http\Controllers\Patient\ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
        
        // Patient Appointments
        Route::get('/appointments', [\App\Http\Controllers\Patient\AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/book', [\App\Http\Controllers\Patient\AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [\App\Http\Controllers\Patient\AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/{appointment}', [\App\Http\Controllers\Patient\AppointmentController::class, 'show'])->name('appointments.show');
        Route::put('/appointments/{appointment}/cancel', [\App\Http\Controllers\Patient\AppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Patient\AppointmentController::class, 'destroy'])->name('appointments.destroy');
        
        // Patient Medical Records
        Route::get('/medical-records', [\App\Http\Controllers\Patient\MedicalRecordController::class, 'index'])->name('medical-records.index');
        Route::get('/medical-records/{record}', [\App\Http\Controllers\Patient\MedicalRecordController::class, 'show'])->name('medical-records.show');
        Route::get('/medical-records/{record}/download', [\App\Http\Controllers\Patient\MedicalRecordController::class, 'download'])->name('medical-records.download');
        Route::get('/prescriptions', [\App\Http\Controllers\Patient\MedicalRecordController::class, 'prescriptions'])->name('prescriptions.index');
        
        // Patient Lab Reports
        Route::get('/lab-reports', [\App\Http\Controllers\Patient\LabReportController::class, 'index'])->name('lab-reports.index');
        Route::get('/lab-reports/{labReport}', [\App\Http\Controllers\Patient\LabReportController::class, 'show'])->name('lab-reports.show');
        Route::get('/lab-reports/{labReport}/download', [\App\Http\Controllers\Patient\LabReportController::class, 'download'])->name('lab-reports.download');
        
        // Patient Billing
        Route::get('/billing', [\App\Http\Controllers\Patient\BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/{invoice}', [\App\Http\Controllers\Patient\BillingController::class, 'show'])->name('billing.show');
        Route::get('/billing/{invoice}/pay', [\App\Http\Controllers\Patient\BillingController::class, 'selectGateway'])->name('billing.pay');
        Route::post('/billing/{invoice}/select-gateway', [\App\Http\Controllers\Patient\BillingController::class, 'showPaymentForm'])->name('billing.select-gateway');
        Route::post('/billing/{invoice}/process-payment', [\App\Http\Controllers\Patient\BillingController::class, 'processPayment'])->name('billing.process-payment');
        Route::get('/billing/{invoice}/download', [\App\Http\Controllers\Patient\BillingController::class, 'downloadInvoice'])->name('billing.download');
        Route::get('/payments', [\App\Http\Controllers\Patient\BillingController::class, 'payments'])->name('payments.index');
        Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\Patient\BillingController::class, 'downloadReceipt'])->name('payments.receipt');
        
        // PayPal Payment Routes
        Route::get('/billing/paypal/success/{payment}', [\App\Http\Controllers\Patient\BillingController::class, 'handlePayPalSuccess'])->name('billing.paypal.success');
        Route::get('/billing/paypal/cancel/{payment}', [\App\Http\Controllers\Patient\BillingController::class, 'handlePayPalCancel'])->name('billing.paypal.cancel');
        
        // Patient Notifications
        Route::get('/notifications', [\App\Http\Controllers\Patient\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}', [\App\Http\Controllers\Patient\NotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications/mark-as-read', [\App\Http\Controllers\Patient\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\Patient\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\Patient\NotificationController::class, 'destroy'])->name('notifications.destroy');
        
        // AJAX Routes for patient portal
        Route::get('/api/stats', [\App\Http\Controllers\Patient\DashboardController::class, 'getStats'])->name('api.stats');
        
        // Auto-complete suggestions API
        Route::get('/api/suggestions/diagnosis', [\App\Http\Controllers\Api\SuggestionController::class, 'getDiagnosisSuggestions'])->name('api.suggestions.diagnosis');
        Route::get('/api/suggestions/medication', [\App\Http\Controllers\Api\SuggestionController::class, 'getMedicationSuggestions'])->name('api.suggestions.medication');
        Route::get('/api/notifications', [\App\Http\Controllers\Patient\NotificationController::class, 'getPatientNotifications'])->name('api.notifications');
        Route::get('/api/medical-records/stats', [\App\Http\Controllers\Patient\MedicalRecordController::class, 'getStats'])->name('api.medical-records.stats');
        Route::get('/api/billing/stats', [\App\Http\Controllers\Patient\BillingController::class, 'getStats'])->name('api.billing.stats');
        Route::get('/api/payment-status/{payment}', [\App\Http\Controllers\Patient\BillingController::class, 'getPaymentStatus'])->name('api.payment-status');
        Route::get('/appointments/doctors/{departmentId}', [\App\Http\Controllers\Patient\AppointmentController::class, 'getDoctorsByDepartment'])->name('appointments.doctors-by-department');
        Route::get('/appointments/slots/{doctorId}', [\App\Http\Controllers\Patient\AppointmentController::class, 'getAvailableSlots'])->name('appointments.available-slots');
    });
    
    // Patient Stripe Checkout Route (accessible outside of patient prefix)
    Route::get('/payment/patient-stripe/{intent}', function($intent) {
        // Find patient payment by payment intent
        $payment = \App\Models\Payment::with('invoice', 'invoice.patient')
            ->where('gateway_transaction_id', $intent)
            ->whereHas('invoice', function ($query) {
                $query->where('patient_id', Auth::guard('patient')->id());
            })->first();
        
        // Debug information
        \Log::info('Stripe checkout debug', [
            'intent' => $intent,
            'payment_found' => !!$payment,
            'gateway_response' => $payment ? $payment->gateway_response : null,
            'client_secret' => $payment ? ($payment->gateway_response['client_secret'] ?? 'NOT_FOUND') : 'NO_PAYMENT'
        ]);
        
        $clientSecret = $payment ? $payment->gateway_response['client_secret'] ?? '' : '';
        return view('payment.stripe-checkout', compact('payment', 'clientSecret'));
    })->middleware(['installed', 'auth:patient'])->name('payment.patient-stripe-checkout');
    
    // Admin Protected Routes
    Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'admin', 'require.2fa', 'log.activity'])->group(function () {
        // Admin Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::post('/', function() {
            return redirect()->route('admin.dashboard');
        }); // Handle POST requests to /admin by redirecting to dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
        
        // AJAX Routes for Dashboard
        Route::get('/api/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats'])->name('api.stats');
        Route::get('/api/chart-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getChartData'])->name('api.chart-data');
        Route::get('/api/department-data', [\App\Http\Controllers\Admin\DashboardController::class, 'getDepartmentData'])->name('api.department-data');
        Route::get('/api/system-health', [\App\Http\Controllers\Admin\DashboardController::class, 'getSystemHealth'])->name('api.system-health');
        Route::get('/api/advanced-charts', [\App\Http\Controllers\Admin\DashboardController::class, 'getAdvancedChartData'])->name('api.advanced-charts');
        Route::get('/api/realtime-stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getRealtimeStats'])->name('api.realtime-stats');
        
        // Account Management
        Route::get('/change-password', [\App\Http\Controllers\Admin\AuthController::class, 'showChangePassword'])->name('change-password');
        Route::post('/change-password', [\App\Http\Controllers\Admin\AuthController::class, 'changePassword']);
        
        // Two-Factor Authentication Management (protected)
        Route::get('/two-factor/setup', [\App\Http\Controllers\Admin\TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
        Route::post('/two-factor/enable', [\App\Http\Controllers\Admin\TwoFactorController::class, 'enable'])->name('two-factor.enable');
        Route::post('/two-factor/disable', [\App\Http\Controllers\Admin\TwoFactorController::class, 'disable'])->name('two-factor.disable');
        Route::post('/two-factor/regenerate-codes', [\App\Http\Controllers\Admin\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-codes');
        
        // Profile Management
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password/change', [\App\Http\Controllers\Admin\ProfileController::class, 'changePassword'])->name('profile.password.change');
        // Admin Appointment Management Routes (using unified AppointmentsController)
        Route::get('/appointments', [\App\Http\Controllers\Admin\AppointmentsController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/calendar', [\App\Http\Controllers\Admin\AppointmentsController::class, 'calendar'])->name('appointments.calendar');
        Route::get('/appointments/today', [\App\Http\Controllers\Admin\AppointmentsController::class, 'today'])->name('appointments.today');
        Route::get('/appointments/create', [\App\Http\Controllers\Admin\AppointmentsController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [\App\Http\Controllers\Admin\AppointmentsController::class, 'store'])->name('appointments.store');
        
        // Individual Appointment Management with proper parameter names
        Route::get('/appointments/{appointment}', [\App\Http\Controllers\Admin\AppointmentsController::class, 'show'])->name('appointments.show');
        Route::get('/appointments/{appointment}/edit', [\App\Http\Controllers\Admin\AppointmentsController::class, 'edit'])->name('appointments.edit');
        Route::put('/appointments/{appointment}', [\App\Http\Controllers\Admin\AppointmentsController::class, 'update'])->name('appointments.update');
        Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Admin\AppointmentsController::class, 'destroy'])->name('appointments.destroy');
        Route::post('/appointments/{appointment}/confirm', [\App\Http\Controllers\Admin\AppointmentsController::class, 'confirm'])->name('appointments.confirm');
        Route::post('/appointments/{appointment}/cancel', [\App\Http\Controllers\Admin\AppointmentsController::class, 'cancel'])->name('appointments.cancel');
        Route::post('/appointments/{appointment}/complete', [\App\Http\Controllers\Admin\AppointmentsController::class, 'complete'])->name('appointments.complete');
        Route::post('/appointments/{appointment}/reschedule', [\App\Http\Controllers\Admin\AppointmentsController::class, 'reschedule'])->name('appointments.reschedule');
        
        // AJAX Routes for Calendar
        Route::get('/api/appointments/calendar-data', [\App\Http\Controllers\Admin\AppointmentsController::class, 'getCalendarData'])->name('api.appointments.calendar-data');
        Route::get('/api/appointments/today', [\App\Http\Controllers\Admin\AppointmentsController::class, 'getTodayAppointments'])->name('api.appointments.today');
        
        // Booking Services Management
        Route::resource('booking-services', \App\Http\Controllers\Admin\BookingServicesController::class);
        Route::post('/booking-services/{bookingService}/toggle-status', [\App\Http\Controllers\Admin\BookingServicesController::class, 'toggleStatus'])->name('booking-services.toggle-status');
        Route::get('/booking-services/{bookingService}/assign-doctor', [\App\Http\Controllers\Admin\BookingServicesController::class, 'assignDoctor'])->name('booking-services.assign-doctor');
        Route::post('/booking-services/{bookingService}/assign-doctor', [\App\Http\Controllers\Admin\BookingServicesController::class, 'storeDoctorAssignment'])->name('booking-services.store-doctor-assignment');
        
        // Patients Management
        Route::get('/patients/export/csv', [\App\Http\Controllers\Admin\PatientsController::class, 'exportCsv'])->name('patients.export.csv');
        Route::get('/patients/import', [\App\Http\Controllers\Admin\PatientsController::class, 'showImport'])->name('patients.import');
        Route::post('/patients/import/csv', [\App\Http\Controllers\Admin\PatientsController::class, 'importCsv'])->name('patients.import.csv');
        Route::resource('patients', \App\Http\Controllers\Admin\PatientsController::class);
        Route::get('/patients/{patient}/download-document/{type}', [\App\Http\Controllers\Admin\PatientsController::class, 'downloadDocument'])->name('patients.download-document');
        Route::get('/patients/{patient}/gp-email', [\App\Http\Controllers\Admin\PatientsController::class, 'showGpEmailForm'])->name('patients.gp-email');
        Route::post('/patients/{patient}/gp-email', [\App\Http\Controllers\Admin\PatientsController::class, 'sendGpEmail'])->name('patients.gp-email.send');
        Route::get('/patients/{patient}/convert-guest', [\App\Http\Controllers\Admin\PatientsController::class, 'showConvertGuest'])->name('patients.convert-guest');
        Route::post('/patients/{patient}/convert-guest', [\App\Http\Controllers\Admin\PatientsController::class, 'convertGuest'])->name('patients.convert-guest.post');
        
        // Patient Alerts - All Alerts List
        Route::get('/alerts', [\App\Http\Controllers\Admin\AlertsController::class, 'index'])->name('alerts.index');
        
        // Patient Alerts Management
        Route::get('/patients/{patient}/alerts', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'index'])->name('patients.alerts.index');
        Route::get('/patients/{patient}/alerts/create', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'create'])->name('patients.alerts.create');
        Route::post('/patients/{patient}/alerts', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'store'])->name('patients.alerts.store');
        Route::get('/patients/{patient}/alerts/{alert}', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'show'])->name('patients.alerts.show');
        Route::get('/patients/{patient}/alerts/{alert}/edit', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'edit'])->name('patients.alerts.edit');
        Route::put('/patients/{patient}/alerts/{alert}', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'update'])->name('patients.alerts.update');
        Route::post('/patients/{patient}/alerts/{alert}/toggle-active', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'toggleActive'])->name('patients.alerts.toggle-active');
        Route::delete('/patients/{patient}/alerts/{alert}', [\App\Http\Controllers\Admin\PatientAlertsController::class, 'destroy'])->name('patients.alerts.destroy');
        
        // Document Templates
        Route::resource('document-templates', \App\Http\Controllers\Admin\DocumentTemplatesController::class);
        Route::post('/document-templates/{documentTemplate}/deactivate', [\App\Http\Controllers\Admin\DocumentTemplatesController::class, 'deactivate'])->name('document-templates.deactivate');
        
        // Patient Documents
        Route::get('/patients/{patient}/documents', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'index'])->name('patients.documents.index');
        Route::get('/patients/{patient}/documents/create', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'create'])->name('patients.documents.create');
        Route::post('/patients/{patient}/documents', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'store'])->name('patients.documents.store');
        Route::get('/patients/{patient}/documents/{document}', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'show'])->name('patients.documents.show');
        Route::get('/patients/{patient}/documents/{document}/edit', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'edit'])->name('patients.documents.edit');
        Route::put('/patients/{patient}/documents/{document}', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'update'])->name('patients.documents.update');
        Route::post('/patients/{patient}/documents/{document}/finalise', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'finalise'])->name('patients.documents.finalise');
        Route::post('/patients/{patient}/documents/{document}/void', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'void'])->name('patients.documents.void');
        Route::get('/patients/{patient}/documents/{document}/download', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'download'])->name('patients.documents.download');
        Route::post('/patients/{patient}/documents/bulk-action', [\App\Http\Controllers\Admin\PatientDocumentsController::class, 'bulkAction'])->name('patients.documents.bulk-action');
        
        // Document Deliveries
        Route::get('/patients/{patient}/documents/{document}/deliveries', [\App\Http\Controllers\Admin\DocumentDeliveriesController::class, 'index'])->name('patients.documents.deliveries.index');
        Route::post('/patients/{patient}/documents/{document}/deliveries', [\App\Http\Controllers\Admin\DocumentDeliveriesController::class, 'store'])->name('patients.documents.deliveries.store');
        
        // Medical Records Management
        Route::get('/medical-records/import', [\App\Http\Controllers\Admin\MedicalRecordsController::class, 'showImport'])->name('medical-records.import');
        Route::post('/medical-records/import/csv', [\App\Http\Controllers\Admin\MedicalRecordsController::class, 'importCsv'])->name('medical-records.import.csv');
        Route::resource('medical-records', \App\Http\Controllers\Admin\MedicalRecordsController::class);
        
        // Medical Record Attachments (Admin)
        Route::get('/medical-record-attachments/{attachment}/view', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'view'])->name('medical-record-attachments.view');
        Route::get('/medical-record-attachments/{attachment}/download', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'download'])->name('medical-record-attachments.download');
        Route::get('/medical-record-attachments/{attachment}/signed-url', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'getSignedUrl'])->name('medical-record-attachments.signed-url');
        Route::delete('/medical-record-attachments/{attachment}', [\App\Http\Controllers\MedicalRecordAttachmentController::class, 'destroy'])->name('medical-record-attachments.destroy');
        Route::get('medical-records/create-from-appointment/{appointment}', [\App\Http\Controllers\Admin\MedicalRecordsController::class, 'createFromAppointment'])->name('medical-records.create-from-appointment');
        Route::get('api/appointments-by-patient', [\App\Http\Controllers\Admin\MedicalRecordsController::class, 'getAppointmentsByPatient'])->name('api.appointments-by-patient');
        
        // Prescriptions Management
        Route::get('/prescriptions/import', [\App\Http\Controllers\Admin\PrescriptionsController::class, 'showImport'])->name('prescriptions.import');
        Route::post('/prescriptions/import/csv', [\App\Http\Controllers\Admin\PrescriptionsController::class, 'importCsv'])->name('prescriptions.import.csv');
        Route::resource('prescriptions', \App\Http\Controllers\Admin\PrescriptionsController::class);
        Route::patch('prescriptions/{prescription}/status', [\App\Http\Controllers\Admin\PrescriptionsController::class, 'updateStatus'])->name('prescriptions.update-status');
        
        // Lab Reports Management
        Route::get('/lab-reports/import', [\App\Http\Controllers\Admin\LabReportsController::class, 'showImport'])->name('lab-reports.import');
        Route::post('/lab-reports/import/csv', [\App\Http\Controllers\Admin\LabReportsController::class, 'importCsv'])->name('lab-reports.import.csv');
        Route::resource('lab-reports', \App\Http\Controllers\Admin\LabReportsController::class);
        Route::patch('lab-reports/{labReport}/status', [\App\Http\Controllers\Admin\LabReportsController::class, 'updateStatus'])->name('lab-reports.update-status');
        Route::get('lab-reports/{labReport}/download', [\App\Http\Controllers\Admin\LabReportsController::class, 'download'])->name('lab-reports.download');
        
        // Doctors Management
        Route::get('/doctors', [\App\Http\Controllers\Admin\DoctorsController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/create', [\App\Http\Controllers\Admin\DoctorsController::class, 'create'])->name('doctors.create');
        Route::post('/doctors', [\App\Http\Controllers\Admin\DoctorsController::class, 'store'])->name('doctors.store');
        Route::get('/doctors/export/csv', [\App\Http\Controllers\Admin\DoctorsController::class, 'exportCsv'])->name('doctors.export.csv');
        Route::get('/doctors/import', [\App\Http\Controllers\Admin\DoctorsController::class, 'showImport'])->name('doctors.import');
        Route::post('/doctors/import/csv', [\App\Http\Controllers\Admin\DoctorsController::class, 'importCsv'])->name('doctors.import.csv');
        Route::post('/doctors/bulk-delete', [\App\Http\Controllers\Admin\DoctorsController::class, 'bulkDelete'])->name('doctors.bulk-delete');
        Route::get('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorsController::class, 'show'])->name('doctors.show');
        Route::get('/doctors/{doctor}/edit', [\App\Http\Controllers\Admin\DoctorsController::class, 'edit'])->name('doctors.edit');
        Route::put('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorsController::class, 'update'])->name('doctors.update');
        Route::delete('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorsController::class, 'destroy'])->name('doctors.destroy');
        Route::post('/doctors/{doctor}/toggle-status', [\App\Http\Controllers\Admin\DoctorsController::class, 'toggleStatus'])->name('doctors.toggle-status');
        Route::post('/doctors/{doctor}/reset-password', [\App\Http\Controllers\Admin\DoctorsController::class, 'resetPassword'])->name('doctors.reset-password');
        Route::post('/doctors/{doctor}/resend-credentials', [\App\Http\Controllers\Admin\DoctorsController::class, 'resendCredentials'])->name('doctors.resend-credentials');
        
        // Billing Management
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BillingsController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Admin\BillingsController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\BillingsController::class, 'store'])->name('store');
            Route::get('{billing}', [\App\Http\Controllers\Admin\BillingsController::class, 'show'])->name('show');
            Route::get('{billing}/edit', [\App\Http\Controllers\Admin\BillingsController::class, 'edit'])->name('edit');
            Route::put('{billing}', [\App\Http\Controllers\Admin\BillingsController::class, 'update'])->name('update');
            Route::delete('{billing}', [\App\Http\Controllers\Admin\BillingsController::class, 'destroy'])->name('destroy');
            Route::post('{billing}/process-payment', [\App\Http\Controllers\Admin\BillingsController::class, 'processPayment'])->name('process-payment');
            Route::post('{billing}/update-status', [\App\Http\Controllers\Admin\BillingsController::class, 'updateStatus'])->name('update-status');
            Route::get('{billing}/invoice', [\App\Http\Controllers\Admin\BillingsController::class, 'generateInvoice'])->name('invoice');
            Route::post('{billing}/send-to-patient', [\App\Http\Controllers\Admin\BillingsController::class, 'sendToPatient'])->name('send-to-patient');
        });
        
        // Payment Gateway Management
        Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'store'])->name('store');
            Route::get('/{paymentGateway}', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'show'])->name('show');
            Route::get('/{paymentGateway}/edit', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'edit'])->name('edit');
            Route::put('/{paymentGateway}', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'update'])->name('update');
            Route::delete('/{paymentGateway}', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'destroy'])->name('destroy');
            Route::post('/{paymentGateway}/toggle-status', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{paymentGateway}/set-default', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'setDefault'])->name('set-default');
            Route::post('/{paymentGateway}/test-connection', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'testConnection'])->name('test-connection');
            Route::post('/update-order', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'updateOrder'])->name('update-order');
        });
        
        // Departments Management
        Route::get('/departments', [\App\Http\Controllers\Admin\DepartmentsController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [\App\Http\Controllers\Admin\DepartmentsController::class, 'create'])->name('departments.create');
        Route::post('/departments', [\App\Http\Controllers\Admin\DepartmentsController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentsController::class, 'show'])->name('departments.show');
        Route::get('/departments/{department}/edit', [\App\Http\Controllers\Admin\DepartmentsController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentsController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentsController::class, 'destroy'])->name('departments.destroy');
        Route::post('/departments/{department}/toggle-status', [\App\Http\Controllers\Admin\DepartmentsController::class, 'toggleStatus'])->name('departments.toggle-status');
        
        // Website Content Management - Removed (Banner Slides, Homepage Features, Testimonials, FAQs, Services, About Us, Contact Page)
        // Route::resource('banner-slides', \App\Http\Controllers\Admin\BannerSlideController::class);
        // Route::post('/banner-slides/{bannerSlide}/toggle-status', [\App\Http\Controllers\Admin\BannerSlideController::class, 'toggleStatus'])->name('banner-slides.toggle-status');
        // Route::resource('homepage-sections', \App\Http\Controllers\Admin\HomepageSectionsController::class);
        // Route::resource('homepage-features', \App\Http\Controllers\Admin\HomepageFeaturesController::class);
        // Route::post('/homepage-features/{homepageFeature}/toggle-status', [\App\Http\Controllers\Admin\HomepageFeaturesController::class, 'toggleStatus'])->name('homepage-features.toggle-status');
        // Route::resource('about-stats', \App\Http\Controllers\Admin\AboutStatsController::class);
        // Route::post('/about-stats/{aboutStat}/toggle-status', [\App\Http\Controllers\Admin\AboutStatsController::class, 'toggleStatus'])->name('about-stats.toggle-status');
        // Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class);
        // Route::post('/faqs/{faq}/toggle-status', [\App\Http\Controllers\Admin\FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
        // Route::resource('services', \App\Http\Controllers\Admin\ServicesController::class);
        // Route::post('/services/{service}/toggle-status', [\App\Http\Controllers\Admin\ServicesController::class, 'toggleStatus'])->name('services.toggle-status');
        // Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);
        // Route::post('/testimonials/{testimonial}/toggle-status', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggleStatus'])->name('testimonials.toggle-status');
        // Route::get('/about-us', [\App\Http\Controllers\Admin\AboutUsController::class, 'index'])->name('about.index');
        // Route::get('/about-us/edit', [\App\Http\Controllers\Admin\AboutUsController::class, 'edit'])->name('about.edit');
        // Route::put('/about-us', [\App\Http\Controllers\Admin\AboutUsController::class, 'update'])->name('about.update');
        // Route::get('/about-us/reset-image', [\App\Http\Controllers\Admin\AboutUsController::class, 'resetImage'])->name('about.resetImage');
        // Route::get('/contact', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contact.index');
        // Route::get('/contact/edit', [\App\Http\Controllers\Admin\ContactController::class, 'edit'])->name('contact.edit');
        // Route::put('/contact', [\App\Http\Controllers\Admin\ContactController::class, 'update'])->name('contact.update');
        
        // User Management
        Route::resource('users', \App\Http\Controllers\Admin\UsersController::class);
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UsersController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UsersController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/resend-credentials', [\App\Http\Controllers\Admin\UsersController::class, 'resendCredentials'])->name('users.resend-credentials');
        Route::get('/users/stats', [\App\Http\Controllers\Admin\UsersController::class, 'getStats'])->name('users.stats');
        
        // Communication Management
        Route::get('/email-config', [\App\Http\Controllers\Admin\CommunicationController::class, 'emailConfig'])->name('email-config');
        Route::post('/email-config', [\App\Http\Controllers\Admin\CommunicationController::class, 'updateEmailConfig']);
        Route::get('/sms-config', [\App\Http\Controllers\Admin\CommunicationController::class, 'smsConfig'])->name('sms-config');
        Route::post('/sms-config', [\App\Http\Controllers\Admin\CommunicationController::class, 'updateSmsConfig']);
        Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplatesController::class);
        Route::post('/email-templates/{emailTemplate}/duplicate', [\App\Http\Controllers\Admin\EmailTemplatesController::class, 'duplicate'])->name('email-templates.duplicate');
        Route::post('/email-templates/{emailTemplate}/toggle-status', [\App\Http\Controllers\Admin\EmailTemplatesController::class, 'toggleStatus'])->name('email-templates.toggle-status');
        Route::get('/email-templates/{emailTemplate}/preview', [\App\Http\Controllers\Admin\EmailTemplatesController::class, 'preview'])->name('email-templates.preview');
        Route::get('/email-templates/sample-data', [\App\Http\Controllers\Admin\EmailTemplatesController::class, 'sampleData'])->name('email-templates.sample-data');
        
        // SMS Templates Management
        Route::resource('sms-templates', \App\Http\Controllers\Admin\SmsTemplatesController::class);
        Route::post('/sms-templates/{smsTemplate}/duplicate', [\App\Http\Controllers\Admin\SmsTemplatesController::class, 'duplicate'])->name('sms-templates.duplicate');
        Route::get('/sms-templates/{smsTemplate}/preview', [\App\Http\Controllers\Admin\SmsTemplatesController::class, 'preview'])->name('sms-templates.preview');
        Route::post('/sms-templates/{smsTemplate}/test-send', [\App\Http\Controllers\Admin\SmsTemplatesController::class, 'testSend'])->name('sms-templates.test-send');
        
        // SEO Management - Removed
        // Route::get('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('seo.index');
        // Route::post('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'updateConfig'])->name('seo.update');
        // Route::get('/seo/meta-tags', [\App\Http\Controllers\Admin\SeoController::class, 'metaTags'])->name('seo.meta-tags');
        // Route::post('/seo/meta-tags', [\App\Http\Controllers\Admin\SeoController::class, 'updateMetaTags']);
        // Route::put('/seo/meta-tags', [\App\Http\Controllers\Admin\SeoController::class, 'updateMetaTags'])->name('seo.meta-tags.update');
        // Route::get('/seo/sitemap', [\App\Http\Controllers\Admin\SeoController::class, 'sitemap'])->name('seo.sitemap');
        // Route::post('/seo/sitemap/generate', [\App\Http\Controllers\Admin\SeoController::class, 'generateSitemap'])->name('seo.sitemap.generate');
        // Route::get('/seo/sitemap/download', [\App\Http\Controllers\Admin\SeoController::class, 'downloadSitemap'])->name('seo.sitemap.download');
        // Route::get('/seo/robots', [\App\Http\Controllers\Admin\SeoController::class, 'robots'])->name('seo.robots');
        // Route::post('/seo/robots', [\App\Http\Controllers\Admin\SeoController::class, 'updateRobots']);
        // Route::get('/seo/analytics', [\App\Http\Controllers\Admin\SeoController::class, 'analytics'])->name('seo.analytics');
        // Route::get('/seo/pages/create', [\App\Http\Controllers\Admin\SeoController::class, 'createPage'])->name('seo.pages.create');
        // Route::post('/seo/pages', [\App\Http\Controllers\Admin\SeoController::class, 'storePage'])->name('seo.pages.store');
        // Route::get('/seo/pages/{page}/edit', [\App\Http\Controllers\Admin\SeoController::class, 'editPage'])->name('seo.pages.edit');
        // Route::put('/seo/pages/{page}', [\App\Http\Controllers\Admin\SeoController::class, 'updatePage'])->name('seo.pages.update');
        // Route::delete('/seo/pages/{page}', [\App\Http\Controllers\Admin\SeoController::class, 'deletePage'])->name('seo.pages.delete');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/general', [\App\Http\Controllers\Admin\SettingsController::class, 'general'])->name('settings.general');
        Route::post('/settings/general', [\App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        // Email and SMS settings moved to Communication section
        // Use admin.email-config and admin.sms-config routes instead
        Route::get('/settings/security', [\App\Http\Controllers\Admin\SettingsController::class, 'security'])->name('settings.security');
        Route::post('/settings/security', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSecurity'])->name('settings.security.update');
        Route::put('/settings/security', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSecurity']);
        Route::get('/settings/appearance', [\App\Http\Controllers\Admin\SettingsController::class, 'appearance'])->name('settings.appearance');
        Route::post('/settings/appearance', [\App\Http\Controllers\Admin\SettingsController::class, 'updateAppearance'])->name('settings.appearance.update');
        Route::get('/settings/alerts', [\App\Http\Controllers\Admin\SettingsController::class, 'alerts'])->name('settings.alerts');
        Route::post('/settings/alerts', [\App\Http\Controllers\Admin\SettingsController::class, 'updateAlerts'])->name('settings.alerts.update');
        Route::get('/settings/maintenance', [\App\Http\Controllers\Admin\SettingsController::class, 'maintenance'])->name('settings.maintenance');
        Route::post('/settings/maintenance', [\App\Http\Controllers\Admin\SettingsController::class, 'updateMaintenance'])->name('settings.maintenance.update');
        Route::put('/settings/maintenance', [\App\Http\Controllers\Admin\SettingsController::class, 'updateMaintenance']);
        Route::get('/settings/backup', [\App\Http\Controllers\Admin\SettingsController::class, 'backup'])->name('settings.backup');
        Route::post('/settings/backup', [\App\Http\Controllers\Admin\SettingsController::class, 'updateBackup'])->name('settings.backup.update');
        Route::put('/settings/backup', [\App\Http\Controllers\Admin\SettingsController::class, 'updateBackup']);
        Route::get('/settings/security-logs', [\App\Http\Controllers\Admin\SettingsController::class, 'securityLogs'])->name('settings.security-logs');
        Route::get('/settings/system-info', [\App\Http\Controllers\Admin\SettingsController::class, 'systemInfo'])->name('settings.system-info'); // Route name: admin.settings.system-info
        Route::get('/settings/php-info', [\App\Http\Controllers\Admin\SettingsController::class, 'phpInfo'])->name('settings.php-info');
        
        // Role-Based Menu Visibility
        Route::get('/settings/role-menu-visibility', [\App\Http\Controllers\Admin\RoleMenuVisibilityController::class, 'index'])->name('role-menu-visibility.index');
        Route::post('/settings/role-menu-visibility', [\App\Http\Controllers\Admin\RoleMenuVisibilityController::class, 'store'])->name('role-menu-visibility.store');
        Route::post('/settings/role-menu-visibility/reset', [\App\Http\Controllers\Admin\RoleMenuVisibilityController::class, 'reset'])->name('role-menu-visibility.reset');
        
        // Custom Menu Items
        Route::resource('custom-menu-items', \App\Http\Controllers\Admin\CustomMenuItemController::class)->except(['show']);
        // Note: The resource route above already creates the index route, so we don't need a duplicate
        
        // Settings API Routes
        Route::post('/settings/test-email', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('settings.test-email');
        Route::post('/settings/test-sms', [\App\Http\Controllers\Admin\SettingsController::class, 'testSms'])->name('settings.test-sms');
        Route::post('/settings/test-security', [\App\Http\Controllers\Admin\SettingsController::class, 'testSecurity'])->name('settings.test-security');
        Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::post('/settings/optimize', [\App\Http\Controllers\Admin\SettingsController::class, 'optimize'])->name('settings.optimize');
        Route::get('/settings/download-logs', [\App\Http\Controllers\Admin\SettingsController::class, 'downloadLogs'])->name('settings.download-logs');
        Route::post('/settings/create-backup', [\App\Http\Controllers\Admin\SettingsController::class, 'createBackup'])->name('settings.create-backup');
        Route::post('/settings/restore-backup', [\App\Http\Controllers\Admin\SettingsController::class, 'restoreBackup'])->name('settings.restore-backup');
        Route::get('/settings/backup/{id}/download', [\App\Http\Controllers\Admin\SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
        Route::delete('/settings/backup/{id}', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteBackup'])->name('settings.backup.delete');
        Route::delete('/settings/session/{sessionId}', [\App\Http\Controllers\Admin\SettingsController::class, 'terminateSession'])->name('settings.session.terminate');
        Route::post('/settings/terminate-all-sessions', [\App\Http\Controllers\Admin\SettingsController::class, 'terminateAllSessions'])->name('settings.terminate-all-sessions');
        Route::get('/settings/session/{sessionId}/details', [\App\Http\Controllers\Admin\SettingsController::class, 'getSessionDetails'])->name('settings.session.details');
        
        
        // Email Management
        Route::prefix('email-management')->name('email-management.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmailManagementController::class, 'index'])->name('index');
            Route::get('/logs', [\App\Http\Controllers\Admin\EmailManagementController::class, 'logs'])->name('logs');
            Route::get('/{emailLog}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'show'])->name('show');
            Route::get('/statistics', [\App\Http\Controllers\Admin\EmailManagementController::class, 'statistics'])->name('statistics');
            Route::post('/resend/{id}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'resendEmail'])->name('resend');
            Route::delete('/logs/{id}', [\App\Http\Controllers\Admin\EmailManagementController::class, 'deleteEmailLog'])->name('logs.delete');
            Route::post('/settings', [\App\Http\Controllers\Admin\EmailManagementController::class, 'updateSettings'])->name('settings.update');
            Route::post('/test-email', [\App\Http\Controllers\Admin\EmailManagementController::class, 'sendTestEmail'])->name('test');
        });
        
        // Admin Notifications
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/admin', [\App\Http\Controllers\Admin\NotificationController::class, 'getAdminNotifications'])->name('notifications.admin');
        Route::get('/notifications/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'view'])->name('notifications.view');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/mark-read/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');
        
        // Advanced Reports Management
        Route::prefix('advanced-reports')->name('advanced-reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'index'])->name('index');
            Route::get('/custom-reports', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'customReports'])->name('custom-reports');
            Route::post('/custom-reports/generate', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'generateCustomReport'])->name('custom-reports.generate');
            Route::post('/custom-reports/save', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'saveCustomReport'])->name('custom-reports.save');
            Route::get('/financial-analytics', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'financialAnalytics'])->name('financial-analytics');
            Route::get('/patient-analytics', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'patientAnalytics'])->name('patient-analytics');
            Route::get('/doctor-analytics', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'doctorAnalytics'])->name('doctor-analytics');
            Route::get('/export/{reportId}', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'exportReport'])->name('export');
            Route::get('/tables', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'getAvailableTables'])->name('tables');
            Route::get('/columns/{table}', [\App\Http\Controllers\Admin\AdvancedReportsController::class, 'getTableColumns'])->name('columns');
            
            // Audit Trail
            Route::get('/audit-trail', [\App\Http\Controllers\Admin\AuditTrailController::class, 'index'])->name('audit-trail');
            Route::get('/audit-trail/{auditLog}', [\App\Http\Controllers\Admin\AuditTrailController::class, 'show'])->name('audit-trail.show');
            Route::post('/audit-trail/cleanup', [\App\Http\Controllers\Admin\AuditTrailController::class, 'cleanup'])->name('audit-trail.cleanup');
            Route::get('/audit-trail/export', [\App\Http\Controllers\Admin\AuditTrailController::class, 'export'])->name('audit-trail.export');
        });
        
        // Email Template Seeder Tool (for shared hosting without SSH)
        Route::prefix('tools')->name('tools.')->group(function () {
            Route::get('/email-template-seeder', [\App\Http\Controllers\Admin\EmailTemplateSeedController::class, 'index'])->name('email-template-seeder');
            Route::post('/email-template-seeder/seed', [\App\Http\Controllers\Admin\EmailTemplateSeedController::class, 'seed'])->name('email-template-seeder.seed');
            Route::post('/email-template-seeder/diagnose', [\App\Http\Controllers\Admin\EmailTemplateSeedController::class, 'diagnose'])->name('email-template-seeder.diagnose');
            Route::post('/email-template-seeder/clear-cache', [\App\Http\Controllers\Admin\EmailTemplateSeedController::class, 'clearCache'])->name('email-template-seeder.clear-cache');
            Route::post('/email-template-seeder/repair', [\App\Http\Controllers\Admin\EmailTemplateSeedController::class, 'repairTemplates'])->name('email-template-seeder.repair');
        });
        
        // Profile Management
    });
    
});

// Storage access route for shared hosting environments without symlinks
Route::get('/storage-access/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($fullPath);
    
    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000', // Cache for 1 year
    ]);
})->where('path', '.*')->name('storage.access');

// Fallback Route - Handle 404s properly based on installation status
Route::fallback(function () {
    if (!File::exists(storage_path('installed'))) {
        return redirect()->route('install.index');
    }
    abort(404);
});

require __DIR__.'/auth.php'; // Email verification routes
// require __DIR__.'/user.php'; // Commented out until User controllers are created
