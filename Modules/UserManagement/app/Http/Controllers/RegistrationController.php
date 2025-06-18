<?php

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\County;
use Modules\UserManagement\Entities\Constituency;
use Modules\UserManagement\Entities\Ethnicity;
use Modules\UserManagement\Entities\Membership;
use Modules\UserManagement\Entities\MobileProvider;
use Modules\UserManagement\Entities\Profile;
use Modules\UserManagement\Entities\ProfileType;
use Modules\UserManagement\Entities\Religion;
use Modules\UserManagement\Entities\SpecialStatus;
use Modules\UserManagement\Entities\Ward;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class RegistrationController extends Controller
{
    // Existing methods remain unchanged

    /**
     * Entry point for the registration wizard.
     * Redirects to the first step.
     */
    public function showWizard()
    {
        return redirect()->route('usermanagement.register.wizard.step1');
    }

    /**
     * Show step 1 of the registration wizard - Profile Type.
     */
    public function showStep1()
    {
        // Get profile types (limiting to only Party Member, Volunteer and Voter types for now)
        $profileTypes = ProfileType::whereIn('code', ['PM', 'VOLUNTEER', 'VOTER'])->get();
        
        return view('usermanagement::registration.wizard.step1', compact('profileTypes'));
    }

    /**
     * Process step 1 submission.
     */
    public function submitStep1(Request $request)
    {
        // Validate the profile type selection
        $validator = Validator::make($request->all(), [
            'profile_type_id' => ['required', 'exists:profile_types,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('usermanagement.register.wizard.step1')
                ->withErrors($validator)
                ->withInput();
        }

        // Get profile type name for the success message
        $profileType = ProfileType::find($request->profile_type_id);
        
        // Store data in session
        $this->storeWizardData('profile_type_id', $request->profile_type_id);
        
        // Set current step in session
        Session::put('wizard_step', 2);
        
        // Proceed to next step with success message
        return redirect()->route('usermanagement.register.wizard.step2')
            ->with('success', 'Profile type "' . $profileType->name . '" selected successfully. Please complete your personal information.');
    }

    /**
     * Show step 2 of the registration wizard - Personal Information.
     */
    public function showStep2()
    {
        // Check if step 1 was completed
        if (!Session::has('wizard_data.profile_type_id')) {
            return redirect()->route('usermanagement.register.wizard.step1')
                ->with('error', 'Please select a profile type first.');
        }
        
        return view('usermanagement::registration.wizard.step2');
    }

    /**
     * Process step 2 submission.
     */
    public function submitStep2(Request $request)
    {
        // Validate personal information
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:15', 'unique:profiles,mobile_number'],
            'id_number' => ['required', 'string', 'max:20', 'unique:profiles,id_passport_number'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'postal_address' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('usermanagement.register.wizard.step2')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $fields = [
            'first_name', 'surname', 'last_name', 'email', 'phone_number',
            'id_number', 'date_of_birth', 'postal_address', 'password'
        ];
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->storeWizardData($field, $request->input($field));
            }
        }
        $password = 'NPPK.123';
        // Store hashed password
        $this->storeWizardData('password_hash', Hash::make($password));
        
        // Set current step in session
        Session::put('wizard_step', 3);
        
        // Proceed to next step with success message
        return redirect()->route('usermanagement.register.wizard.step3')
            ->with('success', 'Personal information saved successfully. Please provide additional details.');
    }

    /**
     * Show step 3 of the registration wizard - Additional Information.
     */
    public function showStep3()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 2) {
            return redirect()->route('usermanagement.register.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }
        
        // Get data for dropdowns
        $ethnicities = Ethnicity::orderBy('name')->get();
        $specialStatuses = SpecialStatus::orderBy('name')->get();
        $religions = Religion::orderBy('name')->get();
        $mobileProviders = MobileProvider::all();
        
        return view('usermanagement::registration.wizard.step3', compact(
            'ethnicities', 
            'specialStatuses', 
            'religions', 
            'mobileProviders'
        ));
    }

    /**
     * Process step 3 submission.
     */
    public function submitStep3(Request $request)
    {
        // Validate additional information
        $validator = Validator::make($request->all(), [
            'gender' => ['required', 'string', 'in:male,female,other'],
            'ethnicity_id' => ['nullable', 'exists:ethnicities,id'],
            'special_status_id' => ['nullable', 'exists:special_statuses,id'],
            'ncpwd_number' => ['nullable', 'string', 'max:50', 'required_if:special_status_id,2'], // Assuming 2 is PWD
            'religion_id' => ['nullable', 'exists:religions,id'],
            'mobile_provider_id' => ['nullable', 'exists:mobile_providers,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('usermanagement.register.wizard.step3')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $fields = [
            'gender', 'ethnicity_id', 'special_status_id', 'ncpwd_number',
            'religion_id', 'mobile_provider_id'
        ];
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $this->storeWizardData($field, $request->input($field));
            }
        }
        
        // Set current step in session
        Session::put('wizard_step', 4); 
        
        // Proceed to next step with success message
        return redirect()->route('usermanagement.register.wizard.step4')
            ->with('success', 'Additional information saved successfully. Please provide your geographic details.');
    }

    /**
     * Show step 4 of the registration wizard - Geographic Information.
     */
    public function showStep4()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 3) {
            return redirect()->route('usermanagement.register.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }
        
        // Get counties for dropdown
        $counties = County::orderBy('name')->get();
        
        // Get constituencies if county is selected
        $constituencies = [];
        if (Session::has('wizard_data.county_id')) {
            $constituencies = Constituency::where('county_id', Session::get('wizard_data.county_id'))
                ->orderBy('name')
                ->get();
        }
        
        // Get wards if constituency is selected
        $wards = [];
        if (Session::has('wizard_data.constituency_id')) {
            $wards = Ward::where('constituency_id', Session::get('wizard_data.constituency_id'))
                ->orderBy('name')
                ->get();
        }
        
        return view('usermanagement::registration.wizard.step4', compact(
            'counties', 'constituencies', 'wards'
        ));
    }
    public function getConstituencies(Request $request)
    {
        $constituencies = Constituency::where('county_id', $request->county_id)
            ->orderBy('name')
            ->get();
            
        return response()->json($constituencies);
    }
    
    /**
     * Get wards for a constituency.
     */
    public function getWards(Request $request)
    {
        $wards = Ward::where('constituency_id', $request->constituency_id)
            ->orderBy('name')
            ->get();
            
        return response()->json($wards);
    }

    /**
     * Process step 4 submission.
     */
    public function submitStep4(Request $request)
    {
        // Validate geographic information
        $validator = Validator::make($request->all(), [
            'county_id' => ['required', 'exists:counties,id'],
            'constituency_id' => ['required', 'exists:constituencies,id'],
            'ward_id' => ['required', 'exists:wards,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('usermanagement.register.wizard.step4')
                ->withErrors($validator)
                ->withInput();
        }

        // Store data in session
        $this->storeWizardData('county_id', $request->county_id);
        $this->storeWizardData('constituency_id', $request->constituency_id);
        $this->storeWizardData('ward_id', $request->ward_id);
        
        // Set current step in session
        Session::put('wizard_step', 5);
        
        // Proceed to next step with success message
        return redirect()->route('usermanagement.register.wizard.step5')
            ->with('success', 'Geographic information saved successfully. Please review and accept terms to complete registration.');
    }

    /**
     * Show step 5 of the registration wizard - Terms & Photo.
     */
    public function showStep5()
    {
        // Check if previous steps were completed
        if (!Session::has('wizard_step') || Session::get('wizard_step') < 4) {
            return redirect()->route('usermanagement.register.wizard.step1')
                ->with('error', 'Please complete the previous steps first.');
        }
        
        return view('usermanagement::registration.wizard.step5');
    }

    /**
     * Process final step submission and complete registration.
     */
    public function submitStep5(Request $request)
    {
        // Validate final information
        $validator = Validator::make($request->all(), [
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'agree_terms' => ['required', 'boolean', 'accepted'],
            'agree_privacy' => ['required', 'boolean', 'accepted'],
            'agree_marketing' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('usermanagement.register.wizard.step5')
                ->withErrors($validator)
                ->withInput();
        }

        // Get wizard data from session
        $wizardData = Session::get('wizard_data', []);
        
        // Check if all required data is present
        $requiredKeys = [
            'profile_type_id', 'first_name', 'surname', 'email', 'phone_number',
            'id_number', 'date_of_birth', 'password_hash', 'gender',
            'county_id', 'constituency_id', 'ward_id'
        ];
        
        foreach ($requiredKeys as $key) {
            if (!isset($wizardData[$key])) {
                return redirect()->route('usermanagement.register.wizard.step1')
                    ->with('error', 'Missing required information. Please complete all steps.');
            }
        }

        try {
            // Start database transaction
            DB::beginTransaction();
            
            // Create user
            $fullName = $wizardData['first_name'] . ' ' . $wizardData['surname'];
            if (!empty($wizardData['last_name'])) {
                $fullName .= ' ' . $wizardData['last_name'];
            }
            
            $user = User::create([
                'first_name' => $wizardData['first_name'],
                'surname' => $wizardData['surname'],
                'last_name' => $wizardData['last_name'],
                'email' => $wizardData['email'],
                'password' => $wizardData['password_hash'],
                'phone_number' => $wizardData['phone_number'],
                'id_number' => $wizardData['id_number'],
            ]);
            
            // Get profile type
            $profileType = ProfileType::findOrFail($wizardData['profile_type_id']);
            
            // Create profile
            $profile = Profile::create([
                'user_id' => $user->id,
                'id_passport_number' => $wizardData['id_number'],
                'date_of_birth' => $wizardData['date_of_birth'],
                'postal_address' => $wizardData['postal_address'] ?? null,
                'mobile_number' => $wizardData['phone_number'],
                'gender' => $wizardData['gender'],
                'ethnicity_id' => $wizardData['ethnicity_id'] ?? null,
                'special_status_id' => $wizardData['special_status_id'] ?? null,
                'ncpwd_number' => $wizardData['ncpwd_number'] ?? null,
                'religion_id' => $wizardData['religion_id'] ?? null,
                'mobile_provider_id' => $wizardData['mobile_provider_id'] ?? null,
                'county_id' => $wizardData['county_id'],
                'constituency_id' => $wizardData['constituency_id'],
                'ward_id' => $wizardData['ward_id'],
                'enlisting_date' => now(),
                'recruiting_person' => null,
                'profile_type_id' => $profileType->id,
                'additional_data' => [
                    'agree_marketing' => $request->has('agree_marketing'),
                ],
            ]);
            
            // Generate membership number based on profile type
            $membershipNumber = $this->generateMembershipNumber($profileType->code);
            
            // Create membership record
            $membership = Membership::create([
                'membership_number' => $membershipNumber,
                'user_id' => $user->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'membership_type' => 'regular',
            ]);
            
            // Assign role based on profile type
            switch ($profileType->code) {
                case 'PM':
                    $user->assignRole('party_member');
                    break;
                case 'VOLUNTEER':
                    $user->assignRole('volunteer');
                    break;
                case 'VOTER':
                    $user->assignRole('voter');
                    break;
                default:
                    $user->assignRole('party_member');
            }
            
            // Handle photo upload if provided
            if ($request->hasFile('photo')) {
                $user->addMediaFromRequest('photo')
                    ->toMediaCollection('profile_photos');
            }
            
            // Commit the transaction
            DB::commit();
            
            // Registration successful, clear wizard data
            Session::forget('wizard_data');
            Session::forget('wizard_step');
            
            // Redirect to login page with success message
            return redirect()->route('login', [
                'email' => $user->email,
                'auto_login' => true
                ])->with('success', 'Registration successful! You can now log in with your account.');
                
        } catch (\Exception $e) {
            // Rollback the transaction in case of failure
            DB::rollback();
            
            return redirect()->route('usermanagement.register.wizard.step5')
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->withInput();
        }
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
     * Generate membership number based on profile type.
     */
    private function generateMembershipNumber($profileTypeCode)
    {
        $prefix = strtoupper('NPK-'.$profileTypeCode);
        $year = date('Y');
        $count = \Modules\UserManagement\Entities\Membership::whereYear('created_at', $year)->count() + 1;
        
        return $prefix . $year . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}