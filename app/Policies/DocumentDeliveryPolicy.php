<?php

namespace App\Policies;

use App\Models\DocumentDelivery;
use App\Models\PatientDocument;
use App\Models\User;

class DocumentDeliveryPolicy
{
    /**
     * Determine whether the user can view any deliveries for a document.
     */
    public function viewAny(User $user, PatientDocument $patientDocument): bool
    {
        // Admin and Doctor can view deliveries
        if ($user->is_admin || $user->role === 'admin' || $user->role === 'doctor') {
            return true;
        }

        // Nurse/Staff can view if permitted
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true; // Will be enforced at query level
        }

        // Patient can view deliveries referring to their own record (optional)
        // For now, restrict to staff
        return false;
    }

    /**
     * Determine whether the user can view the delivery.
     */
    public function view(User $user, DocumentDelivery $documentDelivery): bool
    {
        // Admin and Doctor can view deliveries
        if ($user->is_admin || $user->role === 'admin' || $user->role === 'doctor') {
            return true;
        }

        // Nurse/Staff can view if permitted
        if (in_array($user->role, ['nurse', 'staff'])) {
            return true;
        }

        // Patient can view deliveries for their own documents (if needed)
        // For now, restrict to staff
        return false;
    }

    /**
     * Determine whether the user can create/send deliveries.
     */
    public function create(User $user, PatientDocument $patientDocument): bool
    {
        // Use PatientDocumentPolicy to check if user can send the document
        // This is essentially the same as send permission
        return app(PatientDocumentPolicy::class)->send($user, $patientDocument);
    }

    /**
     * Determine whether the user can update the delivery.
     */
    public function update(User $user, DocumentDelivery $documentDelivery): bool
    {
        // Deliveries are generally immutable once created
        // Only admin can update (for corrections)
        return $user->is_admin || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the delivery.
     */
    public function delete(User $user, DocumentDelivery $documentDelivery): bool
    {
        // Only admin can delete deliveries
        return $user->is_admin || $user->role === 'admin';
    }
}
