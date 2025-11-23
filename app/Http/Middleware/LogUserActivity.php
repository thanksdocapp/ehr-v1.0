<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserActivity;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users (check all guards)
        $user = auth('admin')->user() ?? auth()->user();
        if ($user) {
            $this->logActivity($request, $response, $user);
        }

        return $response;
    }

    /**
     * Log user activity based on the request.
     */
    private function logActivity(Request $request, Response $response, $user)
    {
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();
        
        // Debug logging
        \Log::info('LogUserActivity Middleware', [
            'method' => $method,
            'path' => $path,
            'status' => $statusCode,
            'user_id' => $user->id,
        ]);

        // Skip logging for certain routes to avoid noise
        $skipPaths = [
            'admin/api/',
            'admin/notifications',
            'css/',
            'js/',
            'images/',
            'assets/',
            '/audit-trail',
            '/change-password',
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_contains($path, $skipPath)) {
                \Log::info('Skipped path', ['path' => $path, 'skip_pattern' => $skipPath]);
                return;
            }
        }
        
        // Only log important actions - skip simple GET requests (views)
        // But always log login/logout
        if ($method === 'GET' && !str_contains($path, '/login') && !str_contains($path, '/logout')) {
            \Log::info('Skipped GET request', ['path' => $path]);
            return;
        }
        
        // Only log successful operations (2xx and 3xx status codes)
        if ($statusCode >= 400) {
            \Log::info('Skipped due to error status', ['path' => $path, 'status' => $statusCode]);
            return;
        }
        
        \Log::info('About to log activity', ['method' => $method, 'path' => $path, 'status' => $statusCode]);

        // Determine action based on HTTP method and route
        $action = $this->determineAction($method, $path);
        $description = $this->generateDescription($method, $path, $statusCode);
        $severity = $this->determineSeverity($method, $path, $statusCode);

        // Extract model information if available
        $modelInfo = $this->extractModelInfo($request, $path);

        UserActivity::log([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'model_type' => $modelInfo['type'] ?? null,
            'model_id' => $modelInfo['id'] ?? null,
            'severity' => $severity,
        ]);
    }

    /**
     * Determine the action based on HTTP method and path.
     */
    private function determineAction(string $method, string $path): string
    {
        // Handle specific routes
        if (str_contains($path, '/login')) {
            return 'login';
        }
        if (str_contains($path, '/logout')) {
            return 'logout';
        }
        if (str_contains($path, '/download')) {
            return 'download';
        }
        if (str_contains($path, '/export')) {
            return 'export';
        }

        // Handle CRUD operations
        return match($method) {
            'GET' => str_contains($path, '/edit') ? 'view_edit_form' : 'view',
            'POST' => str_contains($path, '/login') ? 'login' : 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'action'
        };
    }

    /**
     * Generate a human-readable description.
     */
    private function generateDescription(string $method, string $path, int $statusCode): string
    {
        $resource = $this->extractResourceName($path);
        $action = $this->determineAction($method, $path);

        $description = match($action) {
            'view' => "Viewed {$resource}",
            'view_edit_form' => "Accessed edit form for {$resource}",
            'create' => "Created new {$resource}",
            'update' => "Updated {$resource}",
            'delete' => "Deleted {$resource}",
            'download' => "Downloaded {$resource}",
            'export' => "Exported {$resource}",
            'login' => "Logged into the system",
            'logout' => "Logged out of the system",
            default => "Performed {$action} on {$resource}"
        };

        if ($statusCode >= 400) {
            $description .= " (Failed - HTTP {$statusCode})";
        }

        return $description;
    }

    /**
     * Extract resource name from path.
     */
    private function extractResourceName(string $path): string
    {
        $segments = explode('/', trim($path, '/'));
        
        // Remove 'admin' or 'staff' prefix if present
        if (isset($segments[0]) && in_array($segments[0], ['admin', 'staff'])) {
            array_shift($segments);
        }

        $resource = $segments[0] ?? 'resource';
        
        // Convert to singular and human readable
        $resource = str_replace(['-', '_'], ' ', $resource);
        $resource = rtrim($resource, 's'); // Simple singularization
        
        return ucfirst($resource);
    }

    /**
     * Determine severity based on the action.
     */
    private function determineSeverity(string $method, string $path, int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'critical';
        }
        if ($statusCode >= 400) {
            return 'high';
        }

        return match($method) {
            'DELETE' => 'high',
            'POST', 'PUT', 'PATCH' => 'medium',
            'GET' => 'low',
            default => 'low'
        };
    }

    /**
     * Extract model information from the request.
     */
    private function extractModelInfo(Request $request, string $path): array
    {
        $segments = explode('/', trim($path, '/'));
        
        // Try to find numeric ID in path
        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                // Determine model type based on path
                $modelType = $this->guessModelType($path);
                return [
                    'type' => $modelType,
                    'id' => (int)$segment
                ];
            }
        }

        return [];
    }

    /**
     * Guess model type based on path.
     */
    private function guessModelType(string $path): ?string
    {
        $modelMap = [
            'users' => 'App\\Models\\User',
            'patients' => 'App\\Models\\Patient',
            'doctors' => 'App\\Models\\Doctor',
            'appointments' => 'App\\Models\\Appointment',
            'medical-records' => 'App\\Models\\MedicalRecord',
            'prescriptions' => 'App\\Models\\Prescription',
            'lab-reports' => 'App\\Models\\LabReport',
            'departments' => 'App\\Models\\Department',
        ];

        foreach ($modelMap as $path_key => $model) {
            if (str_contains($path, $path_key)) {
                return $model;
            }
        }

        return null;
    }
}
