@extends('auth.layouts.cover')

@section('title', 'Change Password')

@section('content')
<h5 class="text-primary">Change Your Password</h5>
<p class="text-muted">For security reasons, you need to change your default password before continuing.</p>

<div class="mt-2 text-center">
    <i class="ri-lock-password-line display-5 text-danger"></i>
</div>

<div class="p-2 mt-4">
    <form action="{{ route('password.force_change.update') }}" method="POST">
        @csrf
        
        <!-- Current Password -->
        <div class="mb-3">
            <label class="form-label" for="current-password-input">Current Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 password-input @error('current_password') is-invalid @enderror" 
                       placeholder="Enter current password" id="current-password-input" name="current_password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" 
                        type="button"><i class="ri-eye-fill align-middle"></i></button>
                @error('current_password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label class="form-label" for="password-input">New Password</label>
            <div class="position-relative auth-pass-inputgroup">
                <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" 
                       placeholder="Enter new password" id="password-input" name="password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" 
                        type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Confirm New Password -->
        <div class="mb-3">
            <label class="form-label" for="confirm-password-input">Confirm New Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 password-input" 
                       placeholder="Confirm new password" id="confirm-password-input" name="password_confirmation" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" 
                        type="button"><i class="ri-eye-fill align-middle"></i></button>
            </div>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-danger w-100" type="submit">Change Password</button>
        </div>
    </form>
</div>
@endsection