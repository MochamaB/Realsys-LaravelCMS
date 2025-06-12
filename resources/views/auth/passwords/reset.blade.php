@extends('auth.layouts.cover')

@section('title', 'Set New Password')

@section('content')
<h5 class="text-primary">Create New Password</h5>
<p class="text-muted">Enter your new password to access your account</p>

<div class="mt-2 text-center">
    <i class="ri-lock-password-line display-5 text-danger"></i>
</div>

<div class="p-2 mt-4">
    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">
        
        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" placeholder="Enter email address" 
                   value="{{ $email ?? old('email') }}" required autofocus readonly>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label" for="password-input">Password</label>
            <div class="position-relative auth-pass-inputgroup">
                <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" 
                       placeholder="Enter password" id="password-input" name="password" required autocomplete="new-password">
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" 
                        type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label" for="confirm-password-input">Confirm Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 password-input" 
                       placeholder="Confirm password" id="confirm-password-input" name="password_confirmation" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" 
                        type="button"><i class="ri-eye-fill align-middle"></i></button>
            </div>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-danger w-100" type="submit">Reset Password</button>
        </div>
    </form>
</div>

<div class="mt-4 text-center">
    <p class="mb-0">Wait, I remember my password... <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Sign in</a></p>
</div>
@endsection
