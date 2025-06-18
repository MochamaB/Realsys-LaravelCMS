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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        return redirect()->route('admin.users.wizard');
    }

    /**
     * Entry point for the user creation wizard.
     */
    public function showWizard()
    {
        return redirect()->route('admin.users.wizard.step1');
    }

    /**
     * Show step 1 of the wizard - User Type Selection.
     */
    public function showStep1()
    {
        return view('admin.users.wizard.step1');
    }

    /**
     * Process step 1 submission - User Type Selection.
     */
    public function submitStep1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type' => ['required', 'string', 'in:admin,user'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step1')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $this->storeWizardData('user_type', $request->user_type);
        Session::put('wizard_step', 2);

        return redirect()->route('admin.users.wizard.step2')
            ->with('success', 'User type selected successfully. Please choose a role.');
    }

    /**
     * Show step 2 of the wizard - Role Selection.
     */
    public function showStep2()
    {
        // Check if step 1 was completed
        if (!Session::has('wizard_data.user_type')) {
            return redirect()->route('admin.users.wizard.step1')
                ->with('error', 'Please select a user type first.');
        }

        $userType = Session::get('wizard_data.user_type');
        
        // Get roles based on user type
        if ($userType === 'admin') {
                $roles = Role::where('guard_name', 'admin')->where('name', '!=', 'super-admin')->get();
            } else {
            $roles = Role::where('guard_name', 'web')->get();
        }
       // dd($roles);

        return view('admin.users.wizard.step2', compact('roles', 'userType'));
    }

    /**
     * Process step 2 submission - Role Selection.
     */
    public function submitStep2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step2')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $this->storeWizardData('role_id', $request->role_id);
        Session::put('wizard_step', 3);

        return redirect()->route('admin.users.wizard.step3')
            ->with('success', 'Role selected successfully. Please provide personal information.');
    }

    /**
     * Show step 3 of the wizard - Personal Information.
     */
    public function showStep3()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 2) {
            return redirect()->route('admin.users.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }

        $userType = Session::get('wizard_data.user_type');
        
        return view('admin.users.wizard.step3', compact('userType'));
    }

    /**
     * Process step 3 submission - Personal Information.
     */
    public function submitStep3(Request $request)
    {
        $userType = Session::get('wizard_data.user_type');
        
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:15'],
        ];

        // Add id_number validation for regular users
        if ($userType === 'user') {
            $rules['id_number'] = ['required', 'string', 'max:20', 'unique:profiles,id_passport_number'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step3')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $fields = ['first_name', 'surname', 'last_name', 'email', 'phone_number'];
        if ($userType === 'user') {
            $fields[] = 'id_number';
        }

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->storeWizardData($field, $request->input($field));
            }
        }

        // Set default password to 'NPPK.123' (hashed)
        $password = 'NPPK.123';
        $this->storeWizardData('password_hash', Hash::make($password));

        Session::put('wizard_step', 4);

        return redirect()->route('admin.users.wizard.step4')
            ->with('success', 'Personal information saved successfully.');
    }

    /**
     * Show step 4 of the wizard - Party Membership Decision.
     */
    public function showStep4()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 3) {
            return redirect()->route('admin.users.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }

        $userType = Session::get('wizard_data.user_type');
        
        return view('admin.users.wizard.step4', compact('userType'));
    }

    /**
     * Process step 4 submission - Party Membership Decision.
     */
    public function submitStep4(Request $request)
    {
        $userType = Session::get('wizard_data.user_type');
        
        $rules = ['register_as_party_member' => ['required', 'boolean']];
        
        // For regular users, always register as party member
        if ($userType === 'user') {
            $this->storeWizardData('register_as_party_member', true);
            Session::put('wizard_step', 5);
            return redirect()->route('admin.users.wizard.step5')
                ->with('success', 'Please provide additional information for party membership.');
        }

        // For admin users, check their decision
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step4')
                ->withErrors($validator)
                ->withInput();
        }

        $this->storeWizardData('register_as_party_member', $request->register_as_party_member);

        if ($request->register_as_party_member) {
            Session::put('wizard_step', 5);
            return redirect()->route('admin.users.wizard.step5')
                ->with('success', 'Please provide additional information for party membership.');
        } else {
            // Complete registration without party membership
            return $this->completeRegistration();
        }
    }

    /**
     * Show step 5 of the wizard - Additional Information (Conditional).
     */
    public function showStep5()
    {
        // Check if party membership is selected
        if (!Session::has('wizard_data.register_as_party_member') || !Session::get('wizard_data.register_as_party_member')) {
            return redirect()->route('admin.users.wizard.step1')
                ->with('error', 'Invalid step access.');
        }

        // Get data for dropdowns
        $ethnicities = \Modules\UserManagement\Entities\Ethnicity::orderBy('name')->get();
        $specialStatuses = \Modules\UserManagement\Entities\SpecialStatus::orderBy('name')->get();
        $religions = \Modules\UserManagement\Entities\Religion::orderBy('name')->get();
        $mobileProviders = \Modules\UserManagement\Entities\MobileProvider::all();

        return view('admin.users.wizard.step5', compact('ethnicities', 'specialStatuses', 'religions', 'mobileProviders'));
    }

    /**
     * Process step 5 submission - Additional Information.
     */
    public function submitStep5(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender' => ['required', 'string', 'in:male,female,other'],
            'ethnicity_id' => ['nullable', 'exists:ethnicities,id'],
            'special_status_id' => ['nullable', 'exists:special_statuses,id'],
            'ncpwd_number' => ['nullable', 'string', 'max:50', 'required_if:special_status_id,2'],
            'religion_id' => ['nullable', 'exists:religions,id'],
            'mobile_provider_id' => ['nullable', 'exists:mobile_providers,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step5')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $fields = ['gender', 'ethnicity_id', 'special_status_id', 'ncpwd_number', 'religion_id', 'mobile_provider_id'];
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->storeWizardData($field, $request->input($field));
            }
        }

        Session::put('wizard_step', 6);

        return redirect()->route('admin.users.wizard.step6')
            ->with('success', 'Additional information saved successfully. Please provide geographic details.');
    }

    /**
     * Show step 6 of the wizard - Geographic Information (Conditional).
     */
    public function showStep6()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 5) {
            return redirect()->route('admin.users.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }

        // Get counties for dropdown
        $counties = \Modules\UserManagement\Entities\County::orderBy('name')->get();
        
        // Get constituencies if county is selected
        $constituencies = [];
        if (Session::has('wizard_data.county_id')) {
            $constituencies = \Modules\UserManagement\Entities\Constituency::where('county_id', Session::get('wizard_data.county_id'))
                ->orderBy('name')
                ->get();
        }
        
        // Get wards if constituency is selected
        $wards = [];
        if (Session::has('wizard_data.constituency_id')) {
            $wards = \Modules\UserManagement\Entities\Ward::where('constituency_id', Session::get('wizard_data.constituency_id'))
                ->orderBy('name')
                ->get();
        }

        return view('admin.users.wizard.step6', compact('counties', 'constituencies', 'wards'));
    }

    /**
     * Process step 6 submission - Geographic Information.
     */
    public function submitStep6(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'county_id' => ['required', 'exists:counties,id'],
            'constituency_id' => ['required', 'exists:constituencies,id'],
            'ward_id' => ['required', 'exists:wards,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step6')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $this->storeWizardData('county_id', $request->county_id);
        $this->storeWizardData('constituency_id', $request->constituency_id);
        $this->storeWizardData('ward_id', $request->ward_id);

        Session::put('wizard_step', 7);

        return redirect()->route('admin.users.wizard.step7')
            ->with('success', 'Geographic information saved successfully. Please review and complete registration.');
    }

    /**
     * Show step 7 of the wizard - Terms & Photo (Conditional).
     */
    public function showStep7()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 6) {
            return redirect()->route('admin.users.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }

        return view('admin.users.wizard.step7');
    }

    /**
     * Process step 7 submission - Terms & Photo.
     */
    public function submitStep7(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'agree_terms' => ['required', 'boolean', 'accepted'],
            'agree_privacy' => ['required', 'boolean', 'accepted'],
            'agree_marketing' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.wizard.step7')
                ->withErrors($validator)
                ->withInput();
        }

        // Store agreement data
        $this->storeWizardData('agree_terms', $request->agree_terms);
        $this->storeWizardData('agree_privacy', $request->agree_privacy);
        $this->storeWizardData('agree_marketing', $request->agree_marketing);

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            try {
                $photo = $request->file('photo');
                $this->storeWizardData('photo_original_name', $photo->getClientOriginalName());
                $this->storeWizardData('photo_mime_type', $photo->getMimeType());
                $this->storeWizardData('photo_size', $photo->getSize());
                
                // Store the file temporarily and get the path
                $tempPath = $photo->store('temp/wizard-photos', 'local');
                $this->storeWizardData('photo_temp_path', $tempPath);
            } catch (\Exception $e) {
                \Log::error('Photo upload failed: ' . $e->getMessage());
                return redirect()->route('admin.users.wizard.step7')
                    ->with('error', 'Failed to upload photo. Please try again.')
                    ->withInput();
            }
        }

        // Complete registration
        return $this->completeRegistration();
    }

    /**
     * Get constituencies for a county (AJAX).
     */
    public function getConstituencies(Request $request)
    {
        $constituencies = \Modules\UserManagement\Entities\Constituency::where('county_id', $request->county_id)
            ->orderBy('name')
            ->get();
            
        return response()->json($constituencies);
    }

    /**
     * Get wards for a constituency (AJAX).
     */
    public function getWards(Request $request)
    {
        $wards = \Modules\UserManagement\Entities\Ward::where('constituency_id', $request->constituency_id)
            ->orderBy('name')
            ->get();
            
        return response()->json($wards);
    }

    /**
     * Complete the registration process.
     */
    private function completeRegistration()
    {
        try {
            // Get wizard data from session
            $wizardData = Session::get('wizard_data', []);
            
            // Debug: Log the wizard data
            \Log::info('Wizard data:', $wizardData);
            
            // Check if all required data is present
            $requiredKeys = ['user_type', 'role_id', 'first_name', 'surname', 'email', 'phone_number', 'password_hash'];
            
            foreach ($requiredKeys as $key) {
                if (!isset($wizardData[$key])) {
                    \Log::error('Missing required key: ' . $key);
                    return redirect()->route('admin.users.wizard.step1')
                        ->with('error', 'Missing required information. Please complete all steps.');
                }
            }

            DB::beginTransaction();

            // Create user based on type
            if ($wizardData['user_type'] === 'admin') {
                \Log::info('Creating admin user');
                $admin = Admin::create([
                    'first_name' => $wizardData['first_name'],
                    'last_name' => $wizardData['last_name'] ?? null,
                    'surname' => $wizardData['surname'],
                    'email' => $wizardData['email'],
                    'phone_number' => $wizardData['phone_number'],
                    'password' => $wizardData['password_hash'],
                    'status' => 'pending',
                ]);

                // Assign admin role
                $role = Role::findOrFail($wizardData['role_id']);
                $admin->assignRole($role);
                \Log::info('Admin role assigned: ' . $role->name);

                // If admin is also registered as party member, create User record too
                if (isset($wizardData['register_as_party_member']) && $wizardData['register_as_party_member']) {
                    \Log::info('Creating user record for admin party member');
                    $user = User::create([
                        'first_name' => $wizardData['first_name'],
                        'surname' => $wizardData['surname'],
                        'last_name' => $wizardData['last_name'] ?? null,
                        'email' => $wizardData['email'],
                        'phone_number' => $wizardData['phone_number'],
                        'password' => $wizardData['password_hash'],
                        'status' => 'pending',
                    ]);

                    \Log::info('User created with ID: ' . $user->id);

                    // Create party member profile for the user record
                    \Log::info('Creating party member profile for admin user');
                    $this->createPartyMemberProfile($user, $wizardData);

                    // Store the user ID for reference
                    $createdUser = $user;
                } else {
                    $createdUser = $admin;
                }
            } else {
                \Log::info('Creating regular user');
                $user = User::create([
                    'first_name' => $wizardData['first_name'],
                    'surname' => $wizardData['surname'],
                    'last_name' => $wizardData['last_name'] ?? null,
                    'email' => $wizardData['email'],
                    'phone_number' => $wizardData['phone_number'],
                    'id_number' => $wizardData['id_number'],
                    'password' => $wizardData['password_hash'],
                    'status' => 'pending',
                ]);

                \Log::info('User created with ID: ' . $user->id);

                // Assign role
                $role = Role::findOrFail($wizardData['role_id']);
                $user->assignRole($role);
                \Log::info('Role assigned: ' . $role->name);

                // Create party member profile if selected
                if (isset($wizardData['register_as_party_member']) && $wizardData['register_as_party_member']) {
                    \Log::info('Creating party member profile');
                    $this->createPartyMemberProfile($user, $wizardData);
                }

                $createdUser = $user;
            }

            // Handle photo upload if provided (attach to the appropriate model)
            if (isset($wizardData['photo_temp_path'])) {
                \Log::info('Processing photo upload');
                $tempPath = $wizardData['photo_temp_path'];
                $originalName = $wizardData['photo_original_name'] ?? 'profile_photo.jpg';
                
                // Check if temporary file exists
                if (Storage::disk('local')->exists($tempPath)) {
                    try {
                        // Add media from the temporary file
                        $createdUser->addMediaFromDisk($tempPath, 'local')
                            ->usingName($originalName)
                            ->toMediaCollection('profile_photos');
                        
                        // Clean up temporary file
                        Storage::disk('local')->delete($tempPath);
                        \Log::info('Photo processed successfully');
                    } catch (\Exception $e) {
                        // Log the error but don't fail the registration
                        \Log::error('Failed to process uploaded photo: ' . $e->getMessage());
                    }
                } else {
                    \Log::warning('Temporary photo file not found: ' . $tempPath);
                }
            }

            DB::commit();
            \Log::info('Registration completed successfully');

            // Registration successful, clear wizard data
            $this->cleanupWizard();

            // Create appropriate success message
            $successMessage = 'User created successfully! Password: ' . $wizardData['password_hash'];
            
            if ($wizardData['user_type'] === 'admin' && isset($wizardData['register_as_party_member']) && $wizardData['register_as_party_member']) {
                $successMessage .= ' (Admin account + Party Member profile created)';
            } elseif ($wizardData['user_type'] === 'admin') {
                $successMessage .= ' (Admin account created)';
            } else {
                $successMessage .= ' (User account created)';
                if (isset($wizardData['register_as_party_member']) && $wizardData['register_as_party_member']) {
                    $successMessage .= ' + Party Member profile';
                }
            }

            return redirect()->route('admin.users.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Registration failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('admin.users.wizard.step7')
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Create party member profile for user.
     */
    private function createPartyMemberProfile($user, $wizardData)
    {
        // Get profile type (default to Party Member)
        $profileType = \Modules\UserManagement\Entities\ProfileType::where('code', 'PM')->first();
        
        if (!$profileType) {
            throw new \Exception('Default profile type not found.');
        }

        // Create profile
        $profile = \Modules\UserManagement\Entities\Profile::create([
            'user_id' => $user->id,
            'profile_type_id' => $profileType->id,
            'mobile_number' => $wizardData['phone_number'],
            'id_passport_number' => $wizardData['id_number'] ?? null,
            'date_of_birth' => $wizardData['date_of_birth'] ?? null,
            'postal_address' => $wizardData['postal_address'] ?? null,
            'gender' => $wizardData['gender'] ?? null,
            'ethnicity_id' => $wizardData['ethnicity_id'] ?? null,
            'special_status_id' => $wizardData['special_status_id'] ?? null,
            'ncpwd_number' => $wizardData['ncpwd_number'] ?? null,
            'religion_id' => $wizardData['religion_id'] ?? null,
            'mobile_provider_id' => $wizardData['mobile_provider_id'] ?? null,
            'county_id' => $wizardData['county_id'] ?? null,
            'constituency_id' => $wizardData['constituency_id'] ?? null,
            'ward_id' => $wizardData['ward_id'] ?? null,
            'enlisting_date' => now(),
            'recruiting_person' => null,
        ]);

        // Create membership
        $membershipNumber = $this->generateMembershipNumber($profileType->code);
        
        \Modules\UserManagement\Entities\Membership::create([
            'user_id' => $user->id,
            'membership_number' => $membershipNumber,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'membership_type' => 'regular',
        ]);

        // Assign party_member role to the user
        $user->assignRole('party_member');
    }

    /**
     * Generate membership number based on profile type.
     */
    private function generateMembershipNumber($profileTypeCode)
    {
        $prefix = strtoupper('NPK-'.$profileTypeCode);
        $year = date('Y');
        $count = \Modules\UserManagement\Entities\Membership::whereYear('created_at', $year)->count() + 1;
        
        return $prefix . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store data in the wizard session.
     */
    private function storeWizardData($key, $value)
    {
        $wizardData = Session::get('wizard_data', []);
        $wizardData[$key] = $value;
        Session::put('wizard_data', $wizardData);
    }

    /**
     * Clean up wizard session and temporary files.
     */
    private function cleanupWizard()
    {
        $wizardData = Session::get('wizard_data', []);
        
        // Clean up temporary photo file if exists
        if (isset($wizardData['photo_temp_path'])) {
            $tempPath = $wizardData['photo_temp_path'];
            if (Storage::disk('local')->exists($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }
        }
        
        // Clear session data
        Session::forget('wizard_data');
        Session::forget('wizard_step');
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
          //  dd($user);
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

    /**
     * Cancel the wizard and clean up.
     */
    public function cancelWizard()
    {
        $this->cleanupWizard();
        return redirect()->route('admin.users.index')
            ->with('info', 'User creation wizard cancelled.');
    }
} 