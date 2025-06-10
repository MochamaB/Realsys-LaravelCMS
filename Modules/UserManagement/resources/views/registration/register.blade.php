<x-usermanagement::layouts.master>
<x-slot name="title">Join NPK Party - User Registration</x-slot>

<x-slot name="styles">
<style>
    .card {
        border-radius: 10px;
    }
    .card-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
</style>
</x-slot>

<x-slot name="scripts">
<script>
    $(document).ready(function() {
        // County dropdown change event
        $('#county_id').change(function() {
            var countyId = $(this).val();
            if (countyId) {
                // Clear existing options
                $('#constituency_id').empty().append('<option value="">Loading constituencies...</option>');
                $('#ward_id').empty().append('<option value="">Select Constituency First</option>');
                
                // AJAX request to get constituencies
                $.ajax({
                    url: '{{ route("usermanagement.constituencies") }}',
                    type: 'GET',
                    data: { county_id: countyId },
                    dataType: 'json',
                    success: function(data) {
                        $('#constituency_id').empty().append('<option value="">Select Constituency</option>');
                        $.each(data, function(key, constituency) {
                            $('#constituency_id').append('<option value="' + constituency.id + '">' + constituency.name + '</option>');
                        });
                    }
                });
            } else {
                $('#constituency_id').empty().append('<option value="">Select County First</option>');
                $('#ward_id').empty().append('<option value="">Select Constituency First</option>');
            }
        });
        
        // Constituency dropdown change event
        $('#constituency_id').change(function() {
            var constituencyId = $(this).val();
            if (constituencyId) {
                // Clear existing options
                $('#ward_id').empty().append('<option value="">Loading wards...</option>');
                
                // AJAX request to get wards
                $.ajax({
                    url: '{{ route("usermanagement.wards") }}',
                    type: 'GET',
                    data: { constituency_id: constituencyId },
                    dataType: 'json',
                    success: function(data) {
                        $('#ward_id').empty().append('<option value="">Select Ward</option>');
                        $.each(data, function(key, ward) {
                            $('#ward_id').append('<option value="' + ward.id + '">' + ward.name + '</option>');
                        });
                    }
                });
            } else {
                $('#ward_id').empty().append('<option value="">Select Constituency First</option>');
            }
        });
        
        // Special status dropdown change event
        $('#special_status_id').change(function() {
            if ($(this).val()) {
                $('#special_status_number').prop('required', true);
                $('#special_status_number_group').show();
            } else {
                $('#special_status_number').prop('required', false);
                $('#special_status_number_group').hide();
            }
        });
        
        // Initialize the special status visibility
        if ($('#special_status_id').val()) {
            $('#special_status_number_group').show();
        } else {
            $('#special_status_number_group').hide();
        }
    });
</script>
</x-slot>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Join NPK Party</h3>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('usermanagement.register.submit') }}">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="mb-4">
                            <h4>Basic Information</h4>
                            <hr>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="mb-4 mt-5">
                            <h4>Personal Information</h4>
                            <hr>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_passport_number">ID/Passport Number</label>
                                    <input type="text" class="form-control @error('id_passport_number') is-invalid @enderror" id="id_passport_number" name="id_passport_number" value="{{ old('id_passport_number') }}" required>
                                    @error('id_passport_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
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
                                            <option value="{{ $ethnicity->id }}" {{ old('ethnicity_id') == $ethnicity->id ? 'selected' : '' }}>
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

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile_number">Mobile Number</label>
                                    <div class="input-group">
                                        <select class="form-control @error('mobile_provider_id') is-invalid @enderror" style="max-width: 120px;" id="mobile_provider_id" name="mobile_provider_id">
                                            <option value="">Provider</option>
                                            @foreach($mobileProviders as $provider)
                                                <option value="{{ $provider->id }}" {{ old('mobile_provider_id') == $provider->id ? 'selected' : '' }}>
                                                    {{ $provider->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}" required placeholder="e.g. 712345678">
                                    </div>
                                    @error('mobile_number')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="religion_id">Religion</label>
                                    <select class="form-control @error('religion_id') is-invalid @enderror" id="religion_id" name="religion_id">
                                        <option value="">Select Religion</option>
                                        @foreach($religions as $religion)
                                            <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                                {{ $religion->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('religion_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Special Status -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="special_status_id">Special Status</label>
                                    <select class="form-control @error('special_status_id') is-invalid @enderror" id="special_status_id" name="special_status_id">
                                        <option value="">Select Special Status</option>
                                        @foreach($specialStatuses as $status)
                                            <option value="{{ $status->id }}" {{ old('special_status_id') == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="special_status_number">Special Status ID Number</label>
                                    <input type="text" class="form-control" id="special_status_number" name="special_status_number" value="{{ old('special_status_number') }}" placeholder="e.g. NCPWD Number">
                                </div>
                            </div>
                        </div>

                        <!-- Geographic Information -->
                        <div class="mb-4 mt-5">
                            <h4>Geographic Information</h4>
                            <hr>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="county_id">County</label>
                                    <select class="form-control @error('county_id') is-invalid @enderror" id="county_id" name="county_id" required>
                                        <option value="">Select County</option>
                                        @foreach($counties as $county)
                                            <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>
                                                {{ $county->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('county_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="constituency_id">Constituency</label>
                                    <select class="form-control @error('constituency_id') is-invalid @enderror" id="constituency_id" name="constituency_id" required>
                                        <option value="">Select County First</option>
                                    </select>
                                    @error('constituency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ward_id">Ward</label>
                                    <select class="form-control @error('ward_id') is-invalid @enderror" id="ward_id" name="ward_id" required>
                                        <option value="">Select Constituency First</option>
                                    </select>
                                    @error('ward_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="postal_address">Postal Address</label>
                                    <textarea class="form-control" id="postal_address" name="postal_address" rows="2">{{ old('postal_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-usermanagement::layouts.master>
