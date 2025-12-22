<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;

class PublicEmailTrackingController extends Controller
{
    /**
     * Track email open (called by a 1x1 tracking pixel embedded in the email).
     * This route must be public because patients are not authenticated.
     */
    public function track(string $token, int $id)
    {
        try {
            $emailLog = EmailLog::find($id);

            if ($emailLog) {
                $metadata = $emailLog->metadata ?? [];

                // Check if tracking token matches (using hash_equals for timing attack protection)
                if (isset($metadata['tracking_token'])) {
                    $storedToken = (string) $metadata['tracking_token'];
                    $providedToken = (string) $token;
                    
                    if (hash_equals($storedToken, $providedToken)) {
                        // Mark as opened if not already opened
                        if (is_null($emailLog->opened_at)) {
                            $emailLog->update(['opened_at' => now()]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Always return the pixel to avoid breaking email rendering.
            // Log error for debugging
            \Illuminate\Support\Facades\Log::warning('Email tracking error', [
                'error' => $e->getMessage(),
                'token' => $token,
                'id' => $id,
            ]);
        }

        // 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}


