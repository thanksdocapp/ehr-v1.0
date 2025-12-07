<?php

namespace App\Policies;

use App\Models\DocumentTemplate;
use App\Models\User;

class DocumentTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     * Note: Actual filtering by ownership happens at query level via scopeOwnedBy().
     */
    public function viewAny(User $user): bool
    {
        // Admin can see all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor, nurse, staff can view templates (filtered by ownership at query level)
        if (in_array($user->role, ['doctor', 'nurse', 'staff'])) {
            return true;
        }

        // Patient has no access to templates
        return false;
    }

    /**
     * Determine whether the user can view the model.
     * Doctors can only view their own templates or system templates created by admin.
     */
    public function view(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Admin can see all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can view their own templates or system templates (if active)
        if ($user->role === 'doctor') {
            // Check ownership - can view own templates regardless of active status
            if ($documentTemplate->created_by === $user->id) {
                return true;
            }
            // Can view system templates only if active
            if ($documentTemplate->isSystemTemplate() && $documentTemplate->is_active) {
                return true;
            }
            return false;
        }

        // Nurse/Staff can view system templates if active
        if (in_array($user->role, ['nurse', 'staff'])) {
            return $documentTemplate->is_active && $documentTemplate->isSystemTemplate();
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

        // Doctor can create their own templates
        if ($user->role === 'doctor') {
            return true;
        }

        // Nurses/staff cannot create templates
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Doctors can only update their own templates.
     */
    public function update(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Admin can update all templates
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can only update their own templates
        if ($user->role === 'doctor') {
            return $documentTemplate->created_by === $user->id;
        }

        // Nurses/staff cannot update templates
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Admin can delete any template. Doctors can only delete their own.
     */
    public function delete(User $user, DocumentTemplate $documentTemplate): bool
    {
        // Admin can delete any template
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Doctor can delete their own templates only
        if ($user->role === 'doctor') {
            return $documentTemplate->created_by === $user->id;
        }

        return false;
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
