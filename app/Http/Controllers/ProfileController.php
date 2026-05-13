<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        // Ensure doctor relationship is loaded with fresh data
        if ($user->role === 'doctor') {
            $user->load('doctor');
        }
        
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $postBookingUrlUpdate = false;
        $postBookingUrl = null;
        if ($user->role === 'doctor' && array_key_exists('post_booking_redirect_url', $validated)) {
            $postBookingUrlUpdate = true;
            $v = $validated['post_booking_redirect_url'];
            $postBookingUrl = ($v === null || $v === '') ? null : trim((string) $v);
            unset($validated['post_booking_redirect_url']);
        }

        $clinicPostBookingUrlUpdate = false;
        $clinicPostBookingUrl = null;
        if ($user->role === 'doctor' && array_key_exists('clinic_post_booking_redirect_url', $validated)) {
            $clinicPostBookingUrlUpdate = true;
            $v = $validated['clinic_post_booking_redirect_url'];
            $clinicPostBookingUrl = ($v === null || $v === '') ? null : trim((string) $v);
            unset($validated['clinic_post_booking_redirect_url']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->role === 'doctor' && $user->doctor) {
            $doctorData = [];

            if (array_key_exists('specialization', $validated)) {
                $doctorData['specialization'] = $validated['specialization'] ?: 'GP';
            }

            if ($postBookingUrlUpdate) {
                $doctorData['post_booking_redirect_url'] = $postBookingUrl;
            }

            if ($clinicPostBookingUrlUpdate) {
                $doctorData['clinic_post_booking_redirect_url'] = $clinicPostBookingUrl;
            }

            if ($doctorData !== []) {
                $user->doctor->update($doctorData);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
