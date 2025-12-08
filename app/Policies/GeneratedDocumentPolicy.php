<?php

namespace App\Policies;

use App\Models\GeneratedDocument;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneratedDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any generated documents.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'doctor']) || $user->is_admin;
    }

    /**
     * Determine whether the user can view the generated document.
     * Doctors can only view documents they generated.
     * Admins can view all.
     */
    public function view(User $user, GeneratedDocument $document): bool
    {
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $document->generated_by === $user->id;
    }

    /**
     * Determine whether the user can create documents for a patient.
     */
    public function create(User $user, ?Patient $patient = null): bool
    {
        return in_array($user->role, ['admin', 'doctor']) || $user->is_admin;
    }

    /**
     * Determine whether the user can update the document.
     * Only draft documents can be updated.
     */
    public function update(User $user, GeneratedDocument $document): bool
    {
        if (!$document->isDraft()) {
            return false;
        }

        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $document->generated_by === $user->id;
    }

    /**
     * Determine whether the user can delete the document.
     * Only draft or void documents can be deleted.
     */
    public function delete(User $user, GeneratedDocument $document): bool
    {
        if (!$document->isDraft() && !$document->isVoid()) {
            return false;
        }

        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $document->generated_by === $user->id;
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, GeneratedDocument $document): bool
    {
        return $this->view($user, $document);
    }

    /**
     * Determine whether the user can send the document.
     * Only final documents can be sent.
     */
    public function send(User $user, GeneratedDocument $document): bool
    {
        if (!$document->isFinal()) {
            return false;
        }

        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $document->generated_by === $user->id;
    }

    /**
     * Determine whether the user can finalize the document.
     */
    public function finalize(User $user, GeneratedDocument $document): bool
    {
        if (!$document->isDraft()) {
            return false;
        }

        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $document->generated_by === $user->id;
    }

    /**
     * Determine whether the user can void the document.
     */
    public function void(User $user, GeneratedDocument $document): bool
    {
        // Cannot void already voided documents
        if ($document->isVoid()) {
            return false;
        }

        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $document->generated_by === $user->id;
    }
}
