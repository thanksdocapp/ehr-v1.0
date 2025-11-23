<?php

namespace App\Policies;

use App\Models\PatientDocument;
use App\Models\Patient;
use App\Models\User;

class PatientDocumentPolicy
{
    /**
     * Determine whether the user can view any documents for a patient.
     */
    public function viewAny(User $user, Patient $patient): bool
    {
        // Admin has full access
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can view documents for accessible patients
        if ($user->role === 'doctor') {
            // Use Patient visibility scope if available
            return true; // Will be enforced at query level
        }

        // Nurse/Staff can view documents
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true; // Will be enforced at query level
        }

        // Patient can view their own final documents if exposed via portal
        if ($user->role === 'patient') {
            // Check if patient record matches authenticated patient
            return false; // For now, handled separately in controller
        }

        return false;
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, PatientDocument $patientDocument): bool
    {
        // Admin has full access
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can view documents for accessible patients
        if ($user->role === 'doctor') {
            // Check patient visibility will be done at query level
            return true;
        }

        // Nurse/Staff can view documents
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true;
        }

        // Patient can view their own final documents
        if ($user->role === 'patient') {
            // Check if this is the patient's own document and it's final
            // This will need to be checked against authenticated patient
            return $patientDocument->status === 'final';
        }

        return false;
    }

    /**
     * Determine whether the user can create documents for a patient.
     */
    public function create(User $user, Patient $patient): bool
    {
        // Admin has full access
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can create documents for accessible patients
        if ($user->role === 'doctor') {
            return true;
        }

        // Nurse/Staff can create certain forms (assessments, consent)
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true; // Type restriction enforced in controller
        }

        // Patient cannot create documents
        return false;
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, PatientDocument $patientDocument): bool
    {
        // Cannot update final or void documents
        if ($patientDocument->isFinal() || $patientDocument->isVoid()) {
            return false;
        }

        // Admin has full access
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can update documents for accessible patients
        if ($user->role === 'doctor') {
            return true;
        }

        // Nurse/Staff can update certain forms (if they created them)
        if (in_array($user->role, ['nurse', 'staff'])) {
            // Only allow updates to their own documents
            return $patientDocument->created_by === $user->id;
        }

        // Patient cannot update documents
        return false;
    }

    /**
     * Determine whether the user can finalise the document.
     */
    public function finalise(User $user, PatientDocument $patientDocument): bool
    {
        // Can only finalise draft documents
        if (!$patientDocument->isDraft()) {
            return false;
        }

        // Only Admin and Doctor can finalise
        return ($user->is_admin || $user->role === 'admin' || $user->role === 'doctor');
    }

    /**
     * Determine whether the user can void the document.
     */
    public function void(User $user, PatientDocument $patientDocument): bool
    {
        // Admin and Doctor can void documents
        return ($user->is_admin || $user->role === 'admin' || $user->role === 'doctor');
    }

    /**
     * Determine whether the user can send the document.
     */
    public function send(User $user, PatientDocument $patientDocument): bool
    {
        // Can only send final documents
        if (!$patientDocument->isFinal()) {
            return false;
        }

        // Admin and Doctor can send documents
        if ($user->is_admin || $user->role === 'admin' || $user->role === 'doctor') {
            return true;
        }

        // Nurse/Staff can send certain documents if allowed (configurable)
        if (in_array($user->role, ['nurse', 'staff'])) {
            // For now, restrict to admin/doctor
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, PatientDocument $patientDocument): bool
    {
        // Only admin can delete documents
        // In practice, use void instead
        return $user->is_admin || $user->role === 'admin';
    }

    /**
     * Determine whether the user can download the document PDF.
     */
    public function download(User $user, PatientDocument $patientDocument): bool
    {
        // Same as view permissions
        return $this->view($user, $patientDocument);
    }
}
