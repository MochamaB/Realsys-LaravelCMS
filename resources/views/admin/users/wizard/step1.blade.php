@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step1" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Select User Type</h4>
            <p class="text-muted">Choose the type of user you want to create</p>
        </div>

        <form method="POST" action="{{ route('admin.users.wizard.step1.post') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="user-type-card {{ old('user_type') == 'admin' ? 'selected' : '' }}">
                        <div class="card-icon">
                            <i class="ri-admin-line"></i>
                        </div>
                        <div class="form-check mb-0">
                            <input type="radio" id="user_type_admin" name="user_type" 
                                value="admin" class="form-check-input" 
                                {{ old('user_type') == 'admin' ? 'checked' : '' }} required>
                            <label for="user_type_admin" class="form-check-label card-title">
                                Administrator
                            </label>
                            <p class="card-text">Create an admin user with system management privileges. Admins can access the admin panel and manage the system.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="user-type-card {{ old('user_type') == 'user' ? 'selected' : '' }}">
                        <div class="card-icon">
                            <i class="ri-user-line"></i>
                        </div>
                        <div class="form-check mb-0">
                            <input type="radio" id="user_type_user" name="user_type" 
                                value="user" class="form-check-input" 
                                {{ old('user_type') == 'user' ? 'checked' : '' }} required>
                            <label for="user_type_user" class="form-check-label card-title">
                                Regular User
                            </label>
                            <p class="card-text">Create a regular user who can register as a party member. Users can access the frontend and participate in party activities.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            @error('user_type')
                <div class="alert alert-danger mt-3">
                    <i class="ri-error-warning-line me-2"></i>
                    {{ $message }}
                </div>
            @enderror
            
            <div class="d-flex justify-content-between mt-4 wizardbuttons">
                <div></div> <!-- Empty div for spacing -->
                <button type="submit" class="btn btn-primary">
                    Continue <i class="ri-arrow-right-line ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('step-scripts')
<script>
    $(document).ready(function() {
        // Additional step-specific JavaScript can be added here
    });
</script>
@endsection 