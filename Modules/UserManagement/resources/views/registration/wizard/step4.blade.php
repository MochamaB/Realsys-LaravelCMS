@extends('usermanagement::registration.wizard.layout')

@section('wizard-content')
    <h4 class="text-danger mb-3">Geographic Information</h4>
    <p class="text-muted mb-4">Please select your location details.</p>
    <hr>
    
    <form method="POST" action="{{ route('usermanagement.register.wizard.step4.post') }}">
        @csrf
        
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="county_id">County <span class="text-danger">*</span></label>
                    <select class="form-control @error('county_id') is-invalid @enderror" id="county_id" name="county_id" required>
                        <option value="">Select County</option>
                        @foreach($counties as $county)
                            <option value="{{ $county->id }}" {{ old('county_id', session('wizard_data.county_id', '')) == $county->id ? 'selected' : '' }}>
                                {{ $county->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('county_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="constituency_id">Constituency <span class="text-danger">*</span></label>
                    <select class="form-control @error('constituency_id') is-invalid @enderror" id="constituency_id" name="constituency_id" required>
                        <option value="">Select County First</option>
                        @if(old('county_id', session('wizard_data.county_id')))
                            @foreach($constituencies ?? [] as $constituency)
                                <option value="{{ $constituency->id }}" {{ old('constituency_id', session('wizard_data.constituency_id', '')) == $constituency->id ? 'selected' : '' }}>
                                    {{ $constituency->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('constituency_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="ward_id">Ward <span class="text-danger">*</span></label>
                    <select class="form-control @error('ward_id') is-invalid @enderror" id="ward_id" name="ward_id" required>
                        <option value="">Select Constituency First</option>
                        @if(old('constituency_id', session('wizard_data.constituency_id')))
                            @foreach($wards ?? [] as $ward)
                                <option value="{{ $ward->id }}" {{ old('ward_id', session('wizard_data.ward_id', '')) == $ward->id ? 'selected' : '' }}>
                                    {{ $ward->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('ward_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="wizard-footer">
            <a href="{{ route('usermanagement.register.wizard.step3') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" class="btn btn-primary">Continue <i class="fa fa-arrow-right ms-1"></i></button>
        </div>
    </form>
@endsection

@push('scripts')
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
        
        // Trigger change events if values are pre-selected
        if ($('#county_id').val()) {
            $('#county_id').trigger('change');
            
            // After constituencies are loaded, select the saved constituency
            setTimeout(function() {
                if ($('#constituency_id option[value="{{ session('wizard_data.constituency_id', '') }}"]').length > 0) {
                    $('#constituency_id').val('{{ session('wizard_data.constituency_id', '') }}').trigger('change');
                    
                    // After wards are loaded, select the saved ward
                    setTimeout(function() {
                        if ($('#ward_id option[value="{{ session('wizard_data.ward_id', '') }}"]').length > 0) {
                            $('#ward_id').val('{{ session('wizard_data.ward_id', '') }}');
                        }
                    }, 500);
                }
            }, 500);
        }
    });
</script>
@endpush
