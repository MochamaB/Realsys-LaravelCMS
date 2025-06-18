@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step6" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Geographic Information</h4>
            <p class="text-muted">Select the location details for party membership</p>
        </div>

        <form method="POST" action="{{ route('admin.users.wizard.step6.post') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="county_id" class="form-label">County <span class="text-danger">*</span></label>
                        <select class="form-select @error('county_id') is-invalid @enderror" id="county_id" name="county_id" required>
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
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="constituency_id" class="form-label">Constituency <span class="text-danger">*</span></label>
                        <select class="form-select @error('constituency_id') is-invalid @enderror" id="constituency_id" name="constituency_id" required>
                            <option value="">Select County First</option>
                            @if(old('constituency_id', session('wizard_data.constituency_id')))
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
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="ward_id" class="form-label">Ward <span class="text-danger">*</span></label>
                        <select class="form-select @error('ward_id') is-invalid @enderror" id="ward_id" name="ward_id" required>
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
            
            <div class="d-flex justify-content-between mt-4 wizardbuttons">
                <a href="{{ route('admin.users.wizard.step5') }}" class="btn btn-outline-secondary">
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
        // Load constituencies when county changes
        $('#county_id').change(function() {
            const countyId = $(this).val();
            const constituencySelect = $('#constituency_id');
            const wardSelect = $('#ward_id');
            
            // Reset constituency and ward
            constituencySelect.html('<option value="">Select County First</option>');
            wardSelect.html('<option value="">Select Constituency First</option>');
            
            if (countyId) {
                $.get('{{ route("admin.users.wizard.constituencies") }}', {county_id: countyId})
                    .done(function(data) {
                        constituencySelect.html('<option value="">Select Constituency</option>');
                        data.forEach(function(constituency) {
                            constituencySelect.append('<option value="' + constituency.id + '">' + constituency.name + '</option>');
                        });
                    })
                    .fail(function() {
                        alert('Failed to load constituencies. Please try again.');
                    });
            }
        });
        
        // Load wards when constituency changes
        $('#constituency_id').change(function() {
            const constituencyId = $(this).val();
            const wardSelect = $('#ward_id');
            
            // Reset ward
            wardSelect.html('<option value="">Select Constituency First</option>');
            
            if (constituencyId) {
                $.get('{{ route("admin.users.wizard.wards") }}', {constituency_id: constituencyId})
                    .done(function(data) {
                        wardSelect.html('<option value="">Select Ward</option>');
                        data.forEach(function(ward) {
                            wardSelect.append('<option value="' + ward.id + '">' + ward.name + '</option>');
                        });
                    })
                    .fail(function() {
                        alert('Failed to load wards. Please try again.');
                    });
            }
        });
    });
</script>
@endsection 