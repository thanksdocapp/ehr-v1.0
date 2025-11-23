<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the admin profile page
     */
    public function index()
    {
        return view('admin.profile.index');
    }

    /**
     * Update the admin profile
     */
    public function update(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'phone']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
                Storage::disk('public')->delete($admin->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars/admin', 'public');
            $data['avatar'] = $avatarPath;
        }

        $admin->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Change admin password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $admin = auth('admin')->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully!');
    }
}
