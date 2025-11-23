<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
            'is_admin' => true, // Only allow admin users
        ];

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard('admin')->user();
            
            // Double check user is admin and active
            if (!$user->is_admin) {
                Auth::guard('admin')->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }
            
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => route('admin.dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials or account is inactive.',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }

    public function showProfile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'employee_id' => 'nullable|string|unique:users,employee_id,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'username', 'phone']);
        
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('admin/avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $admin->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin = Auth::guard('admin')->user();
        
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}