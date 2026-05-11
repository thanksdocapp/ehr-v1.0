<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
     * Email alone is not sufficient when multiple patient accounts share an email.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'patient_reference' => ['required', 'string', 'max:255'],
        ]);

        $patient = Patient::where('email', $request->email)
            ->where(function ($q) use ($request) {
                $ref = trim($request->patient_reference);
                $q->where('patient_id', $ref);
                if (ctype_digit((string) $ref)) {
                    $q->orWhere('id', (int) $ref);
                }
            })
            ->first();

        if (! $patient) {
            return back()->withInput($request->only('email', 'patient_reference'))
                ->withErrors(['email' => __('No account matches that email and Patient ID.')]);
        }

        $plain = Str::random(64);

        DB::table('patient_password_reset_tokens')->updateOrInsert(
            ['patient_id' => $patient->id],
            ['token' => Hash::make($plain), 'created_at' => now()]
        );

        $patient->sendPasswordResetNotification($plain);

        return back()->with('status', __('We have emailed your password reset link!'));
    }
}
