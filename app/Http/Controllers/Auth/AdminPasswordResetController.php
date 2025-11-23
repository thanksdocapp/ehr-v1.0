<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AdminPasswordResetController extends Controller
{
    /**
     * Display the password reset form.
     */
    public function showResetForm($token)
    {
        // Verify token exists and is valid
        $resetToken = PasswordResetToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->where('used_at', null)
            ->first();
        
        if (!$resetToken) {
            return redirect()->route('login')
                ->with('error', 'This password reset link is invalid or has expired.');
        }
        
        // Get user
        $user = User::find($resetToken->user_id);
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'User not found.');
        }
        
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $user->email,
            'user' => $user
        ]);
    }
    
    /**
     * Handle the password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        // Verify token
        $resetToken = PasswordResetToken::where('token', $request->token)
            ->where('expires_at', '>', now())
            ->where('used_at', null)
            ->first();
        
        if (!$resetToken) {
            return back()->withErrors([
                'token' => 'This password reset link is invalid or has expired.'
            ]);
        }
        
        // Get user and verify email matches
        $user = User::find($resetToken->user_id);
        
        if (!$user || $user->email !== $request->email) {
            return back()->withErrors([
                'email' => 'The email address does not match our records.'
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // Update user password
            $user->update([
                'password' => Hash::make($request->password),
                'password_change_required' => false, // Clear the force change flag
            ]);
            
            // Mark token as used
            $resetToken->update([
                'used_at' => now(),
                'used_by_ip' => $request->ip(),
            ]);
            
            // Invalidate other tokens for this user
            PasswordResetToken::where('user_id', $user->id)
                ->where('id', '!=', $resetToken->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);
            
            DB::commit();
            
            return redirect()->route('login')
                ->with('success', 'Your password has been reset successfully. Please login with your new password.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'password' => 'Failed to reset password. Please try again.'
            ]);
        }
    }
}
