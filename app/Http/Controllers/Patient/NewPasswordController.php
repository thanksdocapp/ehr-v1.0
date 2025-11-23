<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view for patients.
     */
    public function create(Request $request): View
    {
        return view('patient.auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request for patients.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use the patient password broker to reset password
        $status = Password::broker('patients')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($patient) use ($request) {
                $patient->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($patient));
            }
        );

        // If the password was successfully reset, redirect to patient login
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('patient.login')->with('status', __('Your password has been reset successfully!'))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
