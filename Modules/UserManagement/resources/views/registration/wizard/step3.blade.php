@extends('usermanagement::registration.wizard.layout')

@section('wizard-content')
    <h4 class="text-danger mb-3">Additional Information</h4>
    <p class="text-muted mb-4">Please provide additional details to help us better understand your profile.</p>
    <hr>
    
    <form method="POST" action="{{ route('usermanagement.register.wizard.step3.post') }}">
        @csrf
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="gender">Gender <span class="text-danger">*</span></label>
                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', session('wizard_data.gender', '')) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', session('wizard_data.gender', '')) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', session('wizard_data.gender', '')) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ethnicity_id">Ethnicity</label>
                    <select class="form-control @error('ethnicity_id') is-invalid @enderror" id="ethnicity_id" name="ethnicity_id">
                        <option value="">Select Ethnicity</option>
                        @foreach($ethnicities as $ethnicity)
                            <option value="{{ $ethnicity->id }}" {{ old('ethnicity_id', session('wizard_data.ethnicity_id', '')) == $ethnicity->id ? 'selected' : '' }}>
                                {{ $ethnicity->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ethnicity_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="special_status_id">Special Status</label>
                    <select class="form-control @error('special_status_id') is-invalid @enderror" id="special_status_id" name="special_status_id">
                        <option value="">Select Special Status (if applicable)</option>
                        @foreach($specialStatuses as $status)
                            <option value="{{ $status->id }}" {{ old('special_status_id', session('wizard_data.special_status_id', '')) == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('special_status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ncpwd_number">NCPWD Number</label>
                    <input type="text" class="form-control @error('ncpwd_number') is-invalid @enderror" id="ncpwd_number" name="ncpwd_number" 
                           value="{{ old('ncpwd_number', session('wizard_data.ncpwd_number', '')) }}"
                           {{ old('special_status_id', session('wizard_data.special_status_id')) == 2 ? '' : 'disabled' }}>
                    @error('ncpwd_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Required only for Persons with Disabilities.</small>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="religion_id">Religion</label>
                    <select class="form-control @error('religion_id') is-invalid @enderror" id="religion_id" name="religion_id">
                        <option value="">Select Religion</option>
                        @foreach($religions as $religion)
                            <option value="{{ $religion->id }}" {{ old('religion_id', session('wizard_data.religion_id', '')) == $religion->id ? 'selected' : '' }}>
                                {{ $religion->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('religion_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="mobile_provider_id">Mobile Service Provider</label>
                    <select class="form-control @error('mobile_provider_id') is-invalid @enderror" id="mobile_provider_id" name="mobile_provider_id">
                        <option value="">Select Provider</option>
                        @foreach($mobileProviders as $provider)
                            <option value="{{ $provider->id }}" {{ old('mobile_provider_id', session('wizard_data.mobile_provider_id', '')) == $provider->id ? 'selected' : '' }}>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('mobile_provider_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="wizard-footer">
            <a href="{{ route('usermanagement.register.wizard.step2') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" class="btn btn-primary">Continue <i class="fa fa-arrow-right ms-1"></i></button>
        </div>
    </form>
@endsection
@push('scripts')

<script>
    
    $(document).ready(function() {
        // Function to handle special status change
        function handleSpecialStatusChange() {
            
            var selectedValue = $('#special_status_id').val();
            if (selectedValue == 2) { // Assuming 2 is the ID for "Person with Disabilities"
                $('#ncpwd_number').prop('disabled', false);
                // Add prefix if the field is empty
                if (!$('#ncpwd_number').val()) {
                    $('#ncpwd_number').val('NC{{ session('wizard_data.special_status_code') }}');
                }
            } else {
                $('#ncpwd_number').prop('disabled', true).val('');
            }
        }
        
        // Initial call to set correct state
        handleSpecialStatusChange();
        
        // Add change event listener
        $('#special_status_id').on('change', handleSpecialStatusChange);
    });
</script>
@endpush
