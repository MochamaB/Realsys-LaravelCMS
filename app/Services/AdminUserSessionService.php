<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Models\AdminUserSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSessionService
{
    public function createUserSession(Admin $admin)
    {
        // Create or get user account for admin
        $user = $this->getOrCreateUserAccount($admin);
        
        // Log the admin-to-user switch
        $this->logSessionSwitch($admin, $user);
        
        // Set session variables
        Session::put('admin_as_user', true);
        Session::put('admin_id', $admin->id);
        
        // Login as user
        Auth::guard('web')->login($user);
    }

    public function endUserSession()
    {
        if (Session::has('admin_as_user') && Session::has('admin_id')) {
            $session = AdminUserSession::where('admin_id', Session::get('admin_id'))
                ->whereNull('ended_at')
                ->latest()
                ->first();

            if ($session) {
                $session->update(['ended_at' => now()]);
            }

            Session::forget(['admin_as_user', 'admin_id']);
            Auth::guard('web')->logout();
        }
    }

    protected function getOrCreateUserAccount(Admin $admin)
    {
        // Try to find existing user account
        $user = User::where('email', $admin->email)->first();

        if (!$user) {
            // Create new user account
            $user = User::create([
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'surname' => $admin->surname,
                'email' => $admin->email,
                'password' => Hash::make(Str::random(32)), // Random password since it won't be used
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }

        return $user;
    }

    protected function logSessionSwitch(Admin $admin, User $user)
    {
        AdminUserSession::create([
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
