<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $patient = Patient::findOrFail($request->route('patient'));

        return view('patient.auth.reset-password', [
            'request' => $request,
            'patientEmail' => $patient->email,
        ]);
    }

    /**
     * Handle an incoming new password request for patients.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $row = DB::table('patient_password_reset_tokens')
            ->where('patient_id', $request->integer('patient_id'))
            ->first();

        if (! $row || ! Hash::check($request->input('token'), $row->token)) {
            return back()->withErrors(['email' => __('This password reset link is invalid or has already been used.')]);
        }

        $expireMinutes = (int) config('auth.passwords.patients.expire', 60);
        if ($row->created_at && Carbon::parse($row->created_at)->addMinutes($expireMinutes)->isPast()) {
            return back()->withErrors(['email' => __('This password reset link has expired. Please request a new one.')]);
        }

        $patient = Patient::findOrFail($request->integer('patient_id'));
        $patient->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('patient_password_reset_tokens')->where('patient_id', $patient->id)->delete();

        event(new PasswordReset($patient));

        return redirect()->route('patient.login')
            ->with('status', __('Your password has been reset successfully!'));
    }
}
