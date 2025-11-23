<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class StaffAuthController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }
    /**
     * Show the staff login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->to($this->getRedirectUrl());
        }
        
        return view('auth.staff-login');
    }

    /**
     * Handle staff login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user has any staff role (either in role field or role relationships)
            if (empty($user->role) && $user->roles->isEmpty()) {
                Auth::logout();
                return redirect()->back()
                    ->with('error', 'No role assigned. Please contact administrator.')
                    ->withInput();
            }

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->back()
                    ->with('error', 'Your account is inactive. Please contact administrator.')
                    ->withInput();
            }

            // Determine 2FA state
            $adminRequires2FA = $this->twoFactorService->isRequired($user);
            $twoFARecord = \App\Models\TwoFactorAuth::where('user_id', $user->id)->first(); // may be null
            $hasEnabledRecord = $twoFARecord && $twoFARecord->enabled; // treat enabled as requires verification even if not confirmed yet
            $userHasActive2FA = $this->twoFactorService->requiresTwoFactor($user); // enabled AND confirmed

            if ($adminRequires2FA || $hasEnabledRecord || $userHasActive2FA) {
                // If admin requires 2FA and user does not even have an enabled record, send to setup
                if ($adminRequires2FA && !$hasEnabledRecord) {
                    // Store user ID in session
                    $request->session()->put('2fa_setup_required', true);
                    $request->session()->put('2fa_user_id', $user->id);

                    // Ensure the user is logged out until 2FA is set up
                    Auth::logout();

                    return redirect()->route('staff.two-factor.setup')
                        ->with('warning', 'Two-factor authentication is required. Please set it up to continue.');
                }

                // If user has an enabled 2FA record (confirmed or not), proceed with verification
                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_remember', $remember);

                // Logout the user temporarily
                Auth::logout();

                // Send 2FA code
                $this->twoFactorService->sendCode($user);

                // Log 2FA code sent
                logger()->info('2FA code sent for staff login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                ]);

                return redirect()->route('staff.two-factor.verify')
                    ->with('success', 'A verification code has been sent to your email.');
            }

            $request->session()->regenerate();
            
            // Log successful login
            \App\Models\UserActivity::log([
                'user_id' => $user->id,
                'action' => 'login',
                'description' => 'User logged in successfully',
                'severity' => 'low',
            ]);
            
            return redirect()->intended($this->getRedirectUrl())
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Log failed login attempt
        \App\Models\UserActivity::log([
            'action' => 'failed_login',
            'description' => 'Failed login attempt for email: ' . $request->email,
            'severity' => 'medium',
        ]);

        return redirect()->back()
            ->with('error', 'Invalid credentials. Please try again.')
            ->withInput();
    }

    /**
     * Handle staff logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout
        if ($user) {
            \App\Models\UserActivity::log([
                'user_id' => $user->id,
                'action' => 'logout',
                'description' => 'User logged out',
                'severity' => 'low',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show change password form.
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log password change
        \App\Models\UserActivity::log([
            'user_id' => $user->id,
            'action' => 'password_change',
            'description' => 'User changed password',
            'severity' => 'medium',
        ]);

        return redirect()->to($this->getRedirectUrl())
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Get the redirect URL based on user role
     */
    private function getRedirectUrl()
    {
        $user = Auth::user();
        
        if (!$user) {
            return route('login');
        }

        // Check if user is admin
        if ($user->is_admin || $user->role === 'admin') {
            return route('admin.dashboard');
        }

        // Check if user is staff (any non-admin role)
        if (in_array($user->role, ['staff', 'nurse', 'receptionist', 'doctor', 'pharmacist', 'technician'])) {
            return route('staff.dashboard');
        }

        // Default fallback (shouldn't happen with proper role assignment)
        return route('staff.dashboard');
    }
}
