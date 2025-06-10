<?php

namespace Modules\UserManagement\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

class RegistrationController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        $counties = County::orderBy('name')->get();
        $ethnicities = Ethnicity::orderBy('name')->get();
        $religions = Religion::orderBy('name')->get();
        $specialStatuses = SpecialStatus::orderBy('name')->get();
        $mobileProviders = MobileProvider::all();
        
        return view('usermanagement::registration.register', compact(
            'counties', 
            'ethnicities', 
            'religions', 
            'specialStatuses',
            'mobileProviders'
        ))->withErrors(session()->get('errors') ?: new \Illuminate\Support\MessageBag());
    }
    
    /**
     * Get constituencies for a county.
     */
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
     * Register a new user.
     */
    public function register(Request $request)
    {
        // Validate user data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'id_passport_number' => ['required', 'string', 'max:20', 'unique:profiles'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'mobile_number' => ['required', 'string', 'max:15', 'unique:profiles'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'county_id' => ['required', 'exists:counties,id'],
            'constituency_id' => ['required', 'exists:constituencies,id'],
            'ward_id' => ['required', 'exists:wards,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('usermanagement.register')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Start database transaction
            DB::beginTransaction();
            
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            // Get party member profile type (Default type for registration)
            $profileType = ProfileType::where('code', 'member')->first();
            if (!$profileType) {
                throw new \Exception("Member profile type not found");
            }
            
            // Create profile
            $profile = Profile::create([
                'user_id' => $user->id,
                'id_passport_number' => $request->id_passport_number,
                'date_of_birth' => $request->date_of_birth,
                'postal_address' => $request->postal_address,
                'mobile_number' => $request->mobile_number,
                'gender' => $request->gender,
                'ethnicity_id' => $request->ethnicity_id,
                'special_status_id' => $request->special_status_id,
                'special_status_number' => $request->special_status_number,
                'religion_id' => $request->religion_id,
                'mobile_provider_id' => $request->mobile_provider_id,
                'county_id' => $request->county_id,
                'constituency_id' => $request->constituency_id,
                'ward_id' => $request->ward_id,
                'enlisting_date' => now(),
                'recruiting_person' => $request->recruiting_person,
                'profile_type_id' => $profileType->id,
                'additional_data' => $request->additional_data ?? null,
            ]);
            
            // Generate unique membership number (e.g., NPK23001)
            $membershipCount = Membership::count() + 1;
            $membershipNumber = 'NPK' . date('y') . str_pad($membershipCount, 3, '0', STR_PAD_LEFT);
            
            // Create membership record
            $membership = Membership::create([
                'membership_number' => $membershipNumber,
                'user_id' => $user->id,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'status' => 'pending', // Initial status requiring approval
                'payment_status' => 'unpaid',
                'membership_type' => 'regular',
            ]);
            
            // Assign party member role to user
            $user->assignRole('party_member');
            
            // Commit the transaction
            DB::commit();
            
            // Redirect to success page
            return redirect()->route('usermanagement.registration.success')
                ->with('membership', $membershipNumber);
                
        } catch (\Exception $e) {
            // Rollback the transaction in case of failure
            DB::rollback();
            
            return redirect()->route('usermanagement.register')
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show registration success page.
     */
    public function showSuccess()
    {
        return view('usermanagement::registration.success');
    }
}
