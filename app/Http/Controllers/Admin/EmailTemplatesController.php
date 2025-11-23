<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailTemplatesController extends Controller
{
    /**
     * Display a listing of email templates
     */
    public function index(Request $request)
    {
        try {
            // Build base query for filtering
            $baseQuery = EmailTemplate::query();
            
            // Filter by role if provided
            if ($request->filled('role')) {
                $baseQuery->forRoles($request->role);
            }
            
            // Filter by category if provided
            if ($request->filled('category')) {
                $baseQuery->ofCategory($request->category);
            }
            
            // Get paginated templates for the listing
            $templates = (clone $baseQuery)->orderBy('name')->paginate(15);
            
            // Preserve query parameters in pagination links
            $templates->appends($request->only(['role', 'category']));
            
            // Get all filtered templates (without pagination) for grouping
            // Only if no filters are applied, otherwise show paginated table
            $templatesByRole = collect([]);
            if (!$request->has('role') && !$request->has('category')) {
                $filteredTemplates = EmailTemplate::orderBy('name')->get();
                
                // Group templates by role
                $templatesByRole = $filteredTemplates->groupBy(function($template) {
                    if (empty($template->target_roles)) {
                        return 'all';
                    }
                    // If multiple roles, use the first one for grouping, or join them
                    return is_array($template->target_roles) 
                        ? implode(', ', $template->target_roles) 
                        : 'all';
                });
            }
            
            // Get available roles for filter
            $availableRoles = EmailTemplate::getAvailableRoles();
            
        } catch (\Exception $e) {
            \Log::error('EmailTemplate index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Fallback to empty collection
            $templates = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, 15, 1, ['path' => request()->url()]
            );
            $templatesByRole = collect([]);
            $availableRoles = [];
        }
        
        return view('admin.communication.email-templates.index', compact('templates', 'templatesByRole', 'availableRoles'));
    }
    

    /**
     * Show the form for creating a new email template
     */
    public function create()
    {
        return view('admin.communication.email-templates.create');
    }

    /**
     * Store a newly created email template in storage
     */
    public function store(Request $request)
    {
        // Simplify validation temporarily to isolate the issue
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string',
            'body' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Debug logging
            \Log::info('Creating email template with data:', $request->all());
            \Log::info('Fillable fields:', (new EmailTemplate)->getFillable());
            
            // Check required fields
            $requestData = $request->all();
            foreach (['name', 'subject', 'body', 'category', 'status'] as $required) {
                if (empty($requestData[$required])) {
                    \Log::warning("Missing required field: {$required}");
                }
            }
            
            // Clean data for database insertion
            $cleanData = [];
            
            // Handle required fields
            $cleanData['name'] = $requestData['name'];
            $cleanData['subject'] = $requestData['subject'];
            $cleanData['body'] = $requestData['body'];
            $cleanData['category'] = $requestData['category'];
            $cleanData['status'] = $requestData['status'];
            
            // Handle optional text fields - set to null if empty
            $cleanData['description'] = !empty($requestData['description']) ? $requestData['description'] : null;
            $cleanData['sender_name'] = !empty($requestData['sender_name']) ? $requestData['sender_name'] : null;
            $cleanData['sender_email'] = !empty($requestData['sender_email']) ? $requestData['sender_email'] : null;
            
            // Handle JSON fields - ensure they're properly formatted or null
            $cleanData['variables'] = null;
            if (!empty($requestData['variables']) && is_array($requestData['variables'])) {
                $cleanData['variables'] = $requestData['variables'];
            }
            
            // Set other JSON fields to null for now
            $cleanData['cc_emails'] = null;
            $cleanData['bcc_emails'] = null;
            $cleanData['attachments'] = null;
            $cleanData['metadata'] = null;
            
            \Log::info('Clean data for creation:', $cleanData);
            
            $template = EmailTemplate::create($cleanData);
            
            \Log::info('Email template created successfully', ['id' => $template->id]);

            return redirect()->route('admin.email-templates.index')
                ->with('success', 'Email template created successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to create email template: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'sql_error' => $e instanceof \Illuminate\Database\QueryException ? $e->errorInfo : null
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create email template. Please try again. Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified email template
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return view('admin.communication.email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified email template
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.communication.email-templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified email template in storage
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id . ',id,deleted_at,NULL',
            'subject' => 'required|string|max:500', // Increased limit for compatibility
            'body' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'status' => 'required|in:active,inactive,draft,archived', // Added 'archived' status
            'sender_name' => 'nullable|string|max:255',
            'sender_email' => 'nullable|email|max:255',
            'variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $emailTemplate->update($request->all());

            return redirect()->route('admin.email-templates.index')
                ->with('success', 'Email template updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update email template. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified email template from storage
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        try {
            $emailTemplate->delete();

            return redirect()->route('admin.email-templates.index')
                ->with('success', 'Email template deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete email template. Please try again.');
        }
    }

    /**
     * Duplicate an email template
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        try {
            $duplicate = $emailTemplate->replicate();
            $duplicate->name = $emailTemplate->name . ' (Copy)';
            $duplicate->status = 'draft';
            $duplicate->save();

            return redirect()->route('admin.email-templates.index')
                ->with('success', 'Email template duplicated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to duplicate email template. Please try again.');
        }
    }

    /**
     * Toggle status of an email template (activate/deactivate)
     */
    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        try {
            // Toggle between active and inactive
            $newStatus = $emailTemplate->status === 'active' ? 'inactive' : 'active';
            $emailTemplate->update(['status' => $newStatus]);

            $statusText = $newStatus === 'active' ? 'activated' : 'deactivated';

            return redirect()->route('admin.email-templates.index')
                ->with('success', "Email template {$statusText} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle email template status. Please try again.');
        }
    }

    /**
     * Preview an email template with sample data
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        $emailService = new EmailService();
        
        // Sample data for preview
        $sampleData = $this->getSampleShortcodeData();

        try {
            $preview = $emailService->previewTemplate($emailTemplate->name, $sampleData);
            
            return response()->json([
                'success' => true,
                'preview' => $preview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get sample shortcode data for demonstration
     */
    public function getSampleShortcodeData()
    {
        return [
            // Patient Information
            'patient_name' => 'John Doe',
            'pname' => 'John Doe',
            'patient_first_name' => 'John',
            'pfn' => 'John',
            'patient_last_name' => 'Doe',
            'pln' => 'Doe',
            'patient_id' => 'P001234',
            'pid' => 'P001234',
            'patient_email' => 'john.doe@example.com',
            'pemail' => 'john.doe@example.com',
            'patient_phone' => '+233 123 456 789',
            'pphone' => '+233 123 456 789',
            'patient_age' => '45 years',
            'page' => '45 years',
            'patient_gender' => 'Male',
            'pgender' => 'Male',
            'patient_address' => '123 Oak Street, Accra, Ghana',
            'paddress' => '123 Oak Street, Accra, Ghana',
            'patient_date_of_birth' => 'March 15, 1978',
            'pdob' => 'March 15, 1978',

            // Doctor Information
            'doctor_name' => 'Dr. Sarah Johnson',
            'dname' => 'Dr. Sarah Johnson',
            'doctor_first_name' => 'Sarah',
            'dfn' => 'Sarah',
            'doctor_last_name' => 'Johnson',
            'dln' => 'Johnson',
            'doctor_title' => 'Dr.',
            'dtitle' => 'Dr.',
            'doctor_specialization' => 'Cardiology',
            'dspec' => 'Cardiology',
            'doctor_phone' => '+233 123 456 700',
            'dphone' => '+233 123 456 700',
            'doctor_email' => 'dr.johnson@hospital.com',
            'demail' => 'dr.johnson@hospital.com',
            'doctor_qualification' => 'MD, PhD, FACC',
            'dqual' => 'MD, PhD, FACC',

            // Appointment Details
            'appointment_id' => 'APT001234',
            'aid' => 'APT001234',
            'appointment_date' => 'January 15, 2024',
            'adate' => 'January 15, 2024',
            'appointment_time' => '10:30 AM',
            'atime' => '10:30 AM',
            'appointment_datetime' => 'January 15, 2024 at 10:30 AM',
            'adt' => 'January 15, 2024 at 10:30 AM',
            'appointment_duration' => '30 minutes',
            'adur' => '30 minutes',
            'appointment_type' => 'Consultation',
            'atype' => 'Consultation',
            'appointment_status' => 'Confirmed',
            'astatus' => 'Confirmed',
            'department' => 'Cardiology Department',
            'dept' => 'Cardiology Department',
            'room_number' => 'Room 305',
            'room' => 'Room 305',
            'floor' => '3rd Floor',
            'appointment_notes' => 'Please arrive 15 minutes early for check-in',
            'anotes' => 'Please arrive 15 minutes early for check-in',

            // Hospital Information
            'hospital_name' => 'ThankDoc EHR',
            'hname' => 'ThankDoc EHR',
            'hospital_phone' => '+233 302 123 456',
            'hphone' => '+233 302 123 456',
            'hospital_email' => 'info@newwaveshospital.com',
            'hemail' => 'info@newwaveshospital.com',
            'hospital_address' => '123 Healthcare Avenue, Accra, Ghana',
            'haddress' => '123 Healthcare Avenue, Accra, Ghana',
            'hospital_website' => 'https://www.newwaveshospital.com',
            'hsite' => 'https://www.newwaveshospital.com',
            'hospital_logo' => url('/images/logo.png'),
            'hlogo' => url('/images/logo.png'),
            'emergency_number' => '+233 302 EMERGENCY',
            'emnum' => '+233 302 EMERGENCY',

            // Billing Information
            'invoice_number' => 'INV-2024-001234',
            'invnum' => 'INV-2024-001234',
            'invoice_date' => 'January 10, 2024',
            'invdate' => 'January 10, 2024',
            'invoice_amount' => 'GHS 450.00',
            'invamt' => 'GHS 450.00',
            'amount_due' => 'GHS 450.00',
            'amtdue' => 'GHS 450.00',
            'payment_due_date' => 'February 10, 2024',
            'paydue' => 'February 10, 2024',
            'payment_method' => 'Credit Card',
            'paymethod' => 'Credit Card',
            'insurance_company' => 'National Health Insurance',
            'insco' => 'National Health Insurance',
            'payment_url' => url('/patient/billing/pay'),
            'payurl' => url('/patient/billing/pay'),

            // Prescription Information
            'prescription_id' => 'RX001234',
            'rxid' => 'RX001234',
            'medication_name' => 'Lisinopril 10mg',
            'medname' => 'Lisinopril 10mg',
            'dosage' => '10mg',
            'dose' => '10mg',
            'frequency' => 'Once daily',
            'freq' => 'Once daily',
            'prescription_date' => 'January 10, 2024',
            'rxdate' => 'January 10, 2024',
            'pharmacy_name' => 'ThankDoc Pharmacy',
            'pharm' => 'ThankDoc Pharmacy',

            // Lab Results
            'test_name' => 'Complete Blood Count (CBC)',
            'testname' => 'Complete Blood Count (CBC)',
            'test_results' => 'Normal - All values within normal range',
            'results' => 'Normal - All values within normal range',
            'test_date' => 'January 8, 2024',
            'testdate' => 'January 8, 2024',
            'reference_range' => 'Normal ranges provided in report',
            'refrange' => 'Normal ranges provided in report',
            'test_status' => 'Normal',
            'tstatus' => 'Normal',

            // Staff Information
            'staff_name' => 'Nurse Mary Wilson',
            'sname' => 'Nurse Mary Wilson',
            'staff_position' => 'Head Nurse',
            'spos' => 'Head Nurse',
            'staff_department' => 'Emergency Department',
            'sdept' => 'Emergency Department',
            'staff_phone' => '+233 123 456 750',
            'sphone' => '+233 123 456 750',
            'staff_email' => 'mary.wilson@hospital.com',
            'semail' => 'mary.wilson@hospital.com',

            // System Variables
            'site_url' => config('app.url'),
            'site' => config('app.url'),
            'current_date' => now()->format('F d, Y'),
            'date' => now()->format('F d, Y'),
            'current_time' => now()->format('g:i A'),
            'time' => now()->format('g:i A'),
            'current_datetime' => now()->format('F d, Y g:i A'),
            'now' => now()->format('F d, Y g:i A'),
            'current_year' => now()->year,
            'year' => now()->year,
            'login_url' => url('/patient/login'),
            'login' => url('/patient/login'),
            'support_email' => 'support@newwaveshospital.com',
            'support' => 'support@newwaveshospital.com',
            'contact_us_url' => url('/contact'),
            'contact' => url('/contact'),
            'unsubscribe_url' => url('/unsubscribe'),
            'unsub' => url('/unsubscribe'),
        ];
    }

    /**
     * Get sample shortcode data as JSON for AJAX requests
     */
    public function sampleData()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSampleShortcodeData()
        ]);
    }
}
