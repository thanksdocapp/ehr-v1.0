<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        
        // Default to current month if no dates provided
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->input('department_id');

        // Build query
        $query = Appointment::with(['doctor', 'department', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'completed'); // Only completed appointments

        // Filter by department if selected
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Group by month
        $appointments = $query->get();
        
        $reportData = $appointments->groupBy(function ($appointment) {
            return Carbon::parse($appointment->appointment_date)->format('Y-m');
        })
        ->map(function ($monthAppointments, $monthKey) {
            $monthName = Carbon::createFromFormat('Y-m', $monthKey)->format('F Y');
            $totalDuration = $monthAppointments->sum(function ($apt) {
                // Try to get duration from check_in/check_out, otherwise use service duration
                if ($apt->check_in_time && $apt->check_out_time) {
                    return $apt->check_in_time->diffInMinutes($apt->check_out_time);
                }
                // Fallback to service duration if available
                if ($apt->service && $apt->service->default_duration_minutes) {
                    return $apt->service->default_duration_minutes;
                }
                // Default 30 minutes if nothing available
                return 30;
            });
            $count = $monthAppointments->count();
            
            return [
                'month_key' => $monthKey,
                'month_name' => $monthName,
                'total_consultations' => $count,
                'total_duration_minutes' => $totalDuration,
                'average_duration_minutes' => $count > 0 ? round($totalDuration / $count, 2) : 0,
                'department_name' => $monthAppointments->first()->department->name ?? 'N/A',
            ];
        })
        ->sortBy('month_key')
        ->values();

        // Paginate
        $perPage = 12; // Show 12 months per page
        $currentPage = $request->get('page', 1);
        $items = $reportData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        // Create paginator manually
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $reportData->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );

        // Summary stats
        $summary = [
            'total_consultations' => $reportData->sum('total_consultations'),
            'total_duration_hours' => round($reportData->sum('total_duration_minutes') / 60, 2),
            'average_duration_minutes' => $reportData->count() > 0 
                ? round($reportData->sum('total_duration_minutes') / $reportData->sum('total_consultations'), 2)
                : 0,
        ];

        return view('admin.reports.consultations.index', compact(
            'paginator',
            'departments',
            'startDate',
            'endDate',
            'departmentId',
            'summary'
        ));
    }

    /**
     * Export consultations report to Excel (CSV)
     */
    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->input('department_id');

        // Build query
        $query = Appointment::with(['doctor', 'department', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $reportData = $query->get()
            ->groupBy(function ($appointment) {
                return Carbon::parse($appointment->appointment_date)->format('Y-m');
            })
            ->map(function ($monthAppointments, $monthKey) {
                $monthName = Carbon::createFromFormat('Y-m', $monthKey)->format('F Y');
                $totalDuration = $monthAppointments->sum(function ($apt) {
                    if ($apt->check_in_time && $apt->check_out_time) {
                        return $apt->check_in_time->diffInMinutes($apt->check_out_time);
                    }
                    if ($apt->service && $apt->service->default_duration_minutes) {
                        return $apt->service->default_duration_minutes;
                    }
                    // Default 30 minutes if nothing available
                    return 30;
                });
                $count = $monthAppointments->count();
                
                return [
                    'Month' => $monthName,
                    'Department' => $monthAppointments->first()->department->name ?? 'N/A',
                    'Total Consultations' => $count,
                    'Total Duration (Minutes)' => $totalDuration,
                    'Total Duration (Hours)' => round($totalDuration / 60, 2),
                    'Average Duration (Minutes)' => $count > 0 ? round($totalDuration / $count, 2) : 0,
                ];
            })
            ->sortBy(function ($item, $key) {
                return $key;
            })
            ->values();

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
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->input('department_id');

        // Build query
        $query = Appointment::with(['doctor', 'department', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
            $department = Department::find($departmentId);
        } else {
            $department = null;
        }

        $reportData = $query->get()
            ->groupBy(function ($appointment) {
                return Carbon::parse($appointment->appointment_date)->format('Y-m');
            })
            ->map(function ($monthAppointments, $monthKey) {
                $monthName = Carbon::createFromFormat('Y-m', $monthKey)->format('F Y');
                $totalDuration = $monthAppointments->sum(function ($apt) {
                    if ($apt->check_in_time && $apt->check_out_time) {
                        return $apt->check_in_time->diffInMinutes($apt->check_out_time);
                    }
                    if ($apt->service && $apt->service->default_duration_minutes) {
                        return $apt->service->default_duration_minutes;
                    }
                    // Default 30 minutes if nothing available
                    return 30;
                });
                $count = $monthAppointments->count();
                
                return [
                    'month_key' => $monthKey,
                    'month_name' => $monthName,
                    'department_name' => $monthAppointments->first()->department->name ?? 'N/A',
                    'total_consultations' => $count,
                    'total_duration_minutes' => $totalDuration,
                    'total_duration_hours' => round($totalDuration / 60, 2),
                    'average_duration_minutes' => $count > 0 ? round($totalDuration / $count, 2) : 0,
                ];
            })
            ->sortBy('month_key')
            ->values();

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
}

