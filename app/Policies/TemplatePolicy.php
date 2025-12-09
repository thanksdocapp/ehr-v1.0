<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any templates.
     * All authenticated staff can view the template list.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor', 'nurse', 'staff', 'receptionist']) || $user->is_admin;
    }

    /**
     * Determine whether the user can view the template.
     * Users can view their own templates or system templates.
     * Admins can view all templates.
     */
    public function view(User $user, Template $template): bool
    {
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Staff can view their own templates or system templates
        return $template->created_by === $user->id || $template->is_system;
    }

    /**
     * Determine whether the user can create templates.
     * Only doctors and admins can create templates.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']) || $user->is_admin;
    }

    /**
     * Determine whether the user can update the template.
     * Users can only update their own templates.
     * Admins can update all templates.
     */
    public function update(User $user, Template $template): bool
    {
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $template->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the template.
     * Users can only delete their own templates.
     * Admins can delete all templates.
     */
    public function delete(User $user, Template $template): bool
    {
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $template->created_by === $user->id;
    }

    /**
     * Determine whether the user can use the template to generate documents.
     * Users can use their own templates or system templates.
     * Admins can use all templates.
     */
    public function use(User $user, Template $template): bool
    {
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        // Template must be active
        if (!$template->is_active) {
            return false;
        }

        // Staff can use their own templates or system templates
        return $template->created_by === $user->id || $template->is_system;
    }

    /**
     * Determine whether the user can duplicate the template.
     */
    public function duplicate(User $user, Template $template): bool
    {
        return $this->view($user, $template) && $this->create($user);
    }

    /**
     * Determine whether the user can toggle template active status.
     */
    public function toggleActive(User $user, Template $template): bool
    {
        return $this->update($user, $template);
    }

    /**
     * Determine whether the user can mark template as system template.
     * Only admins can do this.
     */
    public function markAsSystem(User $user, Template $template): bool
    {
        return $user->is_admin || $user->role === 'admin';
    }
}
