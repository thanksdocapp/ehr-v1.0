<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\IntegrationModule;
use App\Models\Notice;
use App\Services\Integrations\QuincyService;
use App\Services\PatientFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get the current user's primary department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        // For doctors, get primary department from doctors pivot table or department_id
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                // Get primary department from pivot table or fallback to department_id
                $primaryDept = $doctor->primaryDepartment();
                return $primaryDept ? $primaryDept->id : $doctor->department_id;
            }
            return null;
        }
        
        // For other roles, get primary department from users pivot table or department_id
        $user->load('departments');
        $primaryDept = $user->primaryDepartment();
        return $primaryDept ? $primaryDept->id : $user->department_id;
    }

    /**
     * Get all department IDs for the current user (supports multiple departments)
     */
    private function getUserDepartmentIds()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }
        
        $departmentIds = [];
        
        // For doctors, get all departments from doctors pivot table or department_id
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                if ($doctor->departments->isNotEmpty()) {
                    $departmentIds = $doctor->departments->pluck('id')->toArray();
                } elseif ($doctor->department_id) {
                    $departmentIds = [$doctor->department_id];
                }
            }
        } else {
            // For other roles, get all departments from users pivot table or department_id
            $user->load('departments');
            if ($user->departments->isNotEmpty()) {
                $departmentIds = $user->departments->pluck('id')->toArray();
            } elseif ($user->department_id) {
                $departmentIds = [$user->department_id];
            }
        }
        
        return $departmentIds;
    }

    public function index(PatientFeedbackService $patientFeedbackService)
    {
        $user = Auth::user();
        $departmentId = $this->getUserDepartmentId();
        $userDepartmentIds = $this->getUserDepartmentIds();
        
        // Build queries filtered by visibility (visibleTo already handles department filtering)
        $patientsQuery = Patient::query()->visibleTo($user);
        $appointmentsQuery = Appointment::query();
        
        // Filter appointments by department if user has one (support multiple departments)
        if (!empty($userDepartmentIds)) {
            $appointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            // Fallback to single department
            $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        
        // Calculate accurate stats filtered by department
        $stats = [
            'total_patients' => $patientsQuery->count(),
            'total_appointments' => $appointmentsQuery->count(),
            'pending_appointments' => (clone $appointmentsQuery)->where('status', 'pending')->count(),
            'today_appointments' => (clone $appointmentsQuery)->whereDate('appointment_date', Carbon::today())->count(),
        ];

        // Get recent appointments filtered by department
        $recentAppointmentsQuery = Appointment::with(['patient', 'doctor']);
        if (!empty($userDepartmentIds)) {
            $recentAppointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            $recentAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $recentAppointments = $recentAppointmentsQuery->latest()->limit(5)->get();

        // Get active notices for the current user's role
        $notices = Notice::active()
            ->forRole($user->role)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get today's appointments filtered by department for the Today's Schedule widget
        $todayAppointmentsQuery = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', Carbon::today());
        if (!empty($userDepartmentIds)) {
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $todayAppointments = $todayAppointmentsQuery->orderBy('appointment_time', 'asc')->get();

        // Check Quincy integration status (only for non-doctor staff)
        $quincyStatus = null;
        if ($user->role !== 'doctor') {
            $quincyStatus = $this->getQuincyIntegrationStatus();
        }

        // Use doctor-specific layout for doctors
        if ($user->role === 'doctor') {
            // Get doctor model with department relationship for booking link
            $doctor = Doctor::where('user_id', $user->id)
                ->with(['departments', 'department'])
                ->first();

            // Overall patient feedback rating for this doctor (submitted surveys only, excludes N/A)
            $doctorRating = ['avg' => null, 'count' => 0];
            if ($doctor) {
                $map = $patientFeedbackService->getDoctorRatingStats([$doctor->id]);
                if (isset($map[$doctor->id])) {
                    $doctorRating = $map[$doctor->id];
                }
            }
            
            // Get Quincy prescription delivery status for this doctor
            $quincyDeliveryStatus = $this->getDoctorQuincyDeliveryStatus($doctor ? $doctor->id : null);

            // Get upcoming video consultations (online appointments for today and future)
            $upcomingVideoConsultations = Appointment::with(['patient', 'doctor', 'service'])
                ->where('is_online', true)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function($q) {
                    $q->whereDate('appointment_date', '>', Carbon::today())
                      ->orWhere(function($q2) {
                          $q2->whereDate('appointment_date', Carbon::today())
                             ->whereTime('appointment_time', '>=', Carbon::now()->subMinutes(30)->format('H:i:s'));
                      });
                })
                ->when($doctor, function($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                })
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->limit(5)
                ->get();

            return view('doctor.dashboard.index', compact('stats', 'recentAppointments', 'todayAppointments', 'doctor', 'doctorRating', 'quincyDeliveryStatus', 'upcomingVideoConsultations', 'notices'));
        }
        
        return view('staff.dashboard.index', compact('stats', 'recentAppointments', 'todayAppointments', 'quincyStatus', 'notices'));
    }

    public function getStats()
    {
        $departmentId = $this->getUserDepartmentId();
        $userDepartmentIds = $this->getUserDepartmentIds();
        
        // Build queries filtered by department (support multiple departments)
        $patientsQuery = Patient::query()->visibleTo(Auth::user());
        $appointmentsQuery = Appointment::query();
        $doctorsQuery = Doctor::where('status', 'active');
        
        // Filter by department if user has one (support multiple departments)
        if (!empty($userDepartmentIds)) {
            $patientsQuery->byDepartments($userDepartmentIds);
            $appointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
            $doctorsQuery->byDepartments($userDepartmentIds);
        } elseif ($departmentId) {
            // Fallback to single department
            $patientsQuery->byDepartment($departmentId);
            $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
            $doctorsQuery->byDepartment($departmentId);
        }
        
        // Limited stats for staff - no financial or sensitive data, filtered by department
        return response()->json([
            'patients' => [
                'total' => $patientsQuery->count(),
                'new_this_month' => (clone $patientsQuery)->whereMonth('created_at', Carbon::now()->month)->count(),
            ],
            'appointments' => [
                'total' => $appointmentsQuery->count(),
                'pending' => (clone $appointmentsQuery)->where('status', 'pending')->count(),
                'confirmed' => (clone $appointmentsQuery)->where('status', 'confirmed')->count(),
                'today' => (clone $appointmentsQuery)->whereDate('appointment_date', Carbon::today())->count(),
            ],
            'doctors' => [
                'total' => $doctorsQuery->count(),
                'available' => (clone $doctorsQuery)->where('availability_status', 'available')->count(),
            ],
        ]);
    }

    /**
     * Toggle dark mode for doctor users
     * DISABLED - Dark mode feature has been disabled
     */
    public function toggleDarkMode(Request $request)
    {
        // Dark mode feature is disabled
        return response()->json([
            'success' => false,
            'message' => 'Dark mode feature has been disabled.'
        ], 403);
    }
    
    /**
     * Reorder custom menu items (Quick Links) for the current user
     */
    public function reorderCustomMenuItems(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|array',
                'order.*.id' => 'required|integer|exists:custom_menu_items,id',
                'order.*.order' => 'required|integer|min:1'
            ]);
            
            // Update the order for each menu item
            foreach ($request->order as $item) {
                \App\Models\CustomMenuItem::where('id', $item['id'])
                    ->update(['order' => $item['order']]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Quick Links order updated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error reordering custom menu items: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order. Please try again.'
            ], 500);
        }
    }

    /**
     * Get Quincy integration status
     */
    private function getQuincyIntegrationStatus(): array
    {
        try {
            $module = IntegrationModule::where('slug', 'quincy')->first();
            
            if (!$module) {
                return [
                    'available' => false,
                    'configured' => false,
                    'active' => false,
                    'successful' => false,
                    'message' => 'Quincy integration module not found',
                ];
            }

            $isConfigured = $module->is_configured;
            $isActive = $module->is_active;
            $isSuccessful = false;
            $message = 'Not configured';

            if ($isConfigured && $isActive) {
                try {
                    $service = $module->getService();
                    if ($service instanceof QuincyService) {
                        $isSuccessful = $service->isQuincyIntegrationSuccessful();
                        $message = $isSuccessful ? 'Connection successful' : 'Connection test failed';
                    } else {
                        $message = 'Service not available';
                    }
                } catch (\Exception $e) {
                    $message = 'Error checking connection: ' . $e->getMessage();
                }
            } elseif (!$isConfigured) {
                $message = 'Not configured';
            } elseif (!$isActive) {
                $message = 'Integration disabled';
            }

            return [
                'available' => true,
                'configured' => $isConfigured,
                'active' => $isActive,
                'successful' => $isSuccessful,
                'message' => $message,
                'environment' => $module->environment ?? 'sandbox',
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'configured' => false,
                'active' => false,
                'successful' => false,
                'message' => 'Error checking status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get Quincy prescription delivery status for a doctor
     */
    private function getDoctorQuincyDeliveryStatus(?int $doctorId): array
    {
        if (!$doctorId) {
            return [
                'success' => false,
                'available' => false,
                'message' => 'Doctor not found',
                'stats' => [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'success_rate' => 0,
                ],
                'recent_failed' => [],
                'has_failures' => false,
            ];
        }

        try {
            $module = IntegrationModule::where('slug', 'quincy')->first();
            
            if (!$module || !$module->isReady()) {
                return [
                    'success' => false,
                    'available' => false,
                    'message' => 'Quincy integration is not configured or active',
                    'stats' => [
                        'total' => 0,
                        'successful' => 0,
                        'failed' => 0,
                        'pending' => 0,
                        'success_rate' => 0,
                    ],
                    'recent_failed' => [],
                    'has_failures' => false,
                ];
            }

            $service = $module->getService();
            if ($service instanceof QuincyService) {
                return $service->getDoctorPrescriptionDeliveryStatus($doctorId);
            }

            return [
                'success' => false,
                'available' => false,
                'message' => 'Quincy service not available',
                'stats' => [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'success_rate' => 0,
                ],
                'recent_failed' => [],
                'has_failures' => false,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting doctor Quincy delivery status: ' . $e->getMessage());
            return [
                'success' => false,
                'available' => false,
                'message' => 'Error checking delivery status',
                'stats' => [
                    'total' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'pending' => 0,
                    'success_rate' => 0,
                ],
                'recent_failed' => [],
                'has_failures' => false,
            ];
        }
    }
}
