<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Services\NotificationService;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BillingsController extends Controller
{
    protected NotificationService $notificationService;
    protected HospitalEmailNotificationService $emailService;

    public function __construct(NotificationService $notificationService, HospitalEmailNotificationService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Get the current user's department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        // Admins should see all doctors regardless of department
        if ($user->role === 'admin' || ($user->is_admin ?? false)) {
            return null;
        }
        
        // For doctors, get department from doctors table
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor ? $doctor->department_id : null;
        }
        
        // For other roles (nurse, staff, etc.), get from users table
        return $user->department_id;
    }

    /**
     * Display a listing of billings.
     */
    public function index(Request $request): View
    {
        $query = Billing::with(['patient', 'doctor', 'appointment', 'createdBy', 'invoice']);

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = parseDateInput($request->date_from);
            $query->where('billing_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = parseDateInput($request->date_to);
            $query->where('billing_date', '<=', $dateTo);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $billings = $query->orderBy('created_at', 'desc')->paginate(15);

        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();

        $stats = [
            'total' => Billing::count(),
            'pending' => Billing::where('status', 'pending')->count(),
            'paid' => Billing::where('status', 'paid')->count(),
            'overdue' => Billing::overdue()->count(),
            'total_amount' => Billing::sum('total_amount'),
            'paid_amount' => Billing::sum('paid_amount'),
            'outstanding' => Billing::sum('balance'),
            'this_month' => Billing::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.billing.index', compact('billings', 'patients', 'doctors', 'stats'))->with('bills', $billings);
    }

    /**
     * Show the form for creating a new billing.
     */
    public function create(Request $request): View
    {
        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();
        $appointments = [];
        
        // If appointment_id is provided, pre-select appointment
        $selectedAppointment = null;
        if ($request->filled('appointment_id')) {
            $selectedAppointment = Appointment::with(['patient', 'doctor'])->find($request->appointment_id);
            if ($selectedAppointment) {
                $appointments = [$selectedAppointment];
            }
        }

        return view('admin.billing.create', compact('patients', 'doctors', 'appointments', 'selectedAppointment'));
    }

    /**
     * Store a newly created billing.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'billing_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:billing_date',
            'type' => 'required|string|in:consultation,procedure,medication,lab_test,other',
            'description' => 'required|string|max:500',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $subtotal = $request->subtotal;
        $discount = $request->discount ?? 0;
        $tax = $request->tax ?? 0;
        $totalAmount = $subtotal - $discount + $tax;

        $billing = Billing::create([
            'bill_number' => Billing::generateBillNumber(),
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_id' => $request->appointment_id,
            'billing_date' => $request->billing_date,
            'due_date' => $request->due_date,
            'type' => $request->type,
            'description' => $request->description,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'balance' => $totalAmount,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Send billing notification to patient if requested
        if ($request->has('send_to_patient') && $request->send_to_patient) {
            try {
                $this->emailService->sendBillingNotification($billing);
            } catch (\Exception $e) {
                \Log::warning('Failed to send billing notification email', [
                    'billing_id' => $billing->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Send in-portal notification to patient about new invoice
        try {
            $this->notificationService->sendBillingNotification($billing, 'invoice_created');
        } catch (\Exception $e) {
            // Log the error but don't fail the billing creation
            \Log::warning('Failed to send portal notification: ' . $e->getMessage());
        }
        
        // Send email notification to patient about new invoice
        if (config('hospital.notifications.payment_reminder.enabled', true)) {
            try {
                $billing->load(['patient']);
                $billingInfo = [
                    'invoice_number' => $billing->id,
                    'amount_due' => number_format($billing->total_amount, 2),
                    'due_date' => $billing->due_date ? $billing->due_date->format('F d, Y') : date('F d, Y', strtotime('+30 days')),
                    'service_description' => $billing->description,
                ];
                $this->emailService->sendPaymentReminder($billing->patient, $billingInfo);
            } catch (\Exception $e) {
                // Log the error but don't fail the billing creation
                \Log::error('Failed to send billing email notification', [
                    'billing_id' => $billing->id,
                    'patient_id' => $billing->patient_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('admin.billing.index')
            ->with('success', 'Billing record created successfully!');
    }

    /**
     * Display the specified billing.
     */
    public function show(Billing $billing): View
    {
        $billing->load(['patient', 'doctor', 'appointment', 'createdBy', 'updatedBy']);
        
        return view('admin.billing.show', compact('billing'));
    }

    /**
     * Show the form for editing the billing.
     */
    public function edit(Billing $billing): View
    {
        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();
        
        return view('admin.billing.edit', compact('billing', 'patients', 'doctors'));
    }

    /**
     * Update the specified billing.
     */
    public function update(Request $request, Billing $billing): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'billing_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:billing_date',
            'type' => 'required|string|in:consultation,procedure,medication,lab_test,other',
            'description' => 'required|string|max:500',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $subtotal = $request->subtotal;
        $discount = $request->discount ?? 0;
        $tax = $request->tax ?? 0;
        $totalAmount = $subtotal - $discount + $tax;

        $billing->update([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_id' => $request->appointment_id,
            'billing_date' => $request->billing_date,
            'due_date' => $request->due_date,
            'type' => $request->type,
            'description' => $request->description,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'balance' => $totalAmount - $billing->paid_amount,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.billing.index')
            ->with('success', 'Billing record updated successfully!');
    }

    /**
     * Remove the specified billing.
     */
    public function destroy(Billing $billing): RedirectResponse
    {
        $patientName = $billing->patient->full_name;
        $billing->delete();

        return redirect()->route('admin.billing.index')
            ->with('success', "Billing record for {$patientName} deleted successfully!");
    }

    /**
     * Show the payment processing page for billing.
     */
    public function payment(Billing $billing): View
    {
        $billing->load(['patient', 'doctor', 'appointment', 'createdBy']);
        
        // Check if bill is already fully paid
        if ($billing->status === 'paid') {
            return redirect()->route('admin.billing.index')
                ->with('info', 'This bill is already fully paid.');
        }
        
        // Use 'bill' variable name to match the view expectations
        $bill = $billing;
        return view('admin.billing.payment', compact('bill'));
    }

    /**
     * Process payment for billing.
     */
    public function processPayment(Request $request, Billing $billing)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $billing->balance,
            'payment_method' => 'required|string|in:cash,card,insurance,bank_transfer',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $newPaidAmount = $billing->paid_amount + $request->payment_amount;
        
        $billing->update([
            'paid_amount' => $newPaidAmount,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
            'updated_by' => auth()->id(),
        ]);

        // Fresh billing instance will automatically sync with invoice via model events
        $freshBilling = $billing->fresh();

        // Send payment notification to admins when payment is processed
        try {
            $this->notificationService->sendBillingNotification(
                $freshBilling,
                'payment_received',
                $request->payment_amount
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the payment processing
            \Log::warning('Failed to send payment notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully! Invoice updated for patient portal.',
            'paid_amount' => $freshBilling->paid_amount,
            'balance' => $freshBilling->balance,
            'status' => $freshBilling->status,
            'invoice_synced' => $freshBilling->invoice ? true : false,
        ]);
    }

    /**
     * Update billing status.
     */
    public function updateStatus(Request $request, Billing $billing)
    {
        $request->validate([
            'status' => 'required|in:pending,partial,paid,overdue,cancelled'
        ]);

        $billing->update([
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Billing status updated successfully!',
            'status' => $billing->status
        ]);
    }

    /**
     * Generate invoice PDF.
     */
    public function generateInvoice(Billing $billing)
    {
        $billing->load(['patient', 'doctor', 'appointment', 'createdBy']);
        
        return view('admin.billing.invoice', compact('billing'));
    }

    /**
     * Send billing notification to patient via email
     */
    public function sendToPatient(Billing $billing)
    {
        try {
            // Ensure billing has all relationships loaded
            $billing->load(['patient', 'doctor', 'invoice']);
            
            // Check if patient has email
            if (!$billing->patient || !$billing->patient->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient email not found. Please ensure the patient has a valid email address.'
                ], 400);
            }
            
            $result = $this->emailService->sendBillingNotification($billing);
            
            if ($result) {
                // Check actual email status
                $result->refresh();
                $emailStatus = $result->status ?? 'unknown';
                
                if ($emailStatus === 'sent') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Billing notification sent to patient successfully! Please check spam folder if email is not received.',
                        'email_log_id' => $result->id,
                        'status' => $emailStatus
                    ]);
                } else {
                    $errorMsg = $result->error_message ?? 'Email sending failed. Status: ' . $emailStatus;
                    return response()->json([
                        'success' => false,
                        'message' => 'Email sending failed: ' . $errorMsg . '. Please check SMTP configuration in Admin > Email Settings.',
                        'email_log_id' => $result->id,
                        'status' => $emailStatus
                    ], 500);
                }
            } else {
                // Get the last error from logs to provide more specific message
                $errorMessage = 'Failed to send email. ';
                $errorMessage .= 'Please check: 1) Email template exists (billing_notification), 2) SMTP configuration in Admin > Email Settings, 3) Check logs for details.';
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send billing notification', [
                'billing_id' => $billing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync details for a billing record
     */
    public function getSyncDetails(Billing $billing)
    {
        $invoice = $billing->invoice;
        
        if (!$invoice) {
            return response()->json([
                'error' => 'This bill is not synchronized with patient portal'
            ], 404);
        }

        $paymentsTotal = $invoice->payments()->sum('amount');
        $lastPayment = $invoice->payments()->latest()->first();

        return response()->json([
            'bill_number' => $billing->bill_number,
            'patient_name' => $billing->patient->full_name,
            'total_amount' => number_format($billing->total_amount, 2),
            'status' => ucfirst(str_replace('_', ' ', $billing->status)),
            'invoice_number' => $invoice->invoice_number,
            'invoice_status' => ucfirst(str_replace('_', ' ', $invoice->status)),
            'payments_made' => number_format($paymentsTotal, 2),
            'last_payment_date' => $lastPayment ? $lastPayment->created_at->format('M d, Y') : null,
        ]);
    }

    /**
     * Manually sync a billing record with patient portal
     */
    public function manualSync(Billing $billing)
    {
        try {
            $billing->syncToInvoice();
            
            return response()->json([
                'success' => true,
                'message' => 'Billing record synchronized successfully with patient portal'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync billing record: ' . $e->getMessage()
            ], 500);
        }
    }
}
