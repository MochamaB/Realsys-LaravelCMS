<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Modules\UserManagement\Entities\Profile;
use Modules\UserManagement\Entities\Membership;
use Modules\UserManagement\Entities\ProfileType;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users with their profiles and memberships.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $perPage = 10;

        // Get statistics for all users
        $allStats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'byRole' => Role::withCount('users')->get()->pluck('users_count', 'name'),
        ];

        // Get statistics for admins
        $adminStats = [
            'total' => Admin::count(),
            'active' => Admin::where('status', 'active')->count(),
            'inactive' => Admin::where('status', 'inactive')->count(),
            'byRole' => Role::whereHas('users', function($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'admin');
                });
            })->withCount(['users' => function($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'admin');
                });
            }])->get()->pluck('users_count', 'name'),
        ];

        // Get statistics for regular users
        $userStats = [
            'total' => User::whereDoesntHave('roles', function($query) {
                $query->where('name', 'admin');
            })->count(),
            'active' => User::whereDoesntHave('roles', function($query) {
                $query->where('name', 'admin');
            })->where('status', 'active')->count(),
            'inactive' => User::whereDoesntHave('roles', function($query) {
                $query->where('name', 'admin');
            })->where('status', 'inactive')->count(),
            'byRole' => Role::where('name', '!=', 'admin')
                ->withCount(['users' => function($query) {
                    $query->whereDoesntHave('roles', function($q) {
                        $q->where('name', 'admin');
                    });
                }])
                ->get()
                ->pluck('users_count', 'name'),
        ];

        // Get users based on category
        switch ($category) {
            case 'admins':
                $users = Admin::with(['roles'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
                $stats = $adminStats;
                break;
            case 'users':
                $users = User::whereDoesntHave('roles', function($query) {
                    $query->where('name', 'admin');
                })
                ->with(['profile', 'profile.profileType', 'membership', 'roles'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
                $stats = $userStats;
                break;
            default: // 'all'
                $users = User::with(['profile', 'profile.profileType', 'membership', 'roles'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
                $stats = $allStats;
                break;
        }

        return view('admin.users.index', compact('users', 'stats', 'category'));
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
    public function show($id)
    {
        try {
            $user = User::with([
                'profile',
                'profile.profileType',
                'membership',
                'roles'
            ])->findOrFail($id);

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

            return view('admin.users.show', compact('user', 'roles', 'profileTypes'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Failed to load user details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::with(['profile', 'profile.profileType', 'membership', 'roles'])
            ->findOrFail($id);
        
        $roles = Role::all();
        $profileTypes = ProfileType::all();

        return view('admin.users.edit', compact('user', 'roles', 'profileTypes'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'status' => 'required|in:active,inactive',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'profile_type_id' => 'required|exists:profile_types,id',
            'membership_status' => 'required|in:active,inactive',
            'membership_expiry' => 'required|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            // Update user basic info
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);

            // Update roles
            $user->syncRoles($validated['roles']);

            // Update profile
            $user->profile()->update([
                'profile_type_id' => $validated['profile_type_id'],
            ]);

            // Update membership
            $user->membership()->update([
                'status' => $validated['membership_status'],
                'expiry_date' => $validated['membership_expiry'],
            ]);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update user. Please try again.');
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete related records
            $user->profile()->delete();
            $user->membership()->delete();
            $user->roles()->detach();
            
            // Delete user
            $user->delete();

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    /**
     * Update user's password.
     */
    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        
        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update password. Please try again.');
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        try {
            $user->update([
                'status' => $user->status === 'active' ? 'inactive' : 'active'
            ]);

            return back()->with('success', 'User status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update user status. Please try again.');
        }
    }

    /**
     * Toggle membership status.
     */
    public function toggleMembership($id)
    {
        $user = User::findOrFail($id);
        
        try {
            $user->membership()->update([
                'status' => $user->membership->status === 'active' ? 'inactive' : 'active'
            ]);

            return back()->with('success', 'Membership status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update membership status. Please try again.');
        }
    }

    /**
     * Update user membership and payment details.
     */
    public function updateMembership(Request $request, $id)
    {
        $validated = $request->validate([
            'membership_status' => 'required|in:pending,active,inactive,expired',
            'membership_expiry' => 'required|date|after:today',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:credit_card,bank_transfer,paypal,cash',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            
            // Update membership
            $membership = $user->membership ?? new Membership();
            $membership->fill([
                'status' => $validated['membership_status'],
                'expiry_date' => $validated['membership_expiry'],
                'payment_status' => $validated['payment_status'],
                'payment_amount' => $validated['payment_amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'payment_notes' => $validated['payment_notes'],
            ]);

            // If this is a new membership, associate it with the user
            if (!$membership->exists) {
                $user->membership()->save($membership);
            } else {
                $membership->save();
            }

            // Create payment history record
            $membership->paymentHistory()->create([
                'amount' => $validated['payment_amount'],
                'method' => $validated['payment_method'],
                'status' => $validated['payment_status'],
                'notes' => $validated['payment_notes'],
                'payment_date' => $validated['payment_date'],
            ]);

            // Update user status based on membership status
            if ($validated['membership_status'] === 'active' && $validated['payment_status'] === 'paid') {
                $user->update(['status' => 'active']);
            } elseif ($validated['membership_status'] === 'pending') {
                $user->update(['status' => 'pending']);
            } else {
                $user->update(['status' => 'inactive']);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Membership and payment details updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update membership and payment details. Please try again.');
        }
    }

    /**
     * Update user's profile picture.
     */
    public function updateProfilePicture(Request $request, $id)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $user = User::findOrFail($id);
            
            // Delete old profile photo if exists
            if ($user->hasMedia('profile_photos')) {
                $user->clearMediaCollection('profile_photos');
            }

            // Add new profile photo
            $user->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile_photos');

            return redirect()
                ->back()
                ->with('success', 'Profile picture updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update profile picture. Please try again.');
        }
    }
} 