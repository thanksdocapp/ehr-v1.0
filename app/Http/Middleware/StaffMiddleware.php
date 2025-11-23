<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this area.');
        }

        $user = Auth::user();

        // Check if user has staff role/permissions
        // Assuming you have a role system or staff flag in your user model
        if (!$this->isStaffMember($user)) {
            abort(403, 'Access denied. Staff privileges required.');
        }

        // Add staff context to the request
        $request->attributes->set('user_context', 'staff');
        $request->attributes->set('user_role', 'staff');

        // Log staff access for security purposes
        \Log::info('Staff access', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'route' => $request->route()->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        return $next($request);
    }

    /**
     * Determine if the user is a staff member
     * 
     * @param mixed $user
     * @return bool
     */
    private function isStaffMember($user): bool
    {
        // Option 1: If you have a role column
        if (isset($user->role)) {
            return in_array($user->role, ['staff', 'nurse', 'receptionist', 'medical_assistant', 'doctor']);
        }

        // Option 2: If you have a staff-specific table or relationship
        if (method_exists($user, 'isStaff')) {
            return $user->isStaff();
        }

        // Option 3: If you have a is_staff boolean column
        if (isset($user->is_staff)) {
            return $user->is_staff;
        }

        // Option 4: Check based on user type or permissions
        if (isset($user->user_type)) {
            return $user->user_type === 'staff';
        }

        // Option 5: Check if user is not admin (fallback)
        // This assumes all non-admin authenticated users are staff
        if (isset($user->is_admin)) {
            return !$user->is_admin;
        }

        // Default: Allow access if authenticated (you may want to adjust this)
        return true;
    }

    /**
     * Get staff permissions for the authenticated user
     * 
     * @param mixed $user
     * @return array
     */
    public static function getStaffPermissions($user): array
    {
        // Define what staff members can do
        $basePermissions = [
            'patients' => ['view', 'create', 'edit'], // No delete
            'appointments' => ['view', 'create', 'edit', 'confirm', 'cancel'], // No delete
            'doctors' => ['view'], // Read-only
            'medical_records' => ['view', 'create'], // No edit/delete
            'prescriptions' => ['view', 'create'], // No edit/delete
            'lab_reports' => ['view'], // Read-only
            'billing' => ['view'], // Base permission
        ];

        // You can extend this based on specific staff roles
        $staffRole = $user->role ?? 'staff';
        
        switch ($staffRole) {
            case 'nurse':
                $basePermissions['medical_records'][] = 'edit';
                $basePermissions['prescriptions'][] = 'edit';
                break;
                
            case 'receptionist':
                // Receptionists might have more appointment management rights
                $basePermissions['appointments'][] = 'reschedule';
                $basePermissions['billing'][] = 'create';
                break;
                
            case 'doctor':
                // Doctors can create and view bills
                $basePermissions['billing'][] = 'create';
                break;
                
            case 'medical_assistant':
                $basePermissions['lab_reports'][] = 'create';
                break;
        }

        return $basePermissions;
    }
}
