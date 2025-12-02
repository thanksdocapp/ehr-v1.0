<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Patient;

class CheckGuestRestrictions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get patient ID from route or request
        $patientId = $request->route('patient')?->id ?? $request->input('patient_id');
        
        if ($patientId) {
            $patient = Patient::find($patientId);
            
            if ($patient && $patient->is_guest) {
                // Check if this is a restricted action
                $restrictedActions = [
                    'medical-records.create',
                    'medical-records.store',
                    'medical-records.edit',
                    'medical-records.update',
                    'prescriptions.create',
                    'prescriptions.store',
                    'prescriptions.edit',
                    'prescriptions.update',
                ];
                
                $routeName = $request->route()->getName();
                
                if (in_array($routeName, $restrictedActions)) {
                    return redirect()->back()
                        ->with('error', 'This is a guest patient record. Please convert to full patient to perform this action.');
                }
            }
        }
        
        return $next($request);
    }
}

