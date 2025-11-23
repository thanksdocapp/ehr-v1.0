<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransferCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransferCodeController extends Controller
{
    /**
     * Verify transfer code
     */
    public function verifyCode(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6'
            ]);

            $user = Auth::user();
            $code = $request->code;

            // Find any active transfer code for this user that matches the code
            $transferCode = TransferCode::where('user_id', $user->id)
                ->where('code', $code)
                ->where('is_active', true)
                ->first();

            if (!$transferCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code. Please check the code and try again.'
                ], 400);
            }

            // Code is valid
            return response()->json([
                'success' => true,
                'message' => 'Code verified successfully.',
                'code_type' => $transferCode->code_type,
                'verified_at' => now()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Transfer code verification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during verification. Please try again.'
            ], 500);
        }
    }

    /**
     * Get active transfer codes for the current user
     */
    public function getActiveCodes(Request $request)
    {
        try {
            $user = Auth::user();

            $activeCodes = TransferCode::where('user_id', $user->id)
                ->where('is_active', true)
                ->select('code_type', 'is_active', 'activated_at', 'message')
                ->get()
                ->mapWithKeys(function ($code) {
                    return [$code->code_type => [
                        'active' => $code->is_active,
                        'activated_at' => $code->activated_at,
                        'message' => $code->getAttributes()['message']  // Get raw attribute to avoid accessor
                    ]];
                });

            return response()->json([
                'success' => true,
                'active_codes' => $activeCodes,
                'has_active_codes' => $activeCodes->count() > 0,
                'required_codes' => $this->getRequiredCodes($activeCodes)
            ]);

        } catch (\Exception $e) {
            \Log::error('Get active codes error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching active codes.'
            ], 500);
        }
    }

    /**
     * Determine which codes are required for verification
     */
    private function getRequiredCodes($activeCodes)
    {
        $required = [];
        
        // Check which codes are active and need verification
        foreach (['COT', 'IMF', 'TCC'] as $codeType) {
            if (isset($activeCodes[$codeType]) && $activeCodes[$codeType]['active']) {
                // Use custom message if available and not empty, otherwise fall back to default
                $customMessage = $activeCodes[$codeType]['message'] ?? null;
                $message = !empty($customMessage) ? $customMessage : $this->getCodeTypeMessage($codeType);
                
                $required[] = [
                    'type' => $codeType,
                    'name' => $this->getCodeTypeName($codeType),
                    'message' => $message
                ];
            }
        }

        return $required;
    }

    /**
     * Get user-friendly name for code type
     */
    private function getCodeTypeName($codeType)
    {
        return match($codeType) {
            'COT' => 'Cost of Transfer Code',
            'IMF' => 'International Monetary Fund Code', 
            'TCC' => 'Tax Cost Code',
            default => $codeType
        };
    }

    /**
     * Get specific message for code type
     */
    private function getCodeTypeMessage($codeType)
    {
        return match($codeType) {
            'COT' => 'The Cost of Transfer Code is required to proceed with this wire transfer. This code covers the processing fees associated with international transfers.',
            'IMF' => 'The International Monetary Fund Code is required for international transfers exceeding certain thresholds as per regulatory compliance.',
            'TCC' => 'The Tax Cost Code is required to ensure proper tax documentation and compliance for this transfer.',
            default => 'This verification code is required to complete your transfer.'
        };
    }
}
