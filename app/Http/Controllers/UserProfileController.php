<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Modules\UserManagement\Entities\Profile;
use Modules\UserManagement\Entities\Membership;
use Modules\UserManagement\Entities\ProfileType;
use Spatie\Permission\Models\Role;

class UserProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Show the user profile.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        try {
            $user = Auth::user()->load([
                'profile',
                'profile.profileType',
                'membership',
                'roles'
            ]);
            
            // Get all roles for the role selection dropdown
            $roles = Role::all()->filter(function ($role) {
                return !preg_match('/super-admin/i', $role->name);
            });
            
            // Get all profile types for the profile type selection
            $profileTypes = ProfileType::all();

            // Ensure profile exists
            if (!$user->profile) {
                $user->profile = new Profile();
            }

            // Ensure membership exists
            if (!$user->membership) {
                $user->membership = new Membership();
            }
          //  dd($user);
            return view('user.profile.show', compact('user', 'roles', 'profileTypes'));
        } catch (\Exception $e) {
            return redirect()
                ->route('user.dashboard.dashboard')
                ->with('error', 'Failed to load user details. Please try again.');
        }
       
    }
    
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);
        
        $user->update($validated);
        
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);
            
            // Delete old avatar if exists
            $user->clearMediaCollection('avatar');
            
            // Upload new avatar
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatar');
        }
        
        return redirect()->route('profile.show')
            ->with('status', 'Profile updated successfully.');
    }
    
    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
        
        return redirect()->route('profile.show')
            ->with('status', 'Password updated successfully.');
    }
    /**
     * Show the force change password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForceChangePassword()
    {
        return view('auth.passwords.force-change');
    }
    
    /**
     * Update the user's password after force change.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateForceChangePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user = Auth::guard('web')->user();

       
        // Update password
        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->save();

        // If this was a login attempt, complete the login
        if ($request->session()->has('login_attempt')) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')
                ->with('success', 'Password changed successfully. Welcome to your dashboard!');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Update the user's profile picture.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $user = Auth::user();
            
            // Delete old profile photo if exists
            if ($user->hasMedia('profile_photos')) {
                $user->clearMediaCollection('profile_photos');
            }

            // Add new profile photo
            $user->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile_photos');

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture. Please try again.'
            ], 500);
        }
    }
}