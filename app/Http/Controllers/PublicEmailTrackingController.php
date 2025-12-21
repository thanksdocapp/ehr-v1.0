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

                if (isset($metadata['tracking_token']) && hash_equals((string) $metadata['tracking_token'], (string) $token)) {
                    if (!$emailLog->wasOpened()) {
                        $emailLog->markAsOpened();
                    }
                }
            }
        } catch (\Throwable $e) {
            // Always return the pixel to avoid breaking email rendering.
        }

        // 1x1 transparent GIF
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}


