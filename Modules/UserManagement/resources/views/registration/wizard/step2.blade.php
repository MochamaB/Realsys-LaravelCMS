@extends('usermanagement::registration.wizard.layout')

@section('wizard-content')
    <h4 class="text-danger mb-3">Personal Information</h4>
    <p class="text-muted mb-4">Please provide your basic personal details.</p>
    <hr>
    
    <form method="POST" action="{{ route('usermanagement.register.wizard.step2.post') }}">
        @csrf
        
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', session('wizard_data.first_name', '')) }}" required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="surname">SurName <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('surname') is-invalid @enderror" id="surname" name="surname" value="{{ old('surname', session('wizard_data.surname', '')) }}" required>
                    @error('surname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="last_name">Middle Name</label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', session('wizard_data.last_name', '')) }}">
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', session('wizard_data.email', '')) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">We'll send you a verification email to this address.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="phone_number">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', session('wizard_data.phone_number', '')) }}" required>
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_number">ID Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('id_number') is-invalid @enderror" id="id_number" name="id_number" value="{{ old('id_number', session('wizard_data.id_number', '')) }}" required>
                    @error('id_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Your National ID or Passport Number.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', session('wizard_data.date_of_birth', '')) }}" required>
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="form-group mb-4">
            <label for="postal_address">Postal Address</label>
            <input type="text" class="form-control @error('postal_address') is-invalid @enderror" id="postal_address" name="postal_address" value="{{ old('postal_address', session('wizard_data.postal_address', '')) }}">
            @error('postal_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        
        <div class="wizard-footer">
            <a href="{{ route('usermanagement.register.wizard.step1') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" class="btn btn-primary">Continue <i class="fa fa-arrow-right ms-1"></i></button>
        </div>
    </form>
@endsection

@section('step-scripts')
<script>
    $(document).ready(function() {
        // Add any step-specific JavaScript here
        $('#email').on('blur', function() {
            // Could add AJAX email verification here
        });
        
        $('#phone_number').on('input', function() {
            // Format phone number as the user types
            let input = $(this).val().replace(/\D/g, '');
            if (input.length > 10) input = input.substring(0, 10);
            $(this).val(input);
        });
    });
</script>
@endsection
