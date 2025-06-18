@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step5" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Additional Information</h4>
            <p class="text-muted">Provide additional details for party membership registration</p>
        </div>

        <form method="POST" action="{{ route('admin.users.wizard.step5.post') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
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
                    <div class="mb-3">
                        <label for="ethnicity_id" class="form-label">Ethnicity</label>
                        <select class="form-select @error('ethnicity_id') is-invalid @enderror" id="ethnicity_id" name="ethnicity_id">
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
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="special_status_id" class="form-label">Special Status</label>
                        <select class="form-select @error('special_status_id') is-invalid @enderror" id="special_status_id" name="special_status_id">
                            <option value="">Select Special Status</option>
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
                    <div class="mb-3">
                        <label for="ncpwd_number" class="form-label">NCPWD Number</label>
                        <input type="text" class="form-control @error('ncpwd_number') is-invalid @enderror" 
                               id="ncpwd_number" name="ncpwd_number" 
                               value="{{ old('ncpwd_number', session('wizard_data.ncpwd_number', '')) }}">
                        @error('ncpwd_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Required if Person with Disability is selected</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="religion_id" class="form-label">Religion</label>
                        <select class="form-select @error('religion_id') is-invalid @enderror" id="religion_id" name="religion_id">
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
                    <div class="mb-3">
                        <label for="mobile_provider_id" class="form-label">Mobile Service Provider</label>
                        <select class="form-select @error('mobile_provider_id') is-invalid @enderror" id="mobile_provider_id" name="mobile_provider_id">
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
            
            <div class="d-flex justify-content-between mt-4 wizardbuttons">
                <a href="{{ route('admin.users.wizard.step4') }}" class="btn btn-outline-secondary">
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
        // Show/hide NCPWD number field based on special status selection
        $('#special_status_id').change(function() {
            const selectedValue = $(this).val();
            const ncpwdField = $('#ncpwd_number').closest('.mb-3');
            
            if (selectedValue === '2') { // Assuming 2 is PWD status
                ncpwdField.show();
                $('#ncpwd_number').prop('required', true);
            } else {
                ncpwdField.hide();
                $('#ncpwd_number').prop('required', false);
                $('#ncpwd_number').val('');
            }
        });
        
        // Initialize on page load
        $('#special_status_id').trigger('change');
    });
</script>
@endsection 