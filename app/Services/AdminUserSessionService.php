<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Models\AdminUserSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminUserSessionService
{
    public function createUserSession(Admin $admin)
    {
        try {
            // Create or get user account for admin
            $user = $this->getOrCreateUserAccount($admin);
            
            // Log the admin-to-user switch
            $this->logSessionSwitch($admin, $user);
            
            // Set session variables
            Session::put('admin_as_user', true);
            Session::put('admin_id', $admin->id);
            
            // Login as user
            Auth::guard('web')->login($user);

            Log::info('Admin user session created successfully', [
                'admin_id' => $admin->id,
                'user_id' => $user->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create admin user session: ' . $e->getMessage(), [
                'admin_id' => $admin->id,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function endUserSession()
    {
        try {
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

                Log::info('Admin user session ended successfully', [
                    'admin_id' => Session::get('admin_id')
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to end admin user session: ' . $e->getMessage(), [
                'admin_id' => Session::get('admin_id'),
                'exception' => $e
            ]);
            throw $e;
        }
    }

    protected function getOrCreateUserAccount(Admin $admin)
    {
        try {
            // Try to find existing user account
            $user = User::where('email', $admin->email)->first();

            if (!$user) {
                // Create new user account
                $user = User::create([
                    'firstname' =>$admin->first_name,
                    'surname' =>$admin->surname,
                    'last_name' => $admin->last_name,
                    'email' => $admin->email,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                ]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to get or create user account: ' . $e->getMessage(), [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    protected function logSessionSwitch(Admin $admin, User $user)
    {
        try {
            AdminUserSession::create([
                'admin_id' => $admin->id,
                'user_id' => $user->id,
                'started_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log session switch: ' . $e->getMessage(), [
                'admin_id' => $admin->id,
                'user_id' => $user->id,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
