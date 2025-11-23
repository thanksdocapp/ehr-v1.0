<?php

namespace App\Policies;

use App\Models\PatientAlert;
use App\Models\Patient;
use App\Models\User;

class PatientAlertPolicy
{
    /**
     * Determine whether the user can view any alerts for a patient.
     */
    public function viewAny(User $user, Patient $patient): bool
    {
        // Patients never see internal alerts
        if ($user->role === 'patient') {
            return false;
        }

        // Admin, doctors, nurses, and staff can view alerts
        return in_array($user->role, ['admin', 'doctor', 'nurse', 'staff']) || $user->is_admin;
    }

    /**
     * Determine whether the user can view the alert.
     */
    public function view(User $user, PatientAlert $alert): bool
    {
        // Patients never see internal alerts
        if ($user->role === 'patient') {
            return false;
        }

        // Admin sees everything
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctors and nurses/staff
        if (in_array($user->role, ['doctor', 'nurse', 'staff'])) {
            if ($alert->restricted) {
                // Only doctors and admin can see restricted alerts
                return $user->role === 'doctor' || $user->is_admin;
            }

            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create alerts for a patient.
     */
    public function create(User $user, Patient $patient): bool
    {
        // Admin and doctors can create any alert
        if ($user->is_admin || $user->role === 'admin' || $user->role === 'doctor') {
            return true;
        }

        // Nurses/staff can create non-restricted alerts
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true; // Type/code restriction will be enforced in controller/validation
        }

        return false;
    }

    /**
     * Determine whether the user can update the alert.
     */
    public function update(User $user, PatientAlert $alert): bool
    {
        // Admin can update all
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctors can update alerts they created OR clinical alerts (unless restricted)
        if ($user->role === 'doctor') {
            if ($alert->restricted && !$this->canSeeRestricted($user, $alert)) {
                return false;
            }

            return true;
        }

        // Nurses/staff: only their own non-restricted communication/admin alerts
        if (in_array($user->role, ['nurse', 'staff'])) {
            if ($alert->restricted) {
                return false;
            }

            // Only allow updates to communication/admin alerts
            // Optionally: only if they created it
            return in_array($alert->type, ['communication', 'admin']) 
                && ($alert->created_by === $user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the alert.
     */
    public function delete(User $user, PatientAlert $alert): bool
    {
        // Deletion is sensitive; restrict to admin only
        // In practice, we'll use deactivation instead of hard deletion
        return $user->is_admin || $user->role === 'admin';
    }

    /**
     * Determine whether the user can deactivate/reactivate the alert.
     */
    public function toggleActive(User $user, PatientAlert $alert): bool
    {
        // Similar to update permissions
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        if ($user->role === 'doctor') {
            if ($alert->restricted && !$this->canSeeRestricted($user, $alert)) {
                return false;
            }
            return true;
        }

        if (in_array($user->role, ['nurse', 'staff'])) {
            if ($alert->restricted) {
                return false;
            }
            return in_array($alert->type, ['communication', 'admin']) 
                && ($alert->created_by === $user->id);
        }

        return false;
    }

    /**
     * Check if user can see restricted alerts.
     * Restricted safeguarding alerts visible to admin + doctors.
     */
    protected function canSeeRestricted(User $user, PatientAlert $alert): bool
    {
        // Admin and doctors can see restricted alerts
        return $user->is_admin || $user->role === 'admin' || $user->role === 'doctor';
    }
}
