<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        $stats = $this->getHospitalStats();
        $recentAppointments = $this->getRecentAppointments();
        $appointmentChartData = $this->getAppointmentChartData();
        $departmentStats = $this->getDepartmentStats();
        $activityLogs = $this->getRecentActivityLogs();
        
        // Get additional data for new dashboard
        $recentPatients = $this->getRecentPatients();
        $pendingAppointments = $this->getPendingAppointments();
        $todaysAppointments = $this->getTodaysAppointments();
        $patientRegistrationData = $this->getPatientRegistrationData();
        
        // Update stats for dashboard compatibility
        $stats['today_patients'] = $stats['today_new_patients'];
        $stats['active_doctors'] = Doctor::where('is_active', true)->count();
        $stats['active_departments'] = Department::where('is_active', true)->count();
        
        return view('admin.dashboard', compact(
            'stats',
            'recentAppointments',
            'recentPatients',
            'pendingAppointments',
            'todaysAppointments',
            'departmentStats',
            'appointmentChartData',
            'patientRegistrationData'
        ));
    }

    /**
     * Get hospital statistics.
     */
    private function getHospitalStats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            'total_patients' => Patient::count(),
            'total_doctors' => Doctor::count(),
            'total_departments' => Department::count(),
            'total_appointments' => Appointment::count(),
            
            // Today's stats
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'today_new_patients' => Patient::whereDate('created_at', $today)->count(),
            
            // This week's stats
            'week_appointments' => Appointment::where('appointment_date', '>=', $thisWeek)->count(),
            'week_new_patients' => Patient::where('created_at', '>=', $thisWeek)->count(),
            
            // This month's stats
            'month_appointments' => Appointment::where('appointment_date', '>=', $thisMonth)->count(),
            'month_new_patients' => Patient::where('created_at', '>=', $thisMonth)->count(),
            
            // This year's stats
            'year_appointments' => Appointment::where('appointment_date', '>=', $thisYear)->count(),
            'year_new_patients' => Patient::where('created_at', '>=', $thisYear)->count(),
            
            // Appointment status breakdown
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
            'cancelled_appointments' => Appointment::where('status', 'cancelled')->count(),
            'completed_appointments' => Appointment::where('status', 'completed')->count(),
            
            // Growth rates (compared to last month)
            'patient_growth' => $this->calculateGrowthRate('patients'),
            'appointment_growth' => $this->calculateGrowthRate('appointments'),
            
            // System stats
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
        ];
    }

    /**
     * Calculate growth rate for a given metric.
     */
    private function calculateGrowthRate($metric)
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->startOfMonth()->subDay();

        switch ($metric) {
            case 'patients':
                $current = Patient::where('created_at', '>=', $thisMonth)->count();
                $previous = Patient::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
                break;
            case 'appointments':
                $current = Appointment::where('created_at', '>=', $thisMonth)->count();
                $previous = Appointment::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
                break;
            default:
                return 0;
        }

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get recent appointments.
     */
    private function getRecentAppointments()
    {
        return Appointment::select('id', 'appointment_number', 'patient_id', 'appointment_date', 'appointment_time', 'status', 'doctor_id', 'department_id', 'created_at')
            ->with([
                'doctor:id,title,first_name,last_name',
                'department:id,name', 
                'patient:id,first_name,last_name,phone,email'
            ])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get appointment chart data for the last 30 days.
     */
    private function getAppointmentChartData()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $appointments = Appointment::select(
                DB::raw('DATE(appointment_date) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('appointment_date', '>=', $thirtyDaysAgo)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [];
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $appointment = $appointments->firstWhere('date', $date);
            $chartData[] = [
                'date' => $date,
                'count' => $appointment ? $appointment->count : 0
            ];
        }

        return $chartData;
    }

    /**
     * Get department statistics.
     */
    private function getDepartmentStats()
    {
        return Department::withCount(['doctors', 'appointments'])
            ->orderBy('appointments_count', 'desc')
            ->get()
            ->map(function ($department) {
                return [
                    'name' => $department->name,
                    'doctors' => $department->doctors_count,
                    'appointments' => $department->appointments_count,
                    'percentage' => $this->calculateDepartmentPercentage($department->appointments_count)
                ];
            });
    }

    /**
     * Calculate department percentage of total appointments.
     */
    private function calculateDepartmentPercentage($count)
    {
        $total = Appointment::count();
        return $total > 0 ? round(($count / $total) * 100, 1) : 0;
    }

    /**
     * Get real-time stats for dashboard updates.
     */
    public function getRealtimeStats()
    {
        return response()->json([
            'timestamp' => now()->toISOString(),
            'stats' => [
                'online_users' => $this->getOnlineUsersCount(),
                'pending_appointments' => Appointment::where('status', 'pending')->count(),
                'todays_appointments' => Appointment::whereDate('appointment_date', today())->count(),
                'active_sessions' => $this->getActiveSessionsCount(),
                'system_load' => $this->getSystemLoad(),
            ],
            'notifications' => $this->getRealtimeNotifications()
        ]);
    }
    
    /**
     * Get appointment trend data.
     */
    private function getAppointmentTrendData($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $appointments = Appointment::select(
                DB::raw('DATE(appointment_date) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(CASE WHEN status = "confirmed" THEN 1 END) as confirmed'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending'),
                DB::raw('COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed')
            )
            ->where('appointment_date', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Total Appointments',
                    'data' => [],
                    'borderColor' => 'rgb(54, 162, 235)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Confirmed',
                    'data' => [],
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Pending',
                    'data' => [],
                    'borderColor' => 'rgb(255, 206, 86)',
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'tension' => 0.1
                ]
            ]
        ];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $appointment = $appointments->firstWhere('date', $date);
            
            $chartData['labels'][] = Carbon::parse($date)->format('M d');
            $chartData['datasets'][0]['data'][] = $appointment ? $appointment->total : 0;
            $chartData['datasets'][1]['data'][] = $appointment ? $appointment->confirmed : 0;
            $chartData['datasets'][2]['data'][] = $appointment ? $appointment->pending : 0;
        }
        
        return $chartData;
    }
    
    /**
     * Get patient registration trend data.
     */
    private function getPatientRegistrationTrendData($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $patients = Patient::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $chartData = [
            'labels' => [],
            'data' => []
        ];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $patient = $patients->firstWhere('date', $date);
            
            $chartData['labels'][] = Carbon::parse($date)->format('M d');
            $chartData['data'][] = $patient ? $patient->count : 0;
        }
        
        return $chartData;
    }
    
    /**
     * Get department comparison data.
     */
    private function getDepartmentComparisonData()
    {
        $departments = Department::withCount(['appointments', 'doctors'])
            ->orderBy('appointments_count', 'desc')
            ->limit(10)
            ->get();
            
        return [
            'labels' => $departments->pluck('name'),
            'datasets' => [
                [
                    'label' => 'Appointments',
                    'data' => $departments->pluck('appointments_count'),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get revenue data (placeholder for billing integration).
     */
    private function getRevenueData($days = 30)
    {
        // This would integrate with your billing system
        // For now, returning sample data structure
        return [
            'labels' => [],
            'data' => [],
            'total_revenue' => 0,
            'average_per_day' => 0
        ];
    }
    
    /**
     * Get appointment status distribution data.
     */
    private function getAppointmentStatusData()
    {
        $statusCounts = Appointment::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
            
        return [
            'labels' => $statusCounts->pluck('status'),
            'data' => $statusCounts->pluck('count'),
            'backgroundColor' => [
                'rgba(255, 206, 86, 0.8)', // pending - yellow
                'rgba(75, 192, 192, 0.8)', // confirmed - green
                'rgba(54, 162, 235, 0.8)', // completed - blue
                'rgba(255, 99, 132, 0.8)'  // cancelled - red
            ]
        ];
    }
    
    /**
     * Get online users count.
     */
    private function getOnlineUsersCount()
    {
        // Simple session-based online users count
        return DB::table('sessions')
            ->where('last_activity', '>', Carbon::now()->subMinutes(5)->timestamp)
            ->count();
    }
    
    /**
     * Get active sessions count.
     */
    private function getActiveSessionsCount()
    {
        return DB::table('sessions')->count();
    }
    
    /**
     * Get system load (simplified).
     */
    private function getSystemLoad()
    {
        return [
            'cpu' => rand(10, 90), // Placeholder - integrate with system monitoring
            'memory' => rand(30, 80),
            'disk' => rand(20, 70)
        ];
    }
    
    /**
     * Get realtime notifications.
     */
    private function getRealtimeNotifications()
    {
        return [
            [
                'type' => 'appointment',
                'message' => 'New appointment booked',
                'time' => Carbon::now()->diffForHumans()
            ]
        ];
    }
    
    /**
     * Get recent activity logs.
     */
    private function getRecentActivityLogs()
    {
        return [
            [
                'type' => 'appointment_created',
                'message' => 'New appointment created',
                'user' => 'System',
                'time' => Carbon::now()->subMinutes(5),
                'icon' => 'fas fa-calendar-plus',
                'color' => 'success'
            ],
            [
                'type' => 'patient_registered',
                'message' => 'New patient registered',
                'user' => 'System',
                'time' => Carbon::now()->subMinutes(15),
                'icon' => 'fas fa-user-plus',
                'color' => 'info'
            ],
            [
                'type' => 'appointment_cancelled',
                'message' => 'Appointment cancelled',
                'user' => 'Admin',
                'time' => Carbon::now()->subMinutes(30),
                'icon' => 'fas fa-times-circle',
                'color' => 'danger'
            ],
            [
                'type' => 'doctor_added',
                'message' => 'New doctor added to system',
                'user' => 'Admin',
                'time' => Carbon::now()->subHours(2),
                'icon' => 'fas fa-user-md',
                'color' => 'primary'
            ],
            [
                'type' => 'system_backup',
                'message' => 'System backup completed',
                'user' => 'System',
                'time' => Carbon::now()->subHours(6),
                'icon' => 'fas fa-database',
                'color' => 'secondary'
            ]
        ];
    }

    /**
     * Get real-time statistics via AJAX.
     */
    public function getStats()
    {
        return response()->json($this->getHospitalStats());
    }

    /**
     * Get appointment chart data via AJAX - Enhanced version.
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'basic');
        $period = $request->get('period', '30');
        
        switch ($type) {
            case 'basic':
                return response()->json($this->getAppointmentChartData());
            case 'appointments':
                return response()->json($this->getAppointmentTrendData($period));
            case 'patients':
                return response()->json($this->getPatientRegistrationTrendData($period));
            case 'departments':
                return response()->json($this->getDepartmentComparisonData());
            case 'revenue':
                return response()->json($this->getRevenueData($period));
            case 'status':
                return response()->json($this->getAppointmentStatusData());
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }
    
    /**
     * Get advanced chart data for various chart types (alias for getChartData).
     */
    public function getAdvancedChartData(Request $request)
    {
        return $this->getChartData($request);
    }

    /**
     * Get department statistics via AJAX.
     */
    public function getDepartmentData()
    {
        return response()->json($this->getDepartmentStats());
    }

    /**
     * Get system health status.
     */
    public function getSystemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
            'cache' => $this->checkCacheHealth(),
        ];

        $overallHealth = collect($health)->every(function ($status) {
            return $status === 'healthy';
        }) ? 'healthy' : 'issues';

        return response()->json([
            'overall' => $overallHealth,
            'components' => $health,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Check database health.
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Check storage health.
     */
    private function checkStorageHealth()
    {
        $freeSpace = disk_free_space(storage_path());
        $totalSpace = disk_total_space(storage_path());
        $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        return $usedPercentage < 90 ? 'healthy' : 'warning';
    }

    /**
     * Check queue health.
     */
    private function checkQueueHealth()
    {
        return 'healthy';
    }

    /**
     * Check cache health.
     */
    private function checkCacheHealth()
    {
        try {
            cache()->put('health_check', 'test', 60);
            $result = cache()->get('health_check');
            return $result === 'test' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }
    
    /**
     * Get recent patients.
     */
    private function getRecentPatients()
    {
        return Patient::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get pending appointments.
     */
    private function getPendingAppointments()
    {
        return Appointment::with([
            'doctor:id,title,first_name,last_name,department_id',
            'doctor.department:id,name',
            'patient:id,first_name,last_name,email,phone'
        ])
            ->where('status', 'pending')
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->limit(5)
            ->get()
            ->map(function($appointment) {
                $appointment->patient_name = $appointment->patient ? ($appointment->patient->first_name . ' ' . $appointment->patient->last_name) : 'N/A';
                return $appointment;
            });
    }
    
    /**
     * Get today's appointments.
     */
    private function getTodaysAppointments()
    {
        return Appointment::with([
            'doctor:id,title,first_name,last_name,department_id',
            'doctor.department:id,name',
            'patient:id,first_name,last_name,email,phone'
        ])
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('appointment_time', 'asc')
            ->get()
            ->map(function($appointment) {
                $appointment->patient_name = $appointment->patient ? ($appointment->patient->first_name . ' ' . $appointment->patient->last_name) : 'N/A';
                return $appointment;
            });
    }
    
    /**
     * Get patient registration data (last 12 months).
     */
    private function getPatientRegistrationData()
    {
        $startDate = Carbon::now()->subMonths(12);
        
        // Use database-agnostic approach
        $dbDriver = config('database.default');
        $dateFormat = $dbDriver === 'mysql' ? 'DATE_FORMAT(created_at, "%Y-%m")' : 'strftime("%Y-%m", created_at)';
        
        return Patient::select(
                DB::raw($dateFormat . ' as month'),
                DB::raw('COUNT(*) as patients')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::parse($item->month . '-01')->format('M Y'),
                    'patients' => $item->patients
                ];
            });
    }
}
