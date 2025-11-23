<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Display audit trail logs
     */
    public function index(Request $request)
    {
        $query = UserActivity::with('user')->latest();

        // Filter by event type (action)
        if ($request->filled('event_type')) {
            $query->where('action', $request->event_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(50)->withQueryString();
        
        // Get unique users for filter
        $users = User::orderBy('name')->get();
        
        // Get unique event types
        $eventTypes = [
            'login' => 'Login',
            'logout' => 'Logout',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'view' => 'View',
            'download' => 'Download',
            'export' => 'Export',
        ];
        
        // Get unique model types
        $modelTypes = UserActivity::select('model_type')
            ->distinct()
            ->whereNotNull('model_type')
            ->pluck('model_type')
            ->mapWithKeys(function ($type) {
                $parts = explode('\\', $type);
                $shortName = end($parts);
                return [$type => $shortName];
            })
            ->toArray();

        // Statistics
        $stats = [
            'total_logs' => UserActivity::count(),
            'today_logs' => UserActivity::whereDate('created_at', today())->count(),
            'unique_users' => UserActivity::distinct('user_id')->whereNotNull('user_id')->count('user_id'),
            'login_count' => UserActivity::where('action', 'login')->whereDate('created_at', today())->count(),
        ];

        return view('admin.advanced-reports.audit-trail', compact('logs', 'users', 'eventTypes', 'modelTypes', 'stats'));
    }

    /**
     * Show details of a specific audit log
     */
    public function show(UserActivity $auditLog)
    {
        $auditLog->load('user');
        
        return view('admin.advanced-reports.audit-detail', compact('auditLog'));
    }

    /**
     * Delete old audit logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:30'
        ]);

        $date = now()->subDays($request->days);
        $count = UserActivity::where('created_at', '<', $date)->delete();

        return redirect()->back()->with('success', "Deleted {$count} audit logs older than {$request->days} days");
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $query = UserActivity::with('user')->latest();

        // Apply same filters as index
        if ($request->filled('event_type')) {
            $query->where('action', $request->event_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'audit_trail_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, ['ID', 'Date Time', 'User', 'Action', 'Model', 'Description', 'IP Address', 'Severity']);

            foreach ($logs as $log) {
                $modelName = 'N/A';
                if ($log->model_type) {
                    $parts = explode('\\', $log->model_type);
                    $modelName = end($parts);
                }
                
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'System',
                    ucfirst($log->action),
                    $modelName,
                    $log->description,
                    $log->ip_address,
                    $log->severity,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
