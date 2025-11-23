<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserActivityController extends Controller
{
    /**
     * Display a listing of user activities.
     */
    public function index(Request $request)
    {
        $query = UserActivity::with('user')->latest();

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $activities = $query->paginate(25)->withQueryString();

        // Get filter options
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        $actions = UserActivity::distinct()->pluck('action')->sort();
        $severities = ['low', 'medium', 'high', 'critical'];

        // Get statistics
        $stats = $this->getActivityStats();

        return view('admin.user-activities.index', compact(
            'activities', 'users', 'actions', 'severities', 'stats'
        ));
    }

    /**
     * Display the specified activity.
     */
    public function show(UserActivity $activity)
    {
        $activity->load('user');
        return view('admin.user-activities.show', compact('activity'));
    }

    /**
     * Get activity statistics.
     */
    public function getStats()
    {
        $stats = [
            'total_activities' => UserActivity::count(),
            'today_activities' => UserActivity::whereDate('created_at', today())->count(),
            'this_week_activities' => UserActivity::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'critical_activities' => UserActivity::where('severity', 'critical')->count(),
            'high_activities' => UserActivity::where('severity', 'high')->count(),
            'failed_logins' => UserActivity::where('action', 'failed_login')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count(),
            'most_active_users' => UserActivity::select('user_id', DB::raw('count(*) as activity_count'))
                ->with('user:id,name,email')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->groupBy('user_id')
                ->orderBy('activity_count', 'desc')
                ->limit(5)
                ->get(),
            'recent_critical' => UserActivity::with('user:id,name,email')
                ->where('severity', 'critical')
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get activity statistics for dashboard.
     */
    private function getActivityStats()
    {
        return [
            'total' => UserActivity::count(),
            'today' => UserActivity::whereDate('created_at', today())->count(),
            'this_week' => UserActivity::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'critical' => UserActivity::where('severity', 'critical')->count(),
            'high' => UserActivity::where('severity', 'high')->count(),
            'medium' => UserActivity::where('severity', 'medium')->count(),
            'low' => UserActivity::where('severity', 'low')->count(),
        ];
    }

    /**
     * Get daily activity chart data.
     */
    public function getDailyChart(Request $request)
    {
        $days = $request->get('days', 7);
        
        $activities = UserActivity::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                'severity'
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date', 'severity')
            ->orderBy('date')
            ->get();

        // Format data for chart
        $chartData = [];
        $dates = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;
            $chartData[$date] = [
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'critical' => 0,
            ];
        }

        foreach ($activities as $activity) {
            if (isset($chartData[$activity->date])) {
                $chartData[$activity->date][$activity->severity] = $activity->count;
            }
        }

        return response()->json([
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Low',
                    'data' => array_column($chartData, 'low'),
                    'backgroundColor' => 'rgba(40, 167, 69, 0.2)',
                    'borderColor' => 'rgba(40, 167, 69, 1)',
                ],
                [
                    'label' => 'Medium',
                    'data' => array_column($chartData, 'medium'),
                    'backgroundColor' => 'rgba(255, 193, 7, 0.2)',
                    'borderColor' => 'rgba(255, 193, 7, 1)',
                ],
                [
                    'label' => 'High',
                    'data' => array_column($chartData, 'high'),
                    'backgroundColor' => 'rgba(220, 53, 69, 0.2)',
                    'borderColor' => 'rgba(220, 53, 69, 1)',
                ],
                [
                    'label' => 'Critical',
                    'data' => array_column($chartData, 'critical'),
                    'backgroundColor' => 'rgba(108, 117, 125, 0.2)',
                    'borderColor' => 'rgba(108, 117, 125, 1)',
                ],
            ]
        ]);
    }

    /**
     * Export activities to CSV.
     */
    public function export(Request $request)
    {
        $query = UserActivity::with('user')->latest();

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->get();

        $filename = 'user_activities_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'User',
                'Email',
                'Action',
                'Description',
                'Model Type',
                'Model ID',
                'IP Address',
                'User Agent',
                'Severity',
                'Created At'
            ]);

            // Add data rows
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->user->name ?? 'Unknown',
                    $activity->user->email ?? 'Unknown',
                    $activity->action,
                    $activity->description,
                    $activity->model_type,
                    $activity->model_id,
                    $activity->ip_address,
                    $activity->user_agent,
                    $activity->severity,
                    $activity->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clean old activities.
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 90);
        
        $deleted = UserActivity::where('created_at', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old activity records (older than {$days} days).",
            'deleted_count' => $deleted
        ]);
    }
}
