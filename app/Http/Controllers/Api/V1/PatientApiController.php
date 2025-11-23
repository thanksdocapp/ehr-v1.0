<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PatientApiController extends BaseApiController
{
    /**
     * Get patient profile (authenticated patient only).
     */
    public function profile(Request $request)
    {
        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            // Load related data
            $patient->load(['appointments.doctor.department']);
            
            // Add computed fields
            $patient->total_appointments = $patient->appointments->count();
            $patient->upcoming_appointments = $patient->appointments()
                ->where('appointment_date', '>=', now()->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->count();

            return $this->sendResponse($patient, 'Patient profile retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve profile: ' . $e->getMessage());
        }
    }

    /**
     * Update patient profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'date_of_birth' => 'sometimes|required|date|before:today',
            'gender' => 'sometimes|required|in:male,female,other',
            'address' => 'sometimes|nullable|string|max:500',
            'emergency_contact_name' => 'sometimes|nullable|string|max:255',
            'emergency_contact_phone' => 'sometimes|nullable|string|max:20',
            'emergency_contact_relationship' => 'sometimes|nullable|string|max:100',
            'blood_group' => 'sometimes|nullable|string|max:10',
            'allergies' => 'sometimes|nullable|string|max:1000',
            'medical_conditions' => 'sometimes|nullable|string|max:1000',
            'current_medications' => 'sometimes|nullable|string|max:1000',
            'insurance_provider' => 'sometimes|nullable|string|max:255',
            'insurance_policy_number' => 'sometimes|nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            $patient->update($request->only([
                'first_name', 'last_name', 'phone', 'date_of_birth', 'gender',
                'address', 'emergency_contact_name', 'emergency_contact_phone',
                'emergency_contact_relationship', 'blood_group', 'allergies',
                'medical_conditions', 'current_medications', 'insurance_provider',
                'insurance_policy_number'
            ]));

            return $this->sendUpdated($patient, 'Profile updated successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            // Delete old photo if exists
            if ($patient->profile_photo) {
                Storage::disk('public')->delete($patient->profile_photo);
            }

            // Store new photo
            $path = $request->file('photo')->store('patient-photos', 'public');
            
            $patient->update(['profile_photo' => $path]);

            $data = [
                'profile_photo' => $patient->profile_photo,
                'profile_photo_url' => Storage::url($path)
            ];

            return $this->sendResponse($data, 'Profile photo uploaded successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to upload photo: ' . $e->getMessage());
        }
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            if (!Hash::check($request->current_password, $patient->password)) {
                return $this->sendError('Current password is incorrect', [], 400);
            }

            $patient->update([
                'password' => Hash::make($request->new_password)
            ]);

            return $this->sendResponse(null, 'Password changed successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to change password: ' . $e->getMessage());
        }
    }

    /**
     * Get patient medical history.
     */
    public function getMedicalHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|in:appointments,prescriptions,tests'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            $type = $request->get('type', 'appointments');
            $perPage = $request->get('per_page', 15);

            switch ($type) {
                case 'appointments':
                    $history = $patient->appointments()
                        ->with(['doctor.department'])
                        ->where('status', 'completed')
                        ->orderBy('appointment_date', 'desc')
                        ->paginate($perPage);
                    break;
                
                case 'prescriptions':
                    // Assuming you have a prescriptions relationship
                    $history = $patient->prescriptions()
                        ->with(['doctor', 'medications'])
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage);
                    break;
                
                case 'tests':
                    // Assuming you have a medical tests relationship
                    $history = $patient->medicalTests()
                        ->with(['doctor'])
                        ->orderBy('test_date', 'desc')
                        ->paginate($perPage);
                    break;
                
                default:
                    return $this->sendError('Invalid history type', [], 400);
            }

            return $this->sendPaginatedResponse($history, 'Medical history retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve medical history: ' . $e->getMessage());
        }
    }

    /**
     * Get patient vital statistics.
     */
    public function getVitalStats(Request $request)
    {
        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            $stats = [
                'total_appointments' => $patient->appointments()->count(),
                'completed_appointments' => $patient->appointments()->where('status', 'completed')->count(),
                'cancelled_appointments' => $patient->appointments()->where('status', 'cancelled')->count(),
                'upcoming_appointments' => $patient->appointments()
                    ->where('appointment_date', '>=', now()->format('Y-m-d'))
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'total_doctors_consulted' => $patient->appointments()
                    ->select('doctor_id')
                    ->distinct()
                    ->count(),
                'total_departments_visited' => $patient->appointments()
                    ->select('department_id')
                    ->distinct()
                    ->count(),
                'last_appointment' => $patient->appointments()
                    ->where('status', 'completed')
                    ->orderBy('appointment_date', 'desc')
                    ->first(),
                'next_appointment' => $patient->appointments()
                    ->where('appointment_date', '>=', now()->format('Y-m-d'))
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('appointment_date', 'asc')
                    ->first()
            ];

            return $this->sendResponse($stats, 'Vital statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve vital statistics: ' . $e->getMessage());
        }
    }

    /**
     * Update emergency contact information.
     */
    public function updateEmergencyContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'emergency_contact_address' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            $patient->update($request->only([
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'emergency_contact_address'
            ]));

            return $this->sendUpdated($patient, 'Emergency contact updated successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to update emergency contact: ' . $e->getMessage());
        }
    }

    /**
     * Update medical information.
     */
    public function updateMedicalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blood_group' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:1000',
            'medical_conditions' => 'nullable|string|max:1000',
            'current_medications' => 'nullable|string|max:1000',
            'family_history' => 'nullable|string|max:1000',
            'lifestyle_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            $patient->update($request->only([
                'blood_group',
                'allergies',
                'medical_conditions',
                'current_medications',
                'family_history',
                'lifestyle_notes'
            ]));

            return $this->sendUpdated($patient, 'Medical information updated successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to update medical information: ' . $e->getMessage());
        }
    }

    /**
     * Delete patient account.
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|in:DELETE_MY_ACCOUNT'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $patient = $request->user();
            
            if (!$patient instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            if (!Hash::check($request->password, $patient->password)) {
                return $this->sendError('Password is incorrect', [], 400);
            }

            // Check for upcoming appointments
            $upcomingAppointments = $patient->appointments()
                ->where('appointment_date', '>=', now()->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($upcomingAppointments > 0) {
                return $this->sendError(
                    'Cannot delete account with upcoming appointments. Please cancel them first.',
                    [],
                    400
                );
            }

            // Delete profile photo if exists
            if ($patient->profile_photo) {
                Storage::disk('public')->delete($patient->profile_photo);
            }

            // Revoke all tokens
            $patient->tokens()->delete();

            // Soft delete the patient
            $patient->delete();

            return $this->sendResponse(null, 'Account deleted successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to delete account: ' . $e->getMessage());
        }
    }
}
