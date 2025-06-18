@extends('admin.users.wizard.layout')

@section('wizard-content')
<div class="tab-pane fade show active" id="step2" role="tabpanel">
    <div class="wizard-step-content">
        <div class="text-center mb-4">
            <h4 class="mb-2">Select Role</h4>
            <p class="text-muted">Choose the appropriate role for this {{ $userType === 'admin' ? 'administrator' : 'user' }}</p>
        </div>

        <form method="POST" action="{{ route('admin.users.wizard.step2.post') }}">
            @csrf
            
            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            @if($userType === 'admin')
                                Admin roles have access to the admin panel and system management features.
                            @else
                                User roles determine the level of access and permissions for regular users.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4 wizardbuttons">
                <a href="{{ route('admin.users.wizard.step1') }}" class="btn btn-outline-secondary">
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
        // Additional step-specific JavaScript can be added here
    });
</script>
@endsection 