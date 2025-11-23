<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the doctor dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ensure user has doctor role
        if (!$user->roles->contains('name', 'doctor')) {
            return redirect()->route('home')->with('error', 'Access denied. Doctor privileges required.');
        }

        // Get doctor-specific data
        $stats = [
            'total_patients' => 0, // You can implement these based on your needs
            'today_appointments' => 0,
            'pending_consultations' => 0,
            'completed_today' => 0,
        ];

        return view('doctor.dashboard', compact('stats'));
    }

    /**
     * Get dashboard statistics for AJAX requests.
     */
    public function getStats()
    {
        $user = Auth::user();
        
        // Return doctor-specific statistics
        return response()->json([
            'total_patients' => 0,
            'today_appointments' => 0,
            'pending_consultations' => 0,
            'completed_today' => 0,
        ]);
    }
}
