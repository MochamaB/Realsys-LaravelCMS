@extends('usermanagement::registration.wizard.layout')

@section('wizard-content')
    <h4 class="text-danger mb-3">Choose Your Profile Type</h4>
    <p class="text-muted mb-4">Select the profile type that best describes your intended relationship with our party.</p>
    <hr>
    
    <form method="POST" action="{{ route('usermanagement.register.wizard.step1.post') }}">
        @csrf
        
        <div class="row mb-4">
            @foreach($profileTypes as $profileType)
                <div class="col-md-6 mb-3">
                    <div class="profile-type-card {{ old('profile_type_id') == $profileType->id ? 'selected' : '' }}">
                        <div class="card-icon">
                            @if($profileType->code == 'PM')
                                <i class="fa fa-user"></i>
                            @elseif($profileType->code == 'VOLUNTEER')
                                <i class="fa fa-user-plus"></i>
                            @elseif($profileType->code == 'VOTER')
                                <i class="fa fa-check-circle"></i>
                            @endif
                        </div>
                        <div class="form-check mb-0">
                            <input type="radio" id="profile_type_{{ $profileType->id }}" name="profile_type_id" 
                                value="{{ $profileType->id }}" class="form-check-input" 
                                {{ old('profile_type_id') == $profileType->id ? 'checked' : '' }} required>
                            <label for="profile_type_{{ $profileType->id }}" class="form-check-label card-title">
                                {{ $profileType->name }}
                            </label>
                            <p class="card-text">{{ $profileType->description }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @error('profile_type_id')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        
        <div class="wizard-footer">
            <div></div> <!-- Empty div for spacing -->
            <button type="submit" class="btn btn-primary">Continue <i class="fa fa-arrow-right ms-1"></i></button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-check the radio when the card is clicked
        $('.profile-type-card').click(function() {
            $(this).find('input[type="radio"]').prop('checked', true);
        });
    });
</script>
@endpush
