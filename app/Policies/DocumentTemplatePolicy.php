<?php

namespace App\Policies;

use App\Models\DocumentTemplate;
use App\Models\User;

class DocumentTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin can see all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor, nurse, staff can view active templates
        if (in_array($user->role, ['doctor', 'nurse', 'staff'])) {
            return true;
        }

        // Patient has no access to templates
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Admin can see all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor, nurse, staff can view active templates
        if (in_array($user->role, ['doctor', 'nurse', 'staff'])) {
            return $documentTemplate->is_active;
        }

        // Patient has no access to templates
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin can create all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can create clinical templates if allowed by configuration
        // For now, allow doctors to create templates
        if ($user->role === 'doctor') {
            return true;
        }

        // Nurses/staff may not modify templates unless explicitly allowed
        // For now, restrict to admin/doctor
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Admin can update all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can update clinical templates if allowed
        if ($user->role === 'doctor') {
            return true;
        }

        // Nurses/staff may not modify templates unless explicitly allowed
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Only admin can delete templates
        // In practice, we'll use deactivation instead
        return $user->is_admin || $user->role === 'admin';
    }

    /**
     * Determine whether the user can deactivate the template.
     */
    public function deactivate(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Same as update permissions
        return $this->update($user, $documentTemplate);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $this->update($user, $documentTemplate);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentTemplate $documentTemplate): bool
    {
        return $this->delete($user, $documentTemplate);
    }
}
