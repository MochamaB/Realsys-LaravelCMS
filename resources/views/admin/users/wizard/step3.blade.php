@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step3" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Personal Information</h4>
            <p class="text-muted">Provide the basic personal details for this {{ $userType === 'admin' ? 'administrator' : 'user' }}</p>
        </div>

        <form method="POST" action="{{ route('admin.users.wizard.step3.post') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                               id="first_name" name="first_name" 
                               value="{{ old('first_name', session('wizard_data.first_name', '')) }}" required>
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('surname') is-invalid @enderror" 
                               id="surname" name="surname" 
                               value="{{ old('surname', session('wizard_data.surname', '')) }}" required>
                        @error('surname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                               id="last_name" name="last_name" 
                               value="{{ old('last_name', session('wizard_data.last_name', '')) }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" 
                               value="{{ old('email', session('wizard_data.email', '')) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">This will be used for login and notifications.</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                               id="phone_number" name="phone_number" 
                               value="{{ old('phone_number', session('wizard_data.phone_number', '')) }}" required>
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: 07XXXXXXXX</div>
                    </div>
                </div>
            </div>
            
            @if($userType === 'user')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="id_number" class="form-label">ID Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('id_number') is-invalid @enderror" 
                               id="id_number" name="id_number" 
                               value="{{ old('id_number', session('wizard_data.id_number', '')) }}" required>
                        @error('id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">National ID or Passport number</div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                <strong>Note:</strong> A secure random password will be generated automatically for this user. 
                The password will be displayed after successful creation.
            </div>
            
            <div class="d-flex justify-content-between mt-4 wizardbuttons">
                <a href="{{ route('admin.users.wizard.step2') }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </a>
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
        // Phone number formatting
        $('#phone_number').on('input', function() {
            let input = $(this).val().replace(/\D/g, '');
            if (input.length > 10) input = input.substring(0, 10);
            $(this).val(input);
        });
        
        // ID number formatting (for users only)
        $('#id_number').on('input', function() {
            let input = $(this).val().replace(/\D/g, '');
            if (input.length > 20) input = input.substring(0, 20);
            $(this).val(input);
        });
    });
</script>
@endsection 