<?php

namespace App\Policies;

use App\Models\PatientDocument;
use App\Models\Patient;
use App\Models\User;

class PatientDocumentPolicy
{
    /**
     * Determine whether the user can view any documents for a patient.
     * Note: Actual filtering by ownership happens at query level via scopeOwnedBy().
     */
    public function viewAny(User $user, Patient $patient): bool
    {
        // Admin has full access
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can view documents (filtered by ownership at query level)
        if ($user->role === 'doctor') {
            return true;
        }

        // Nurse/Staff can view documents (filtered at query level)
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true;
        }

        // Patient can view their own final documents if exposed via portal
        if ($user->role === 'patient') {
            return false; // For now, handled separately in controller
        }

        return false;
    }

    /**
     * Determine whether the user can view the document.
     * Doctors can only view documents they created.
     */
    public function view(User $user, PatientDocument $patientDocument): bool
    {
        // Admin has full access
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can only view documents they created
        if ($user->role === 'doctor') {
            return $patientDocument->created_by === $user->id;
        }

        // Nurse/Staff can only view documents they created
        if (in_array($user->role, ['nurse', 'staff'])) {
            return $patientDocument->created_by === $user->id;
        }

        // Patient can view their own final documents
        if ($user->role === 'patient') {
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
     * Doctors can only update documents they created.
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

        // Doctor can only update documents they created
        if ($user->role === 'doctor') {
            return $patientDocument->created_by === $user->id;
        }

        // Nurse/Staff can only update their own documents
        if (in_array($user->role, ['nurse', 'staff'])) {
            return $patientDocument->created_by === $user->id;
        }

        // Patient cannot update documents
        return false;
    }

    /**
     * Determine whether the user can finalise the document.
     * Doctors can only finalise documents they created.
     */
    public function finalise(User $user, PatientDocument $patientDocument): bool
    {
        // Can only finalise draft documents
        if (!$patientDocument->isDraft()) {
            return false;
        }

        // Admin can finalise any document
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can only finalise documents they created
        if ($user->role === 'doctor') {
            return $patientDocument->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can void the document.
     * Doctors can only void documents they created.
     */
    public function void(User $user, PatientDocument $patientDocument): bool
    {
        // Admin can void any document
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can only void documents they created
        if ($user->role === 'doctor') {
            return $patientDocument->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can send the document.
     * Doctors can only send documents they created.
     */
    public function send(User $user, PatientDocument $patientDocument): bool
    {
        // Can only send final documents
        if (!$patientDocument->isFinal()) {
            return false;
        }

        // Admin can send any document
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can only send documents they created
        if ($user->role === 'doctor') {
            return $patientDocument->created_by === $user->id;
        }

        // Nurse/Staff cannot send documents
        return false;
    }

    /**
     * Determine whether the user can delete the document.
     * Admin can delete any, doctors can delete their own.
     */
    public function delete(User $user, PatientDocument $patientDocument): bool
    {
        // Admin can delete any document
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can delete their own draft documents
        if ($user->role === 'doctor') {
            return $patientDocument->created_by === $user->id && $patientDocument->isDraft();
        }

        return false;
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
