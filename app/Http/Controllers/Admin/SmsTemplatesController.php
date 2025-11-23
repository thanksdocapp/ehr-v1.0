<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmsTemplatesController extends Controller
{
    /**
     * Display a listing of SMS templates
     */
    public function index()
    {
        // Clear any cached models first
        SmsTemplate::clearBootedModels();
        
        // Try multiple approaches to ensure it works on shared hosting
        try {
            // Method 1: Standard Eloquent with explicit where clause
            $templates = SmsTemplate::whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
                
            // If that returns empty, try raw query fallback
            if ($templates->isEmpty()) {
                $rawTemplates = \DB::table('sms_templates')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
                    
                // Convert raw results to models for compatibility
                if (!$rawTemplates->isEmpty()) {
                    $modelCollection = $rawTemplates->getCollection()->map(function($item) {
                        $model = new SmsTemplate();
                        foreach ((array)$item as $key => $value) {
                            if ($key === 'variables' && is_string($value)) {
                                $value = json_decode($value, true) ?? [];
                            }
                            if ($key === 'metadata' && is_string($value)) {
                                $value = json_decode($value, true) ?? [];
                            }
                            $model->setAttribute($key, $value);
                        }
                        $model->exists = true;
                        return $model;
                    });
                    $rawTemplates->setCollection($modelCollection);
                    $templates = $rawTemplates;
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('SmsTemplate index error: ' . $e->getMessage());
            // Create empty paginator if all else fails
            $templates = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), 0, 15, 1, ['path' => request()->url()]
            );
        }
        
        return view('admin.communication.sms-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new SMS template
     */
    public function create()
    {
        return view('admin.communication.sms-templates.create');
    }

    /**
     * Store a newly created SMS template in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sms_templates,name',
            'message' => 'required|string|max:1000',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'status' => 'required|in:active,inactive,draft',
            'sender_id' => 'nullable|string|max:11',
            'variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            SmsTemplate::create($request->all());

            return redirect()->route('admin.sms-templates.index')
                ->with('success', 'SMS template created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create SMS template. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified SMS template
     */
    public function show(SmsTemplate $smsTemplate)
    {
        return view('admin.communication.sms-templates.show', compact('smsTemplate'));
    }

    /**
     * Show the form for editing the specified SMS template
     */
    public function edit(SmsTemplate $smsTemplate)
    {
        return view('admin.communication.sms-templates.edit', compact('smsTemplate'));
    }

    /**
     * Update the specified SMS template in storage
     */
    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sms_templates,name,' . $smsTemplate->id,
            'message' => 'required|string|max:1000',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'status' => 'required|in:active,inactive,draft',
            'sender_id' => 'nullable|string|max:11',
            'variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $smsTemplate->update($request->all());

            return redirect()->route('admin.sms-templates.index')
                ->with('success', 'SMS template updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update SMS template. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified SMS template from storage
     */
    public function destroy(SmsTemplate $smsTemplate)
    {
        try {
            $smsTemplate->delete();

            return redirect()->route('admin.sms-templates.index')
                ->with('success', 'SMS template deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete SMS template. Please try again.');
        }
    }

    /**
     * Duplicate an SMS template
     */
    public function duplicate(SmsTemplate $smsTemplate)
    {
        try {
            $duplicate = $smsTemplate->replicate();
            $duplicate->name = $smsTemplate->name . ' (Copy)';
            $duplicate->status = 'draft';
            $duplicate->save();

            return redirect()->route('admin.sms-templates.index')
                ->with('success', 'SMS template duplicated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to duplicate SMS template. Please try again.');
        }
    }

    /**
     * Preview an SMS template with sample data
     */
    public function preview(SmsTemplate $smsTemplate)
    {
        $smsService = new SmsService();
        
        // Sample data for preview
        $sampleData = [
            'patient_name' => 'John Doe',
            'patient_id' => 'P001',
            'doctor_name' => 'Dr. Smith',
            'appointment_date' => '2024-01-15',
            'appointment_time' => '10:00 AM',
            'department' => 'Cardiology',
            'test_type' => 'Blood Test',
            'test_date' => '2024-01-10',
            'doctor_phone' => '+233 123 456 789',
            'registration_date' => '2024-01-01',
            'admission_date' => '2024-01-12',
            'admission_time' => '09:30 AM',
            'room_number' => '101',
            'discharge_date' => '2024-01-14',
            'medication_name' => 'Aspirin 100mg',
            'pharmacy_hours' => '8:00 AM - 6:00 PM',
            'pharmacy_phone' => '+233 123 456 700',
            'amount_due' => 'GHS 150.00',
            'due_date' => '2024-02-01',
            'payment_url' => url('/payments'),
            'billing_phone' => '+233 123 456 701',
            'new_date' => '2024-01-20',
            'new_time' => '2:00 PM',
            'vaccine_name' => 'COVID-19',
            'child_name' => 'Little John',
            'surgery_type' => 'Appendectomy',
            'surgery_date' => '2024-01-25',
            'surgery_time' => '8:00 AM'
        ];

        try {
            $preview = $smsService->previewTemplate($smsTemplate->name, $sampleData);
            
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
     * Test send SMS template
     */
    public function testSend(Request $request, SmsTemplate $smsTemplate)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'test_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid phone number or test data'
            ]);
        }

        try {
            $smsService = new SmsService();
            
            // Validate phone number
            $phone = $smsService->validatePhoneNumber($request->phone);
            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid phone number format'
                ]);
            }

            // Use provided test data or sample data
            $testData = $request->test_data ?? [
                'patient_name' => 'Test Patient',
                'doctor_name' => 'Dr. Test',
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => now()->format('H:i')
            ];

            $result = $smsService->sendTemplate($smsTemplate->name, $phone, $testData);

            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
