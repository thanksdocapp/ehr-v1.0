<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\TwoFactorAuthService;

class AuthController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    /**
     * Handle admin login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Determine if login is email or username (treat as email for now)
        $credentials = [
            'email' => $request->login,
            'password' => $request->password,
        ];
        $remember = $request->filled('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $user = Auth::guard('admin')->user();
            
            // Check if user has admin access (multiple ways)
            $hasAdminAccess = $user->is_admin || // Simple admin flag
                             $user->role === 'admin' || // Role field
                             ($user->roles && $user->roles->whereIn('name', ['super_admin', 'admin'])->count() > 0) || // Role relationships
                             ($user->hasPermission && $user->hasPermission('admin.access')); // Permission system
            
            if ($hasAdminAccess) {
                // Check if 2FA is required (either enabled by user OR forced by admin settings)
                $userHas2FA = $this->twoFactorService->requiresTwoFactor($user);
                $adminRequires2FA = $this->twoFactorService->isRequired($user);
                
                if ($userHas2FA || $adminRequires2FA) {
                    // If admin requires 2FA but user hasn't enabled it, redirect to setup
                    if ($adminRequires2FA && !$userHas2FA) {
                        // Store user ID in session
                        $request->session()->put('2fa_setup_required', true);
                        $request->session()->put('2fa_user_id', $user->id);
                        
                        // Ensure the user is logged out until 2FA is set up
                        Auth::guard('admin')->logout();
                        
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => true,
                                'requires_2fa_setup' => true,
                                'message' => 'Two-factor authentication is required. Please set it up.',
                                'redirect' => route('admin.two-factor.setup')
                            ]);
                        }
                        
                        return redirect()->route('admin.two-factor.setup')
                            ->with('warning', 'Two-factor authentication is required by your administrator. Please set it up to continue.');
                    }
                    
                    // User has 2FA enabled, proceed with verification
                    // Store user ID in session for 2FA verification
                    $request->session()->put('2fa_user_id', $user->id);
                    $request->session()->put('2fa_remember', $remember);
                    
                    // Logout the user temporarily
                    Auth::guard('admin')->logout();
                    
                    // Send 2FA code
                    $this->twoFactorService->sendCode($user);
                    
                    // Log 2FA code sent
                    logger()->info('2FA code sent for admin login', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip(),
                    ]);
                    
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => true, 
                            'requires_2fa' => true,
                            'message' => 'A verification code has been sent to your email.',
                            'redirect' => route('admin.two-factor.verify')
                        ]);
                    }
                    
                    return redirect()->route('admin.two-factor.verify')
                        ->with('success', 'A verification code has been sent to your email.');
                }
                
                $request->session()->regenerate();
                
                // Update last login
                $user->update(['last_login_at' => now()]);
                
                // Log admin login
                logger()->info('Admin login successful', [
                    'admin_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // Clear any unintended redirect URL from session
                $request->session()->forget('url.intended');
                
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => 'Welcome back, ' . $user->name . '!', 'redirect' => route('admin.dashboard')]);
                }
                
                // Always redirect to admin dashboard, don't use intended() which might have wrong URL
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            } else {
                Auth::guard('admin')->logout();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
                }
                return redirect()->back()
                    ->with('error', 'Access denied. Admin privileges required.')
                    ->withInput();
            }
        }

        // Log failed login attempt
        logger()->warning('Admin login failed', [
            'login' => $request->login,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials. Please try again.']);
        }

        return redirect()->back()
            ->with('error', 'Invalid credentials. Please try again.')
            ->withInput();
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();
        
        // Log admin logout
        if ($user) {
            logger()->info('Admin logout', [
                'admin_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show admin registration form (for initial setup).
     */
    public function showRegister()
    {
        // Only allow registration if no admin exists
        if (User::where('is_admin', true)->exists()) {
            return redirect()->route('admin.login')
                ->with('error', 'Admin registration is not available.');
        }

        return view('admin.auth.register');
    }

    /**
     * Handle admin registration (for initial setup).
     */
    public function register(Request $request)
    {
        // Only allow registration if no admin exists
        if (User::where('is_admin', true)->exists()) {
            return redirect()->route('admin.login')
                ->with('error', 'Admin registration is not available.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => true,
            'email_verified_at' => now(), // Auto-verify admin
        ]);

        // Log admin registration
        logger()->info('Admin registration successful', [
            'admin_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        Auth::guard('admin')->login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Admin account created successfully! Welcome to the Hospital Management System.');
    }

    /**
     * Show change password form.
     */
    public function showChangePassword()
    {
        return view('admin.auth.change-password');
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

        $user = Auth::guard('admin')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Log password change
        logger()->info('Admin password changed', [
            'admin_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Password updated successfully.');
    }
}
