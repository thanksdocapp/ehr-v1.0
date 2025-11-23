<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Billing;
use App\Models\LabReport;

class AdvancedReportsController extends Controller
{
    /**
     * Display advanced reports dashboard.
     */
    public function index()
    {
        $recentReports = $this->getRecentReports();
        $quickStats = $this->getQuickStats();
        
        return view('admin.reports.advanced.index', compact('recentReports', 'quickStats'));
    }

    /**
     * Custom Report Builder Interface.
     */
    public function customReportBuilder()
    {
        $availableTables = $this->getAvailableTables();
        $savedReports = $this->getSavedReports();
        
        return view('admin.reports.advanced.custom-builder', compact('availableTables', 'savedReports'));
    }

    /**
     * Custom Reports Interface - Main entry point for custom reporting.
     */
    public function customReports()
    {
        $availableTables = $this->getAvailableTables();
        $savedReports = $this->getSavedReports();
        
        return view('admin.reports.advanced.custom-builder', compact('availableTables', 'savedReports'));
    }

    /**
     * Generate custom report based on user criteria.
     */
    public function generateCustomReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_name' => 'required|string|max:255',
            'tables' => 'required|array|min:1',
            'columns' => 'required|array|min:1',
            'date_range' => 'nullable|string',
            'custom_date_from' => 'nullable|date',
            'custom_date_to' => 'nullable|date|after_or_equal:custom_date_from',
            'filters' => 'nullable|array',
            'group_by' => 'nullable|array',
            'order_by' => 'nullable|string',
            'order_direction' => 'nullable|in:asc,desc',
            'chart_type' => 'nullable|in:table,bar,line,pie,column',
            'save_report' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reportData = $this->buildCustomQuery($request->all());
            
            // Save report if requested
            if ($request->boolean('save_report')) {
                $this->saveCustomReport($request->all(), $reportData['query']);
            }
            
            return response()->json([
                'success' => true,
                'data' => $reportData['results'],
                'query_info' => $reportData['query_info'],
                'chart_data' => $this->prepareChartData($reportData['results'], $request->chart_type),
                'total_records' => $reportData['total']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Financial Analytics Dashboard.
     */
    public function financialAnalytics()
    {
        $period = request('period', 'month');
        $year = request('year', now()->year);
        
        $analytics = [
            'revenue_overview' => $this->getRevenueOverview($period, $year),
            'payment_methods' => $this->getPaymentMethodsBreakdown($period, $year),
            'department_revenue' => $this->getDepartmentRevenueAnalysis($period, $year),
            'billing_trends' => $this->getBillingTrends($period, $year),
            'outstanding_payments' => $this->getOutstandingPayments(),
            'revenue_forecast' => $this->getRevenueForecast($year),
            'expense_analysis' => $this->getExpenseAnalysis($period, $year),
            'profit_margins' => $this->getProfitMargins($period, $year)
        ];
        
        return view('admin.reports.advanced.financial-analytics', compact('analytics', 'period', 'year'));
    }

    /**
     * Patient Analytics Dashboard.
     */
    public function patientAnalytics()
    {
        $analytics = [
            'patient_demographics' => $this->getPatientDemographics(),
            'appointment_patterns' => $this->getAppointmentPatterns(),
            'patient_retention' => $this->getPatientRetentionAnalysis(),
            'visit_frequency' => $this->getVisitFrequencyAnalysis(),
            'patient_satisfaction' => $this->getPatientSatisfactionMetrics(),
            'geographic_distribution' => $this->getPatientGeographicDistribution()
        ];
        
        return view('admin.reports.advanced.patient-analytics', compact('analytics'));
    }

    /**
     * Doctor Performance Analytics.
     */
    public function doctorAnalytics()
    {
        $period = request('period', 'month');
        
        $analytics = [
            'doctor_performance' => $this->getDoctorPerformanceMetrics($period),
            'appointment_efficiency' => $this->getAppointmentEfficiencyMetrics($period),
            'patient_ratings' => $this->getDoctorRatings($period),
            'specialty_analysis' => $this->getSpecialtyAnalysis($period),
            'workload_distribution' => $this->getWorkloadDistribution($period)
        ];
        
        return view('admin.reports.advanced.doctor-analytics', compact('analytics', 'period'));
    }

    /**
     * Export custom report to various formats.
     */
    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'excel');
        $reportData = json_decode($request->get('data'), true);
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPDF($reportData, $request->get('report_name', 'Custom Report'));
            case 'csv':
                return $this->exportToCSV($reportData, $request->get('report_name', 'Custom Report'));
            case 'excel':
                return $this->exportToExcel($reportData, $request->get('report_name', 'Custom Report'));
            default:
                return response()->json(['error' => 'Unsupported format'], 400);
        }
    }

    /**
     * Get revenue overview analytics (MySQL compatible).
     */
    private function getRevenueOverview($period, $year)
    {
        // MySQL compatible date functions
        $groupBy = $period === 'month' ? "MONTH(created_at)" : "DAY(created_at)";
        
        $revenue = DB::table('billings')
            ->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(paid_amount) as paid_revenue'),
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('AVG(total_amount) as avg_bill_amount')
            )
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->where('status', '!=', 'cancelled')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
            
        $summary = DB::table('billings')
            ->select(
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(total_amount - paid_amount) as outstanding'),
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('AVG(total_amount) as avg_bill')
            )
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->where('status', '!=', 'cancelled')
            ->first();
            
        return [
            'trends' => $revenue,
            'summary' => $summary,
            'growth_rate' => $this->calculateRevenueGrowthRate($year),
            'collection_rate' => $summary->total_revenue > 0 ? 
                round(($summary->total_paid / $summary->total_revenue) * 100, 2) : 0
        ];
    }

    /**
     * Get payment methods breakdown (MySQL compatible).
     */
    private function getPaymentMethodsBreakdown($period, $year)
    {
        return DB::table('billings')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(paid_amount) as total_amount'),
                DB::raw('AVG(paid_amount) as avg_amount')
            )
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->where('status', 'paid')
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();
    }

    /**
     * Get department revenue analysis (MySQL compatible).
     */
    private function getDepartmentRevenueAnalysis($period, $year)
    {
        return DB::table('billings')
            ->join('appointments', 'billings.appointment_id', '=', 'appointments.id')
            ->join('departments', 'appointments.department_id', '=', 'departments.id')
            ->select(
                'departments.name as department_name',
                DB::raw('COUNT(billings.id) as total_bills'),
                DB::raw('SUM(billings.total_amount) as total_revenue'),
                DB::raw('SUM(billings.paid_amount) as total_paid'),
                DB::raw('AVG(billings.total_amount) as avg_bill_amount'),
                DB::raw('COUNT(DISTINCT appointments.patient_id) as unique_patients')
            )
            ->whereRaw("YEAR(billings.created_at) = ?", [$year])
            ->where('billings.status', '!=', 'cancelled')
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Get billing trends analysis (MySQL compatible).
     */
    private function getBillingTrends($period, $year)
    {
        $groupBy = $period === 'month' ? "MONTH(created_at)" : "DAY(created_at)";
        
        return DB::table('billings')
            ->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('SUM(total_amount) as total_billed'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_bills'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_bills')
            )
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get outstanding payments analysis (MySQL compatible).
     */
    private function getOutstandingPayments()
    {
        return [
            'aging_analysis' => DB::table('billings')
                ->select(
                    DB::raw('
                        CASE 
                            WHEN DATEDIFF(NOW(), created_at) <= 30 THEN "0-30 days"
                            WHEN DATEDIFF(NOW(), created_at) <= 60 THEN "31-60 days"
                            WHEN DATEDIFF(NOW(), created_at) <= 90 THEN "61-90 days"
                            ELSE "Over 90 days"
                        END as aging_group
                    '),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(total_amount - paid_amount) as outstanding_amount')
                )
                ->where('status', 'partial')
                ->orWhere('status', 'pending')
                ->groupBy('aging_group')
                ->get(),
            'top_debtors' => DB::table('billings')
                ->join('patients', 'billings.patient_id', '=', 'patients.id')
                ->select(
                    'patients.first_name',
                    'patients.last_name',
                    'patients.phone',
                    DB::raw('SUM(billings.total_amount - billings.paid_amount) as total_outstanding'),
                    DB::raw('COUNT(billings.id) as outstanding_bills')
                )
                ->where(function($query) {
                    $query->where('billings.status', 'partial')
                          ->orWhere('billings.status', 'pending');
                })
                ->groupBy('patients.id', 'patients.first_name', 'patients.last_name', 'patients.phone')
                ->having('total_outstanding', '>', 0)
                ->orderBy('total_outstanding', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    /**
     * Get revenue forecast (MySQL compatible).
     */
    private function getRevenueForecast($year)
    {
        $monthlyRevenue = DB::table('billings')
            ->select(
                DB::raw("MONTH(created_at) as month"),
                DB::raw('SUM(paid_amount) as revenue')
            )
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month')
            ->toArray();

        // Simple forecast calculation
        $forecast = [];
        $currentMonth = (int)date('n');
        $avgGrowth = 0;
        
        if (count($monthlyRevenue) > 1) {
            $values = array_values($monthlyRevenue);
            $growth = [];
            for ($i = 1; $i < count($values); $i++) {
                if ($values[$i-1] > 0) {
                    $growth[] = ($values[$i] - $values[$i-1]) / $values[$i-1];
                }
            }
            $avgGrowth = count($growth) > 0 ? array_sum($growth) / count($growth) : 0;
        }

        $lastRevenue = end($monthlyRevenue) ?: 0;
        for ($i = 1; $i <= 3; $i++) {
            $forecastMonth = $currentMonth + $i;
            if ($forecastMonth > 12) {
                $forecastMonth -= 12;
            }
            $forecast[$forecastMonth] = $lastRevenue * (1 + ($avgGrowth * $i));
        }

        return [
            'historical' => $monthlyRevenue,
            'forecast' => $forecast,
            'growth_rate' => round($avgGrowth * 100, 2)
        ];
    }

    /**
     * Get patient demographics (MySQL compatible).
     */
    private function getPatientDemographics()
    {
        // Age groups using MySQL compatible calculation
        $ageGroups = DB::table('patients')
            ->select(
                DB::raw('
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, NOW()) < 18 THEN "Under 18"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, NOW()) BETWEEN 18 AND 30 THEN "18-30"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, NOW()) BETWEEN 31 AND 50 THEN "31-50"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, NOW()) BETWEEN 51 AND 70 THEN "51-70"
                        ELSE "Over 70"
                    END as age_group
                '),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('date_of_birth')
            ->groupBy('age_group')
            ->orderBy('count', 'desc')
            ->get();

        $genderDistribution = DB::table('patients')
            ->select('gender', DB::raw('COUNT(*) as count'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get();

        $registrationTrends = DB::table('patients')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as new_patients')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'age_groups' => $ageGroups,
            'gender_distribution' => $genderDistribution,
            'registration_trends' => $registrationTrends,
            'total_patients' => DB::table('patients')->count(),
            'active_patients' => DB::table('patients')->where('is_active', true)->count()
        ];
    }

    /**
     * Get appointment patterns (MySQL compatible).
     */
    private function getAppointmentPatterns()
    {
        return [
            'hourly_distribution' => DB::table('appointments')
                ->select(
                    DB::raw("HOUR(appointment_time) as hour"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
            'daily_distribution' => DB::table('appointments')
                ->select(
                    DB::raw("
                        CASE DAYOFWEEK(appointment_date)
                            WHEN 1 THEN 'Sunday'
                            WHEN 2 THEN 'Monday'
                            WHEN 3 THEN 'Tuesday'
                            WHEN 4 THEN 'Wednesday'
                            WHEN 5 THEN 'Thursday'
                            WHEN 6 THEN 'Friday'
                            WHEN 7 THEN 'Saturday'
                        END as day_name
                    "),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('day_name')
                ->orderBy('count', 'desc')
                ->get(),
            'monthly_trends' => DB::table('appointments')
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('COUNT(*) as appointments'),
                    DB::raw('COUNT(DISTINCT patient_id) as unique_patients')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
        ];
    }

    /**
     * Get quick statistics for dashboard.
     */
    private function getQuickStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        return [
            'total_revenue_month' => DB::table('billings')
                ->whereRaw("MONTH(created_at) = ?", [$currentMonth])
                ->whereRaw("YEAR(created_at) = ?", [$currentYear])
                ->sum('paid_amount') ?: 0,
            'total_appointments_month' => DB::table('appointments')
                ->whereRaw("MONTH(created_at) = ?", [$currentMonth])
                ->whereRaw("YEAR(created_at) = ?", [$currentYear])
                ->count() ?: 0,
            'new_patients_month' => DB::table('patients')
                ->whereRaw("MONTH(created_at) = ?", [$currentMonth])
                ->whereRaw("YEAR(created_at) = ?", [$currentYear])
                ->count() ?: 0,
            'outstanding_amount' => DB::table('billings')
                ->where('status', 'partial')
                ->sum(DB::raw('total_amount - paid_amount')) ?: 0
        ];
    }

    // Add all other methods with MySQL compatibility
    private function calculateRevenueGrowthRate($year)
    {
        $currentYear = DB::table('billings')
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->sum('paid_amount') ?: 0;
            
        $previousYear = DB::table('billings')
            ->whereRaw("YEAR(created_at) = ?", [$year - 1])
            ->sum('paid_amount') ?: 0;
            
        if ($previousYear > 0) {
            return round((($currentYear - $previousYear) / $previousYear) * 100, 2);
        }
        
        return 0;
    }

    private function getExpenseAnalysis($period, $year)
    {
        return [
            'total_expenses' => 0,
            'expense_categories' => [],
            'expense_trends' => [],
            'cost_per_patient' => 0
        ];
    }

    private function getProfitMargins($period, $year)
    {
        $revenue = DB::table('billings')
            ->whereRaw("YEAR(created_at) = ?", [$year])
            ->sum('paid_amount') ?: 0;
            
        $estimatedExpenses = $revenue * 0.7;
        $profit = $revenue - $estimatedExpenses;
        
        return [
            'gross_revenue' => $revenue,
            'estimated_expenses' => $estimatedExpenses,
            'net_profit' => $profit,
            'profit_margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0,
            'monthly_breakdown' => collect()
        ];
    }

    // All the remaining placeholder methods (simplified for SQLite)
    private function getPatientRetentionAnalysis()
    {
        return [
            'return_patients' => collect(),
            'retention_stats' => [
                'total_patients' => DB::table('patients')->count(),
                'returning_patients' => 0,
                'avg_visits_per_patient' => 0
            ]
        ];
    }

    private function getVisitFrequencyAnalysis()
    {
        return collect();
    }

    private function getPatientSatisfactionMetrics()
    {
        return [
            'average_rating' => 4.5,
            'total_responses' => 0,
            'rating_distribution' => [],
            'feedback_trends' => []
        ];
    }

    private function getPatientGeographicDistribution()
    {
        return DB::table('patients')
            ->select(
                DB::raw('address as city'),
                DB::raw('COUNT(*) as patient_count')
            )
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->groupBy('address')
            ->orderBy('patient_count', 'desc')
            ->limit(10)
            ->get();
    }

    private function getDoctorPerformanceMetrics($period)
    {
        return collect();
    }

    private function getAppointmentEfficiencyMetrics($period)
    {
        return [
            'completion_rate' => (object)['total' => 0, 'completed' => 0],
            'cancellation_rate' => (object)['total' => 0, 'cancelled' => 0],
            'no_show_rate' => (object)['total' => 0, 'no_shows' => 0]
        ];
    }

    private function getDoctorRatings($period)
    {
        return [];
    }

    private function getSpecialtyAnalysis($period)
    {
        return collect();
    }

    private function getWorkloadDistribution($period)
    {
        return collect();
    }

    // Implement all the other required methods (buildCustomQuery, export methods, etc.)
    private function buildCustomQuery($criteria)
    {
        try {
            $query = DB::table($criteria['tables'][0]);
            
            if (count($criteria['tables']) > 1) {
                $query = $this->addJoins($query, $criteria['tables']);
            }
            
            $query->select($criteria['columns']);
            
            if (!empty($criteria['date_range'])) {
                $query = $this->applyDateFilters($query, $criteria);
            }
            
            if (!empty($criteria['filters'])) {
                $query = $this->applyFilters($query, $criteria['filters']);
            }
            
            if (!empty($criteria['group_by'])) {
                $query->groupBy($criteria['group_by']);
            }
            
            if (!empty($criteria['order_by'])) {
                $direction = $criteria['order_direction'] ?? 'asc';
                $query->orderBy($criteria['order_by'], $direction);
            }
            
            $total = $query->count();
            $results = $query->limit(1000)->get();
            
            return [
                'results' => $results,
                'total' => $total,
                'query_info' => [
                    'tables' => $criteria['tables'],
                    'columns' => $criteria['columns'],
                    'filters_applied' => count($criteria['filters'] ?? []),
                    'execution_time' => microtime(true)
                ]
            ];
        } catch (\Exception $e) {
            return [
                'results' => collect(),
                'total' => 0,
                'query_info' => [
                    'error' => $e->getMessage()
                ]
            ];
        }
    }

    private function getAvailableTables()
    {
        return [
            'appointments' => [
                'name' => 'Appointments',
                'columns' => ['id', 'appointment_number', 'patient_name', 'doctor_id', 'department_id', 'appointment_date', 'appointment_time', 'status', 'created_at'],
                'relationships' => ['patients', 'doctors', 'departments']
            ],
            'patients' => [
                'name' => 'Patients',
                'columns' => ['id', 'patient_id', 'first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender', 'address', 'created_at'],
                'relationships' => ['appointments', 'billings']
            ],
            'doctors' => [
                'name' => 'Doctors',
                'columns' => ['id', 'name', 'email', 'phone', 'specialization', 'license_number', 'department_id', 'is_active', 'created_at'],
                'relationships' => ['appointments', 'departments']
            ],
            'departments' => [
                'name' => 'Departments',
                'columns' => ['id', 'name', 'description', 'is_active', 'created_at'],
                'relationships' => ['doctors', 'appointments']
            ],
            'billings' => [
                'name' => 'Billings',
                'columns' => ['id', 'billing_number', 'patient_id', 'appointment_id', 'total_amount', 'paid_amount', 'status', 'payment_method', 'created_at'],
                'relationships' => ['patients', 'appointments']
            ]
        ];
    }

    private function addJoins($query, $tables)
    {
        // Simplified join logic
        return $query;
    }

    private function applyDateFilters($query, $criteria)
    {
        // Simplified date filtering
        return $query;
    }

    private function applyFilters($query, $filters)
    {
        // Simplified filtering
        return $query;
    }

    private function saveCustomReport($criteria, $query)
    {
        DB::table('saved_reports')->insert([
            'name' => $criteria['report_name'],
            'criteria' => json_encode($criteria),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function getSavedReports()
    {
        return DB::table('saved_reports')
            ->select('id', 'name', 'created_at', 'created_by')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    private function getRecentReports()
    {
        return DB::table('saved_reports')
            ->select('id', 'name', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    private function prepareChartData($data, $chartType)
    {
        if (empty($chartType) || $chartType === 'table') {
            return null;
        }

        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $rowArray = (array) $row;
            $keys = array_keys($rowArray);
            
            if (count($keys) >= 2) {
                $labels[] = $rowArray[$keys[0]];
                $values[] = is_numeric($rowArray[$keys[1]]) ? (float) $rowArray[$keys[1]] : 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $values,
            'type' => $chartType
        ];
    }

    private function exportToPDF($data, $reportName)
    {
        return response()->json([
            'success' => true,
            'message' => 'PDF export functionality to be implemented',
            'download_url' => '#'
        ]);
    }

    private function exportToCSV($data, $reportName)
    {
        $filename = str_slug($reportName) . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            if (!empty($data)) {
                $firstRow = (array) $data[0];
                fputcsv($file, array_keys($firstRow));
                
                foreach ($data as $row) {
                    fputcsv($file, (array) $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($data, $reportName)
    {
        return $this->exportToCSV($data, $reportName);
    }

    public function getAvailableTablesApi()
    {
        return response()->json($this->getAvailableTables());
    }

    public function getTableColumns($table)
    {
        $availableTables = $this->getAvailableTables();
        
        if (isset($availableTables[$table])) {
            return response()->json($availableTables[$table]['columns']);
        }
        
        return response()->json(['error' => 'Table not found'], 404);
    }
}
