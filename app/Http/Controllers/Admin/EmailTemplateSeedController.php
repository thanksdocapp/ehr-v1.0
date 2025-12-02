<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmailTemplateSeedController extends Controller
{
    /**
     * Show the email template seeder page
     */
    public function index()
    {
        // Check if user has admin access
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Access denied. Admin access required.');
        }
        
        $diagnostics = $this->runDiagnostics();
        
        return view('admin.tools.email-template-seeder', compact('diagnostics'));
    }
    
    /**
     * Seed email templates via web interface
     */
    public function seed(Request $request)
    {
        // Check if user has admin access
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin access required.'
            ], 403);
        }
        
        try {
            $templates = $this->getEmailTemplates();
            
            $created = 0;
            $updated = 0;
            $errors = 0;
            $results = [];
            
            foreach ($templates as $template) {
                try {
                    $emailTemplate = EmailTemplate::where('name', $template['name'])->first();
                    
                    if ($emailTemplate) {
                        $emailTemplate->update($template);
                        $updated++;
                        $results[] = "✓ Updated: {$template['name']}";
                    } else {
                        // Use raw SQL to avoid MySQL strict mode issues with soft deletes
                        $templateData = $template;
                        $templateData['created_at'] = now();
                        $templateData['updated_at'] = now();
                        $templateData['variables'] = json_encode($template['variables']);
                        
                        // Use query builder with explicit NULL for deleted_at
                        DB::table('email_templates')->insert([
                            'name' => $templateData['name'],
                            'subject' => $templateData['subject'],
                            'category' => $templateData['category'],
                            'status' => $templateData['status'],
                            'description' => $templateData['description'],
                            'body' => $templateData['body'],
                            'variables' => $templateData['variables'],
                            'sender_name' => $templateData['sender_name'],
                            'sender_email' => $templateData['sender_email'],
                            'created_at' => $templateData['created_at'],
                            'updated_at' => $templateData['updated_at'],
                            'deleted_at' => null
                        ]);
                        
                        $created++;
                        $results[] = "✓ Created: {$template['name']}";
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $results[] = "✗ Error with {$template['name']}: " . $e->getMessage();
                    continue;
                }
            }
            
            $totalCount = EmailTemplate::count();
            
            return response()->json([
                'success' => true,
                'message' => 'Email templates seeded successfully!',
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'total_templates' => $totalCount,
                    'results' => $results
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error seeding email templates: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Run diagnostics via web interface
     */
    public function diagnose(Request $request)
    {
        // Check if user has admin access
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin access required.'
            ], 403);
        }
        
        $diagnostics = $this->runDiagnostics();
        
        return response()->json([
            'success' => true,
            'data' => $diagnostics
        ]);
    }
    
    /**
     * Clear cache via web interface
     */
    public function clearCache(Request $request)
    {
        // Check if user has admin access
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin access required.'
            ], 403);
        }
        
        try {
            // Clear various caches
            \Artisan::call('config:clear');
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Repair soft-deleted email templates by setting deleted_at to NULL
     */
    public function repairTemplates(Request $request)
    {
        // Check if user has admin access
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin access required.'
            ], 403);
        }
        
        try {
            // Check current state
            $totalRecords = DB::table('email_templates')->count();
            $activeRecords = DB::table('email_templates')->whereNull('deleted_at')->count();
            $deletedRecords = DB::table('email_templates')->whereNotNull('deleted_at')->count();
            
            // Fix soft-deleted templates by setting deleted_at to NULL
            $updatedCount = DB::table('email_templates')
                ->whereNotNull('deleted_at')
                ->update(['deleted_at' => null]);
                
            // Clear model cache
            EmailTemplate::clearBootedModels();
            
            // Verify the fix
            $newActiveRecords = DB::table('email_templates')->whereNull('deleted_at')->count();
            $eloquentCount = EmailTemplate::count();
            
            return response()->json([
                'success' => true,
                'message' => "Templates repaired successfully!",
                'details' => [
                    'total_records' => $totalRecords,
                    'previously_active' => $activeRecords,
                    'previously_deleted' => $deletedRecords,
                    'records_repaired' => $updatedCount,
                    'now_active' => $newActiveRecords,
                    'eloquent_count' => $eloquentCount
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template repair failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Run diagnostics and return results
     */
    private function runDiagnostics()
    {
        $diagnostics = [];
        
        // 1. Database connection
        try {
            DB::connection()->getPdo();
            $diagnostics['database'] = [
                'status' => 'success',
                'message' => 'Database connection successful',
                'connection' => config('database.default')
            ];
        } catch (\Exception $e) {
            $diagnostics['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
        
        // 2. Email templates table
        try {
            if (Schema::hasTable('email_templates')) {
                $diagnostics['table'] = [
                    'status' => 'success',
                    'message' => 'Email templates table exists',
                    'columns' => Schema::getColumnListing('email_templates')
                ];
            } else {
                $diagnostics['table'] = [
                    'status' => 'error',
                    'message' => 'Email templates table does not exist'
                ];
            }
        } catch (\Exception $e) {
            $diagnostics['table'] = [
                'status' => 'error',
                'message' => 'Error checking table: ' . $e->getMessage()
            ];
        }
        
        // 3. Email template count
        try {
            // Clear model cache first
            EmailTemplate::clearBootedModels();
            
            // Use both raw count and Eloquent count for comparison
            $rawCount = DB::table('email_templates')->whereNull('deleted_at')->count();
            $eloquentCount = EmailTemplate::count();
            
            $diagnostics['templates'] = [
                'status' => $rawCount > 0 ? 'success' : 'warning',
                'message' => "Found {$rawCount} email templates (Raw: {$rawCount}, Eloquent: {$eloquentCount})",
                'count' => $rawCount,
                'raw_count' => $rawCount,
                'eloquent_count' => $eloquentCount
            ];
            
            if ($rawCount > 0) {
                // Use raw SQL to get templates to avoid any Eloquent caching issues
                $templates = DB::table('email_templates')
                    ->select('name', 'subject', 'status', 'category')
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();
                $diagnostics['templates']['list'] = $templates;
            }
        } catch (\Exception $e) {
            $diagnostics['templates'] = [
                'status' => 'error',
                'message' => 'Error querying email templates: ' . $e->getMessage()
            ];
        }
        
        // 4. Environment info
        $diagnostics['environment'] = [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'php_version' => PHP_VERSION
        ];
        
        return $diagnostics;
    }
    
    /**
     * Get email templates array
     */
    private function getEmailTemplates()
    {
        return [
            [
                'name' => 'appointment_confirmation',
                'subject' => 'Appointment Confirmation - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients when their appointment is confirmed',
                'body' => 'Dear {{patient_name}},\n\nYour appointment has been confirmed with the following details:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\nLocation: {{hospital_address}}\n\nAdditional Notes:\n{{notes}}\n\nPlease arrive 15 minutes early for check-in.\n\nImportant reminders:\n- Bring your ID and insurance card\n- Bring a list of current medications\n- Inform us of any changes to your health status\n\nIf you need to cancel or reschedule, please contact us at {{hospital_phone}} at least 24 hours in advance.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'notes' => 'Additional appointment notes',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'appointment_reminder',
                'subject' => 'Appointment Reminder - {{appointment_date}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'Sent to patients 24 hours before their appointment',
                'body' => 'Dear {{patient_name}},\n\nThis is a friendly reminder about your upcoming appointment:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n\nLocation: {{hospital_name}}\n{{hospital_address}}\n\nPlease remember to:\n✓ Arrive 15 minutes early\n✓ Bring your ID and insurance card\n✓ Bring your current medications list\n\nIf you need to cancel or reschedule, please call us at {{hospital_phone}} as soon as possible.\n\nThank you,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'test_results_ready',
                'subject' => 'Your Test Results Are Ready - {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to patients when their test results are available',
                'body' => 'Dear {{patient_name}},\n\nYour test results from {{test_date}} are now ready for review.\n\nTest Type: {{test_type}}\nOrdered by: {{doctor_name}}\n\nTo access your results, please:\n1. Log into your patient portal at {{portal_url}}\n2. Navigate to "Lab Results"\n3. Review the results and any doctor notes\n\nIf you have any questions about your results, please contact your doctor\'s office at {{doctor_phone}} or schedule a follow-up appointment.\n\nImportant: These results are confidential and should only be accessed by you or your authorized healthcare proxy.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Laboratory Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'test_date' => 'Test date',
                    'test_type' => 'Type of test',
                    'doctor_name' => 'Ordering doctor\'s name',
                    'doctor_phone' => 'Doctor\'s phone number',
                    'portal_url' => 'Patient portal URL',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Laboratory',
                'sender_email' => 'lab@hospital.com'
            ],
            [
                'name' => 'patient_welcome',
                'subject' => 'Welcome to {{hospital_name}} - Your Health Journey Begins Here',
                'category' => 'welcome',
                'status' => 'active',
                'description' => 'Sent to new patients when they register',
                'body' => 'Dear {{patient_name}},\n\nWelcome to {{hospital_name}}! We are honored that you have chosen us for your healthcare needs.\n\nYour patient account has been successfully created with the following information:\n- Patient ID: {{patient_id}}\n- Registration Date: {{registration_date}}\n\nWhat\'s Next:\n1. Access your patient portal at {{portal_url}} using your email and the password you created\n2. Complete your medical history and insurance information\n3. Schedule your first appointment online or call {{hospital_phone}}\n\nOur Services:\n- 24/7 Emergency Care\n- Specialized Medical Departments\n- Online Appointment Scheduling\n- Patient Portal Access\n- Pharmacy Services\n\nImportant Information:\n- Hospital Address: {{hospital_address}}\n- Main Phone: {{hospital_phone}}\n- Emergency: {{emergency_phone}}\n- Patient Portal: {{portal_url}}\n\nOur dedicated staff is here to provide you with exceptional care. If you have any questions, please don\'t hesitate to contact us.\n\nWelcome to the {{hospital_name}} family!\n\nBest regards,\n{{hospital_name}} Patient Services Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID number',
                    'registration_date' => 'Registration date',
                    'portal_url' => 'Patient portal URL',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number',
                    'emergency_phone' => 'Emergency phone number'
                ],
                'sender_name' => 'Hospital Patient Services',
                'sender_email' => 'welcome@hospital.com'
            ],
            [
                'name' => 'payment_reminder',
                'subject' => 'Payment Reminder - {{hospital_name}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'Sent to patients with outstanding balances',
                'body' => 'Dear {{patient_name}},\n\nThis is a friendly reminder that you have an outstanding balance with {{hospital_name}}.\n\nACCOUNT INFORMATION:\n- Account Number: {{account_number}}\n- Patient ID: {{patient_id}}\n- Service Date: {{service_date}}\n- Amount Due: {{amount_due}}\n- Due Date: {{due_date}}\n\nPAYMENT OPTIONS:\n1. Online: Visit {{payment_url}} to pay securely online\n2. Phone: Call {{billing_phone}} to pay by phone\n3. Mail: Send payment to:\n   {{hospital_name}} Billing Department\n   {{billing_address}}\n4. In Person: Visit our billing office during business hours\n\nPAYMENT METHODS ACCEPTED:\n- Credit/Debit Cards\n- Electronic Bank Transfer\n- Check or Money Order\n\nIf you have insurance that should cover this service, please contact our billing department at {{billing_phone}} immediately.\n\nIf you are experiencing financial hardship, we offer payment plans and financial assistance programs. Please contact our financial counselors at {{financial_counselor_phone}} to discuss your options.\n\nIf you have already made this payment, please disregard this notice.\n\nThank you for your prompt attention to this matter.\n\nBest regards,\n{{hospital_name}} Billing Department',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'account_number' => 'Account number',
                    'patient_id' => 'Patient ID',
                    'service_date' => 'Date of service',
                    'amount_due' => 'Amount due',
                    'due_date' => 'Payment due date',
                    'payment_url' => 'Online payment URL',
                    'billing_phone' => 'Billing department phone',
                    'billing_address' => 'Billing mailing address',
                    'financial_counselor_phone' => 'Financial counselor phone',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Billing',
                'sender_email' => 'billing@hospital.com'
            ],
            [
                'name' => 'appointment_cancellation',
                'subject' => 'Appointment Cancelled - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients when their appointment is cancelled',
                'body' => 'Dear {{patient_name}},\n\nYour appointment has been cancelled.\n\nDETAILS:\n- Doctor: {{doctor_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n\nReason: {{cancellation_reason}}\n\nYou can reschedule your appointment at any time: {{reschedule_url}}\n\nIf this was unexpected, please contact us at {{hospital_phone}}.\n\nBest regards,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'cancellation_reason' => 'Reason for cancellation',
                    'reschedule_url' => 'Reschedule URL',
                    'hospital_phone' => 'Hospital phone number',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'appointment_completion',
                'subject' => 'Appointment Summary - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients after appointment completion with summary',
                'body' => 'Dear {{patient_name}},\n\nThank you for visiting {{hospital_name}}. Here is a summary of your appointment:\n\n- Doctor: {{doctor_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n- Diagnosis: {{diagnosis}}\n- Prescription: {{prescription}}\n- Follow-up Instructions: {{follow_up_instructions}}\n- Next Appointment: {{next_appointment_date}}\n\nIf you have any questions, please contact us.\n\nBest regards,\n{{hospital_name}} Care Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'diagnosis' => 'Diagnosis information',
                    'prescription' => 'Prescription details',
                    'follow_up_instructions' => 'Follow-up instructions',
                    'next_appointment_date' => 'Next appointment date',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'appointment_reschedule',
                'subject' => 'Appointment Rescheduled - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients when their appointment is rescheduled',
                'body' => 'Dear {{patient_name}},\n\nYour appointment has been rescheduled.\n\nPREVIOUS:\n- Date: {{old_date}}\n- Time: {{old_time}}\n\nNEW:\n- Date: {{new_date}}\n- Time: {{new_time}}\n- Doctor: {{doctor_name}}\n- Department: {{department}}\n\nReason: {{reschedule_reason}}\n\nIf the new time does not work for you, you can reschedule from your portal or contact us at {{hospital_phone}}.\n\nBest regards,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'old_date' => 'Previous appointment date',
                    'old_time' => 'Previous appointment time',
                    'new_date' => 'New appointment date',
                    'new_time' => 'New appointment time',
                    'doctor_name' => 'Doctor\'s name',
                    'department' => 'Department name',
                    'reschedule_reason' => 'Reason for rescheduling',
                    'hospital_phone' => 'Hospital phone number',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ]
        ];
    }
}
