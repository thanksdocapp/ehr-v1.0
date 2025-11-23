<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show 2FA setup page.
     */
    public function showSetup(Request $request)
    {
        // Check if user is authenticated or coming from forced setup
        $user = Auth::guard('admin')->user();
        
        // If not authenticated, check if this is a forced setup scenario
        if (!$user && $request->session()->has('2fa_setup_required')) {
            $userId = $request->session()->get('2fa_user_id');
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return redirect()->route('admin.login')
                    ->with('error', 'Session expired. Please login again.');
            }
            
            // Temporarily authenticate the user for setup
            Auth::guard('admin')->login($user);
        }
        
        if (!$user) {
            return redirect()->route('admin.login');
        }
        
        $status = $this->twoFactorService->getStatus($user);
        $isRequired = $this->twoFactorService->isRequired($user);
        $isForced = $request->session()->get('2fa_setup_required', false);

        return view('admin.auth.two-factor-setup', compact('status', 'isRequired', 'isForced'));
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:email,authenticator',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::guard('admin')->user();
        $method = $request->input('method', 'email');

        try {
            $twoFactorAuth = $this->twoFactorService->enable($user, $method);

            // Send initial code for email method
            $successMessage = '2FA has been enabled. Enter the verification code sent to your email to complete setup.';
            if ($method === 'email') {
                $sent = $this->twoFactorService->sendCode($user);
                if ($sent) {
                    $request->session()->flash('code_sent', true);
                } else {
                    $request->session()->flash('error', 'We could not send the verification code email. Please check mail settings or click Resend code.');
                    $successMessage = '2FA has been enabled. Click Resend to receive your verification code.';
                }
            }

            // Begin verification flow immediately after enabling
            // Store user ID in session for 2FA verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', false);

            // Clear any forced-setup marker; we'll drive through verification next
            $request->session()->forget('2fa_setup_required');

            // Logout current session to require code verification
            \Illuminate\Support\Facades\Auth::guard('admin')->logout();

            return redirect()->route('admin.two-factor.verify')
                ->with('success', $successMessage)
                ->with('recovery_codes', $twoFactorAuth->recovery_codes);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to enable 2FA: ' . $e->getMessage());
        }
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::guard('admin')->user();
        
        // Check if 2FA is required by admin settings
        if ($this->twoFactorService->isRequired($user)) {
            return back()->with('error', 'Two-factor authentication is required by your administrator and cannot be disabled.');
        }

        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password. Please try again.');
        }

        try {
            $this->twoFactorService->disable($user);
            return back()->with('success', '2FA has been disabled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to disable 2FA: ' . $e->getMessage());
        }
    }

    /**
     * Show 2FA verification page during login.
     */
    public function showVerify(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor-verify');
    }

    /**
     * Verify 2FA code during login.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $userId = $request->session()->get('2fa_user_id');
        $remember = $request->session()->get('2fa_remember', false);

        if (!$userId) {
            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'User not found.');
        }

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            // Clear 2FA session data
            $request->session()->forget('2fa_user_id');
            $request->session()->forget('2fa_remember');

            // Log the user in
            Auth::guard('admin')->login($user, $remember);
            $request->session()->regenerate();

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log successful login
            logger()->info('Admin login successful with 2FA', [
                'admin_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->with('error', 'Invalid verification code. Please try again.');
    }

    /**
     * Verify recovery code during login.
     */
    public function verifyRecovery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recovery_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $userId = $request->session()->get('2fa_user_id');
        $remember = $request->session()->get('2fa_remember', false);

        if (!$userId) {
            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'User not found.');
        }

        if ($this->twoFactorService->verifyRecoveryCode($user, $request->recovery_code)) {
            // Clear 2FA session data
            $request->session()->forget('2fa_user_id');
            $request->session()->forget('2fa_remember');

            // Log the user in
            Auth::guard('admin')->login($user, $remember);
            $request->session()->regenerate();

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log successful login with recovery code
            logger()->info('Admin login successful with 2FA recovery code', [
                'admin_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('warning', 'You used a recovery code. Please regenerate your recovery codes.');
        }

        return back()->with('error', 'Invalid recovery code. Please try again.');
    }

    /**
     * Resend 2FA code.
     */
    public function resendCode(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return back()->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return back()->with('error', 'User not found.');
        }

        try {
            $sent = $this->twoFactorService->sendCode($user);
            if ($sent) {
                return back()
                    ->with('success', 'A new verification code has been sent to your email.')
                    ->with('code_sent', true);
            }
            return back()->with('error', 'Failed to send verification code. Please verify email settings in Admin > Settings > Email/SMTP or with your SMTP provider.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send code: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::guard('admin')->user();

        // Verify password
        if (!\Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password. Please try again.');
        }

        try {
            $codes = $this->twoFactorService->regenerateRecoveryCodes($user);
            return back()->with('success', 'Recovery codes have been regenerated.')
                ->with('recovery_codes', $codes);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to regenerate codes: ' . $e->getMessage());
        }
    }
}
