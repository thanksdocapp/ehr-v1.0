<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the patient login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        // Check if patient login is disabled
        $patientLoginEnabled = Setting::get('patient_login_enabled', true);
        
        if (!$patientLoginEnabled) {
            return redirect()->route('login')
                ->with('info', 'Patient login is currently disabled. Please use the staff login portal.');
        }
        
        return view('patient.auth.login');
    }

    /**
     * Handle patient login.
     */
    public function login(Request $request): RedirectResponse
    {
        // Check if patient login is disabled
        $patientLoginEnabled = Setting::get('patient_login_enabled', true);
        
        if (!$patientLoginEnabled) {
            return redirect()->route('login')
                ->with('info', 'Patient login is currently disabled. Please use the staff login portal.');
        }
        
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'patient_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $ref = $request->input('patient_reference');

        $baseQuery = Patient::where('email', $email)->whereNotNull('password');

        if ($ref !== null && trim((string) $ref) !== '') {
            $ref = trim((string) $ref);
            $patient = (clone $baseQuery)->where(function ($q) use ($ref) {
                $q->where('patient_id', $ref);
                if (ctype_digit((string) $ref)) {
                    $q->orWhere('id', (int) $ref);
                }
            })->first();

            if ($patient && Hash::check($password, $patient->password)) {
                Auth::guard('patient')->login($patient, $request->boolean('remember'));
                $request->session()->regenerate();

                return redirect()->intended(route('patient.dashboard'));
            }

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email', 'patient_reference');
        }

        $matches = $baseQuery->get()->filter(fn (Patient $p) => Hash::check($password, $p->password));

        if ($matches->count() > 1) {
            return back()->withErrors([
                'patient_reference' => 'Several accounts use this email. Enter your Patient ID (reference) shown on letters or booking confirmations.',
            ])->onlyInput('email');
        }

        $patient = $matches->first();
        if ($patient) {
            Auth::guard('patient')->login($patient, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('patient.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the patient registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        // Check if patient login is disabled
        $patientLoginEnabled = Setting::get('patient_login_enabled', true);
        
        if (!$patientLoginEnabled) {
            return redirect()->route('login')
                ->with('info', 'Patient registration and login are currently disabled. Please use the staff login portal.');
        }
        
        return view('patient.auth.register');
    }

    /**
     * Handle patient registration.
     */
    public function register(Request $request): RedirectResponse
    {
        // Check if patient login is disabled
        $patientLoginEnabled = Setting::get('patient_login_enabled', true);
        
        if (!$patientLoginEnabled) {
            return redirect()->route('login')
                ->with('info', 'Patient registration and login are currently disabled. Please use the staff login portal.');
        }
        
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $patient = Patient::create([
            'patient_id' => Patient::generatePatientId(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        event(new Registered($patient));

        Auth::guard('patient')->login($patient);

        return redirect()->route('patient.dashboard')
            ->with('success', 'Registration successful! Welcome to your patient portal.');
    }

    /**
     * Handle patient logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('patient')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('patient.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
