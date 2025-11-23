<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view for patients.
     */
    public function create(): View
    {
        return view('patient.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request for patients.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Use the patient password broker to send reset link
        $status = Password::broker('patients')->sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __('We have emailed your password reset link!'))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __('We can\'t find a patient with that email address.')]);
    }
}
