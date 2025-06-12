<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\UserManagement\Entities\Profile;
use Modules\UserManagement\Entities\Membership;
use Modules\UserManagement\Entities\ProfileType;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users with their profiles and memberships.
     */
    public function index()
    {
        $users = User::with(['profile', 'profile.profileType', 'membership', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $profileTypes = ProfileType::all();
        $roles = Role::all();
        
        return view('admin.users.create', compact('profileTypes', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Implementation will be added later
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['profile', 'profile.profileType', 'membership', 'roles']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['profile', 'profile.profileType', 'membership', 'roles']);
        $profileTypes = ProfileType::all();
        $roles = Role::all();
        
        return view('admin.users.edit', compact('user', 'profileTypes', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Implementation will be added later
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Implementation will be added later
    }
} 