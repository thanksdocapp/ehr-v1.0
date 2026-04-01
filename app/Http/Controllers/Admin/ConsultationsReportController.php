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

        // Summary stats (total duration in minutes for "X hours Y minutes" formatting in view)
        $totalMinutes = (int) $reportData->sum('total_duration_minutes');
        $summary = [
            'total_consultations' => $reportData->sum('total_consultations'),
            'total_duration_minutes' => $totalMinutes,
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
                $mins = (int) $row->total_duration_minutes;
                $h = (int) floor($mins / 60);
                $m = $mins % 60;
                $durationFormatted = $h . ' hours ' . $m . ' minutes';
                return [
                    'Month' => $row->month_name,
                    'Department' => $row->department_name,
                    'Total Consultations' => $row->total_consultations,
                    'Total Duration (Minutes)' => $mins,
                    'Total Duration' => $durationFormatted,
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

        // Summary (total_duration_minutes for "X hours Y minutes" in PDF view)
        $totalMinutes = (int) $reportData->sum('total_duration_minutes');
        $summary = [
            'total_consultations' => $reportData->sum('total_consultations'),
            'total_duration_minutes' => $totalMinutes,
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

    /**
     * Display consultations for a specific department and month.
     */
    public function details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ]);

        $validator->validate();

        $monthKey = $request->input('month');
        $departmentId = (int) $request->input('department_id');

        $monthDate = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
        $startDate = $monthDate->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $monthDate->copy()->endOfMonth()->format('Y-m-d');

        $department = Department::find($departmentId);

        $rows = $this->buildConsultationDetailsQuery($startDate, $endDate, $departmentId)
            ->orderByDesc('record_date')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.reports.consultations.details', [
            'rows' => $rows,
            'department' => $department,
            'monthKey' => $monthKey,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
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
                WHEN COUNT(DISTINCT department_id) > 1 THEN 'Multiple departments'
                ELSE COALESCE(MAX(department_name), 'N/A')
              END"
            : "COALESCE(department_name, 'N/A')";

        $departmentIdExpression = $groupBy === 'month'
            ? "CASE
                WHEN COUNT(DISTINCT department_id) > 1 THEN NULL
                ELSE MAX(department_id)
              END"
            : "department_id";

        $appointmentRows = Appointment::query()
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('booking_services', 'appointments.service_id', '=', 'booking_services.id')
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate])
            ->whereIn('appointments.type', ['consultation', 'followup'])
            ->whereNotIn('appointments.status', ['pending', 'cancelled'])
            ->selectRaw("
                appointments.appointment_date as record_date,
                appointments.department_id as department_id,
                departments.name as department_name,
                $durationExpression as duration_minutes
            ");

        if (!empty($departmentId)) {
            $appointmentRows->where('appointments.department_id', $departmentId);
        }

        $query = DB::query()
            ->fromSub($appointmentRows, 'consultation_rows')
            ->selectRaw("
                DATE_FORMAT(MIN(record_date), '%Y-%m') as month_key,
                DATE_FORMAT(MIN(record_date), '%M %Y') as month_name,
                {$departmentNameExpression} as department_name,
                {$departmentIdExpression} as department_id,
                COUNT(*) as total_consultations,
                SUM(duration_minutes) as total_duration_minutes
            ")
            ->orderByRaw("DATE_FORMAT(MIN(record_date), '%Y-%m')");

        if ($groupBy === 'month') {
            $query->groupByRaw("DATE_FORMAT(record_date, '%Y-%m')");
        } else {
            $query->groupByRaw("DATE_FORMAT(record_date, '%Y-%m'), department_id, department_name");
        }

        return $query;
    }

    private function buildConsultationDetailsQuery(string $startDate, string $endDate, int $departmentId)
    {
        $durationExpression = "CASE
            WHEN appointments.check_in_time IS NOT NULL AND appointments.check_out_time IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, appointments.check_in_time, appointments.check_out_time)
            WHEN booking_services.default_duration_minutes IS NOT NULL
                THEN booking_services.default_duration_minutes
            ELSE 30
        END";

        $appointmentRows = Appointment::query()
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('booking_services', 'appointments.service_id', '=', 'booking_services.id')
            ->whereBetween('appointments.appointment_date', [$startDate, $endDate])
            ->where('appointments.department_id', $departmentId)
            ->whereIn('appointments.type', ['consultation', 'followup'])
            ->whereNotIn('appointments.status', ['pending', 'cancelled'])
            ->selectRaw("
                appointments.id as appointment_id,
                appointments.appointment_date as record_date,
                CONCAT(patients.first_name, ' ', patients.last_name) as patient_name,
                CONCAT(doctors.first_name, ' ', doctors.last_name) as doctor_name,
                departments.name as department_name,
                appointments.type as consultation_type,
                $durationExpression as duration_minutes
            ");

        return DB::query()->fromSub($appointmentRows, 'consultation_details');
    }
}

