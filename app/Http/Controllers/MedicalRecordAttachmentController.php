<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecordAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MedicalRecordAttachmentController extends Controller
{
    /**
     * Authenticated user (admin guard takes precedence on admin routes).
     */
    protected function authUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::guard('admin')->user() ?? Auth::user();

        return $user;
    }

    /**
     * View a medical record attachment (inline for images/PDFs).
     * 
     * @param MedicalRecordAttachment $attachment
     * @return \Illuminate\Http\Response
     */
    public function view(MedicalRecordAttachment $attachment)
    {
        $user = $this->authUser();
        
        // Check if user has permission to access this file
        if (!$attachment->canAccess($user)) {
            abort(403, 'You do not have permission to access this file.');
        }

        $location = $attachment->resolveStorageLocation();
        if (! $location) {
            abort(404, 'File not found.');
        }

        if ($attachment->isExpired()) {
            abort(410, 'This file has expired and is no longer available.');
        }

        if ($attachment->virus_scan_status === 'infected') {
            abort(403, 'This file has been flagged as infected and cannot be viewed.');
        }

        \App\Models\UserActivity::log([
            'user_id' => $user->id,
            'action' => 'file_view',
            'model_type' => MedicalRecordAttachment::class,
            'model_id' => $attachment->id,
            'description' => "File viewed: {$attachment->file_name}",
            'severity' => 'low',
        ]);

        if ($location['disk'] === '_absolute') {
            $file = file_get_contents($location['path']);
            $mimeType = mime_content_type($location['path']) ?: ($attachment->file_type ?: 'application/octet-stream');
        } else {
            $disk = Storage::disk($location['disk']);
            $file = $disk->get($location['path']);
            $mimeType = $disk->mimeType($location['path']) ?: ($attachment->file_type ?: 'application/octet-stream');
        }

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $attachment->file_name . '"');
    }

    /**
     * Download a medical record attachment.
     * 
     * @param MedicalRecordAttachment $attachment
     * @return \Illuminate\Http\Response
     */
    public function download(MedicalRecordAttachment $attachment)
    {
        $user = $this->authUser();
        
        // Check if user has permission to access this file
        if (!$attachment->canAccess($user)) {
            abort(403, 'You do not have permission to access this file.');
        }

        $location = $attachment->resolveStorageLocation();
        if (! $location) {
            abort(404, 'File not found.');
        }

        if ($attachment->isExpired()) {
            abort(410, 'This file has expired and is no longer available.');
        }

        \App\Models\UserActivity::log([
            'user_id' => $user->id,
            'action' => 'file_download',
            'model_type' => MedicalRecordAttachment::class,
            'model_id' => $attachment->id,
            'description' => "File downloaded: {$attachment->file_name}",
            'severity' => 'low',
        ]);

        if ($location['disk'] === '_absolute') {
            return response()->download($location['path'], $attachment->file_name);
        }

        return Storage::disk($location['disk'])->download(
            $location['path'],
            $attachment->file_name
        );
    }

    /**
     * Get a signed URL for viewing/downloading a file.
     * 
     * @param MedicalRecordAttachment $attachment
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSignedUrl(MedicalRecordAttachment $attachment)
    {
        $user = $this->authUser();
        
        // Check if user has permission to access this file
        if (!$attachment->canAccess($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if file has expired
        if ($attachment->isExpired()) {
            return response()->json(['error' => 'File has expired'], 410);
        }

        try {
            $url = $attachment->getSignedUrl(60); // 60 minutes expiration
            
            return response()->json([
                'url' => $url,
                'expires_at' => now()->addMinutes(60)->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate signed URL'], 500);
        }
    }

    /**
     * Delete a medical record attachment.
     * 
     * @param MedicalRecordAttachment $attachment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MedicalRecordAttachment $attachment)
    {
        $user = $this->authUser();

        if (! $user) {
            abort(403, 'You do not have permission to delete this file.');
        }

        // Admin panel users or the uploader can delete
        $canDelete = $user->canViewMedicalRecordAttachments()
            || $attachment->uploaded_by === $user->id;
        if (! $canDelete) {
            abort(403, 'You do not have permission to delete this file.');
        }

        $fileName = $attachment->file_name;
        $medicalRecordId = $attachment->medical_record_id;
        
        // Log deletion
        \App\Models\UserActivity::log([
            'user_id' => $user->id,
            'action' => 'file_delete',
            'model_type' => MedicalRecordAttachment::class,
            'model_id' => $attachment->id,
            'description' => "File deleted: {$fileName}",
            'severity' => 'medium',
        ]);

        $attachment->delete();

        // Determine redirect route based on context
        $routePrefix = request()->route()->getPrefix();
        $routeName = request()->route()->getName();
        
        // Check if we're in admin context
        $authUser = $this->authUser();
        $isAdmin = str_contains($routePrefix, 'admin')
            || ($authUser && $authUser->canViewMedicalRecordAttachments());
        
        if ($isAdmin) {
            return redirect()->route('admin.medical-records.show', $medicalRecordId)
                ->with('success', 'File deleted successfully.');
        } else {
            return redirect()->route('staff.medical-records.show', $medicalRecordId)
                ->with('success', 'File deleted successfully.');
        }
    }
}
