<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends BaseApiController
{
    /**
     * Patient registration via API.
     */
    public function registerPatient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:patients,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = Patient::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'patient_id' => $this->generatePatientId(),
                'is_active' => true,
            ]);

            $token = $patient->createToken('mobile-app')->plainTextToken;

            $data = [
                'patient' => $patient,
                'token' => $token,
                'token_type' => 'Bearer'
            ];

            return $this->sendCreated($data, 'Patient registered successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Patient login via API.
     */
    public function loginPatient(Request $request)
    {
        // Check if patient login is disabled
        $patientLoginEnabled = \App\Models\Setting::get('patient_login_enabled', true);
        
        if (!$patientLoginEnabled) {
            return $this->sendError('Patient login is currently disabled. Please contact your healthcare provider for assistance.', [], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('patient')->attempt($credentials)) {
            $patient = Auth::guard('patient')->user();
            
            if (!$patient->is_active) {
                return $this->sendError('Account is deactivated', [], 403);
            }

            $token = $patient->createToken('mobile-app')->plainTextToken;

            $data = [
                'patient' => $patient,
                'token' => $token,
                'token_type' => 'Bearer'
            ];

            return $this->sendResponse($data, 'Login successful');
        }

        return $this->sendError('Invalid credentials', [], 401);
    }

    /**
     * Staff/Admin login via API.
     */
    public function loginStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if (!$user->is_active) {
                return $this->sendError('Account is deactivated', [], 403);
            }

            $token = $user->createToken('mobile-app')->plainTextToken;

            $data = [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->is_admin ? 'admin' : 'staff'
            ];

            return $this->sendResponse($data, 'Login successful');
        }

        return $this->sendError('Invalid credentials', [], 401);
    }

    /**
     * Logout (Revoke token).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->sendResponse(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Logout failed');
        }
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        return $this->sendResponse($user, 'Profile retrieved successfully');
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $userType = $user instanceof Patient ? 'patient' : 'user';

        $rules = [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
        ];

        if ($userType === 'patient') {
            $rules = array_merge($rules, [
                'date_of_birth' => 'sometimes|required|date',
                'gender' => 'sometimes|required|in:male,female,other',
                'address' => 'sometimes|nullable|string|max:500',
                'emergency_contact_name' => 'sometimes|nullable|string|max:255',
                'emergency_contact_phone' => 'sometimes|nullable|string|max:20',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user->update($request->only(array_keys($rules)));
            return $this->sendUpdated($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Profile update failed');
        }
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect', [], 400);
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return $this->sendResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Password change failed');
        }
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('mobile-app')->plainTextToken;

            $data = [
                'token' => $token,
                'token_type' => 'Bearer'
            ];

            return $this->sendResponse($data, 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Token refresh failed');
        }
    }

    /**
     * Generate unique patient ID.
     */
    private function generatePatientId()
    {
        do {
            $patientId = 'P' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('patient_id', $patientId)->exists());

        return $patientId;
    }
}
