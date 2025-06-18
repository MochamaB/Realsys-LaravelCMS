@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step4" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Party Membership</h4>
            <p class="text-muted">Decide whether this user should be registered as a party member</p>
        </div>

        @if($userType === 'user')
            <!-- For regular users, always register as party member -->
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                <strong>Regular users</strong> are automatically registered as party members to participate in party activities.
            </div>
            
            <form method="POST" action="{{ route('admin.users.wizard.step4.post') }}">
                @csrf
                <input type="hidden" name="register_as_party_member" value="1">
                
                <div class="text-center">
                    <p class="text-muted mb-4">This user will be registered as a party member with the following benefits:</p>
                    <ul class="list-unstyled text-start">
                        <li><i class="ri-check-line text-success me-2"></i>Access to party member portal</li>
                        <li><i class="ri-check-line text-success me-2"></i>Participation in party activities</li>
                        <li><i class="ri-check-line text-success me-2"></i>Receive party communications</li>
                        <li><i class="ri-check-line text-success me-2"></i>Voting rights in party elections</li>
                    </ul>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.users.wizard.step3') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Continue to Additional Info <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </form>
        @else
            <!-- For admin users, show choice -->
            <form method="POST" action="{{ route('admin.users.wizard.step4.post') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="ri-user-line text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="card-title">Admin Only</h5>
                                <p class="card-text">Create this user as an administrator only. They will have access to the admin panel but won't be registered as a party member.</p>
                                <div class="form-check">
                                    <input type="radio" id="register_no" name="register_as_party_member" 
                                           value="0" class="form-check-input" 
                                           {{ old('register_as_party_member') === '0' ? 'checked' : '' }} required>
                                    <label for="register_no" class="form-check-label">
                                        Create as Admin Only
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="ri-group-line text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="card-title">Admin + Party Member</h5>
                                <p class="card-text">Create this user as both an administrator and a party member. They will have admin privileges and can also participate in party activities.</p>
                                <div class="form-check">
                                    <input type="radio" id="register_yes" name="register_as_party_member" 
                                           value="1" class="form-check-input" 
                                           {{ old('register_as_party_member') === '1' ? 'checked' : '' }} required>
                                    <label for="register_yes" class="form-check-label">
                                        Create as Admin + Party Member
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                @error('register_as_party_member')
                    <div class="alert alert-danger mt-3">
                        <i class="ri-error-warning-line me-2"></i>
                        {{ $message }}
                    </div>
                @enderror
                
                <div class="d-flex justify-content-between mt-4 wizardbuttons">
                    <a href="{{ route('admin.users.wizard.step3') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Continue <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@section('step-scripts')
<script>
    $(document).ready(function() {
        // Auto-select card when radio is clicked
        $('input[type="radio"]').change(function() {
            $('.card').removeClass('border-primary');
            $(this).closest('.card').addClass('border-primary');
        });
        
        // Initialize selection
        $('input[type="radio"]:checked').closest('.card').addClass('border-primary');
    });
</script>
@endsection 