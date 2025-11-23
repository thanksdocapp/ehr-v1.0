<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any patients.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // Other roles (doctors, staff) can view patients based on visibility rules
        // The scopeVisibleTo will handle the filtering
        return true;
    }

    /**
     * Determine whether the user can view the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        // Admins can view all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // Use the patient's isVisibleTo method to check visibility
        return $patient->isVisibleTo($user);
    }

    /**
     * Determine whether the user can create patients.
     */
    public function create(User $user): bool
    {
        // Admins and staff with appropriate roles can create patients
        return $user->is_admin || 
               $user->role === 'admin' || 
               in_array($user->role, ['doctor', 'nurse', 'receptionist', 'staff']);
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        // Admins can update all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // Staff can update patients they can view
        return $patient->isVisibleTo($user);
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        // Only admins can delete patients
        return $user->is_admin || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the patient.
     */
    public function restore(User $user, Patient $patient): bool
    {
        // Only admins can restore patients
        return $user->is_admin || $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the patient.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        // Only admins can permanently delete patients
        return $user->is_admin || $user->role === 'admin';
    }
}
