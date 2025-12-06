<?php

namespace App\Http\Controllers;

use App\Models\DocumentDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentTrackingController extends Controller
{
    /**
     * Track email open via tracking pixel.
     * Returns a 1x1 transparent GIF image.
     */
    public function trackOpen(Request $request, string $token)
    {
        try {
            $delivery = DocumentDelivery::findByTrackingToken($token);

            if ($delivery) {
                $delivery->markAsOpened();

                // Log the open event
                \Log::info('Document delivery opened', [
                    'delivery_id' => $delivery->id,
                    'document_id' => $delivery->patient_document_id,
                    'patient_id' => $delivery->patient_id,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to track document open', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }

        // Return a 1x1 transparent GIF
        $transparentGif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($transparentGif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    /**
     * Track link click.
     */
    public function trackClick(Request $request, string $token)
    {
        try {
            $delivery = DocumentDelivery::findByTrackingToken($token);

            if ($delivery) {
                // Update meta with click info
                $meta = $delivery->meta ?? [];
                $meta['clicked_at'] = now()->toIso8601String();
                $meta['click_ip'] = $request->ip();

                $delivery->update(['meta' => $meta]);

                // Log the click event
                \Log::info('Document delivery link clicked', [
                    'delivery_id' => $delivery->id,
                    'document_id' => $delivery->patient_document_id,
                    'patient_id' => $delivery->patient_id,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to track document click', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }

        // Redirect to the actual destination
        $redirectUrl = $request->query('url', url('/'));
        return redirect($redirectUrl);
    }
}
