<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the patient's profile.
     */
    public function index(): View
    {
        $patient = Auth::guard('patient')->user();
        return view('patient.profile.index', compact('patient'));
    }

    /**
     * Show the form for editing the patient's profile.
     */
    public function edit(): View
    {
        $patient = Auth::guard('patient')->user();
        return view('patient.profile.edit', compact('patient'));
    }

    /**
     * Update the patient's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $patient = Auth::guard('patient')->user();

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:patients,email,' . $patient->id],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_number' => ['nullable', 'string', 'max:255'],
            'allergies' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'notification_preferences' => ['nullable', 'array'],
            'notification_preferences.*' => ['nullable'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle password change
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $patient->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
            }

            $patient->password = Hash::make($request->new_password);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($patient->photo && Storage::disk('public')->exists('patients/' . $patient->photo)) {
                Storage::disk('public')->delete('patients/' . $patient->photo);
            }

            // Store new photo
            $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->storeAs('patients', $photoName, 'public');
            $patient->photo = $photoName;
        }

        // Update profile information
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'insurance_provider' => $request->insurance_provider,
            'insurance_number' => $request->insurance_number,
            'allergies' => $request->allergies ? explode(',', $request->allergies) : null,
            'medical_conditions' => $request->medical_conditions ? explode(',', $request->medical_conditions) : null,
            'notification_preferences' => $this->processNotificationPreferences($request->notification_preferences ?? []),
        ];

        // Include photo in update if it was uploaded
        if (isset($patient->photo)) {
            $updateData['photo'] = $patient->photo;
        }

        $patient->update($updateData);

        if ($request->filled('current_password')) {
            $patient->save(); // Save password separately
        }

        return redirect()->route('patient.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the patient's profile photo.
     */
    public function deletePhoto(): RedirectResponse
    {
        $patient = Auth::guard('patient')->user();

        if ($patient->photo && Storage::disk('public')->exists('patients/' . $patient->photo)) {
            Storage::disk('public')->delete('patients/' . $patient->photo);
            $patient->update(['photo' => null]);
        }

        return back()->with('success', 'Profile photo deleted successfully!');
    }

    /**
     * Process notification preferences from form input.
     *
     * @param array $preferences
     * @return array
     */
    protected function processNotificationPreferences(array $preferences): array
    {
        $processed = [];

        // Boolean preferences (convert string "0"/"1" to boolean)
        $booleanKeys = [
            'email_enabled',
            'sms_enabled',
            'push_enabled',
            'appointment_reminders',
            'lab_results',
            'prescription_updates',
            'billing_alerts',
            'health_tips',
            'promotional',
            'quiet_hours_enabled',
        ];

        foreach ($booleanKeys as $key) {
            if (isset($preferences[$key])) {
                $processed[$key] = (bool) $preferences[$key];
            }
        }

        // Time preferences
        if (isset($preferences['quiet_hours_start'])) {
            $processed['quiet_hours_start'] = $preferences['quiet_hours_start'];
        }
        if (isset($preferences['quiet_hours_end'])) {
            $processed['quiet_hours_end'] = $preferences['quiet_hours_end'];
        }

        return $processed;
    }
}
