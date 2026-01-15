<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use Dompdf\Options;

class ConsultationsReportController extends Controller
{
    /**
     * Display consultations report
     */
    public function index(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        [$startDate, $endDate, $departmentId, $groupBy] = $this->resolveReportFilters($request);

        $reportQuery = $this->buildReportQuery($startDate, $endDate, $departmentId, $groupBy);
        $reportData = (clone $reportQuery)->get();
        $paginator = $reportQuery->paginate(12)->appends($request->query());

        // Summary stats
        $summary = [
            'total_consultations' => $reportData->sum('total_consultations'),
            'total_duration_hours' => round($reportData->sum('total_duration_minutes') / 60, 2),
            'average_duration_minutes' => $reportData->sum('total_consultations') > 0 
                ? round($reportData->sum('total_duration_minutes') / $reportData->sum('total_consultations'), 2)
                : 0,
        ];

        return view('admin.reports.consultations.index', compact(
            'paginator',
            'departments',
            'startDate',
            'endDate',
            'departmentId',
            'groupBy',
            'summary'
        ));
    }

    /**
     * Export consultations report to Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        [$startDate, $endDate, $departmentId, $groupBy] = $this->resolveReportFilters($request);

        $reportData = $this->buildReportQuery($startDate, $endDate, $departmentId, $groupBy)
            ->get()
            ->map(function ($row) {
                return [
                    'Month' => $row->month_name,
                    'Department' => $row->department_name,
                    'Total Consultations' => $row->total_consultations,
                    'Total Duration (Minutes)' => $row->total_duration_minutes,
                    'Total Duration (Hours)' => $row->total_duration_hours,
                    'Average Duration (Minutes)' => $row->average_duration_minutes,
                ];
            });

        $filename = 'consultations_report_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reportData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            if ($reportData->count() > 0) {
                fputcsv($file, array_keys($reportData->first()));
            }
            
            // Data rows
            foreach ($reportData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export consultations report to PDF
     */
    public function exportPdf(Request $request)
    {
        [$startDate, $endDate, $departmentId, $groupBy] = $this->resolveReportFilters($request);

        $department = $departmentId ? Department::find($departmentId) : null;
        $reportData = $this->buildReportQuery($startDate, $endDate, $departmentId, $groupBy)->get();

        // Summary
        $summary = [
            'total_consultations' => $reportData->sum('total_consultations'),
            'total_duration_hours' => round($reportData->sum('total_duration_minutes') / 60, 2),
            'average_duration_minutes' => $reportData->sum('total_consultations') > 0 
                ? round($reportData->sum('total_duration_minutes') / $reportData->sum('total_consultations'), 2)
                : 0,
        ];

        $html = view('admin.reports.consultations.pdf', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'department' => $department,
            'summary' => $summary,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', base_path());
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'consultations_report_' . $startDate . '_to_' . $endDate . '.pdf';
        return response()->streamDownload(function() use ($dompdf) {
            echo $dompdf->output();
        }, $filename);
    }

    private function resolveReportFilters(Request $request): array
    {
        $rawStartDate = $request->input('start_date');
        $rawEndDate = $request->input('end_date');
        $groupBy = $request->input('group_by', 'department');

        $validator = Validator::make($request->all(), [
            'start_date' => ['nullable', 'required_with:end_date'],
            'end_date' => ['nullable', 'required_with:start_date'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'group_by' => ['nullable', 'in:department,month'],
        ]);

        $validator->after(function ($validator) use ($rawStartDate, $rawEndDate) {
            if ($rawStartDate && $rawEndDate) {
                $start = $this->parseReportDate($rawStartDate);
                $end = $this->parseReportDate($rawEndDate);

                if (!$start || !$end) {
                    $validator->errors()->add('start_date', 'Dates must be in YYYY-MM-DD or DD/MM/YYYY format.');
                    return;
                }

                if ($end->lt($start)) {
                    $validator->errors()->add('end_date', 'End date must be on or after start date.');
                }

                if ($start->diffInDays($end) > 731) {
                    $validator->errors()->add('end_date', 'Date range cannot exceed 24 months.');
                }
            }
        });

        $validator->validate();

        $startDate = $rawStartDate
            ? $this->parseReportDate($rawStartDate)->format('Y-m-d')
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $rawEndDate
            ? $this->parseReportDate($rawEndDate)->format('Y-m-d')
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        return [
            $startDate,
            $endDate,
            $request->input('department_id'),
            $groupBy,
        ];
    }

    private function parseReportDate(string $value): ?Carbon
    {
        $value = trim($value);

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value);
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function buildReportQuery(string $startDate, string $endDate, $departmentId = null, string $groupBy = 'department')
    {
        $durationExpression = "CASE
            WHEN appointments.check_in_time IS NOT NULL AND appointments.check_out_time IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, appointments.check_in_time, appointments.check_out_time)
            WHEN booking_services.default_duration_minutes IS NOT NULL
                THEN booking_services.default_duration_minutes
            ELSE 30
        END";

        $departmentNameExpression = $groupBy === 'month'
            ? "CASE
                WHEN COUNT(DISTINCT departments.id) > 1 THEN 'Multiple departments'
                ELSE COALESCE(MAX(departments.name), 'N/A')
              END"
            : "COALESCE(departments.name, 'N/A')";

        $query = Appointment::query()
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('booking_services', 'appointments.service_id', '=', 'booking_services.id')
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate])
            ->selectRaw("
                DATE_FORMAT(MIN(appointments.appointment_date), '%Y-%m') as month_key,
                DATE_FORMAT(MIN(appointments.appointment_date), '%M %Y') as month_name,
                {$departmentNameExpression} as department_name,
                COUNT(*) as total_consultations,
                SUM($durationExpression) as total_duration_minutes,
                ROUND(SUM($durationExpression) / 60, 2) as total_duration_hours,
                ROUND(SUM($durationExpression) / NULLIF(COUNT(*), 0), 2) as average_duration_minutes
            ")
            ->orderByRaw("DATE_FORMAT(MIN(appointments.appointment_date), '%Y-%m')");

        if ($groupBy === 'month') {
            $query->groupByRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%m')");
        } else {
            $query->groupByRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%m'), departments.id, departments.name");
        }

        if (!empty($departmentId)) {
            $query->where('appointments.department_id', $departmentId);
        }

        return $query;
    }
}

