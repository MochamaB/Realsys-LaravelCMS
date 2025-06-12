@extends('auth.layouts.cover')

@section('title', 'Reset Password')

@section('content')
<h5 class="text-primary">Forgot Password?</h5>
<p class="text-muted">Enter your email and we'll send you instructions to reset your password</p>

<div class="mt-2 text-center">
    <i class="ri-mail-send-line display-5 text-danger"></i>
</div>

<div class="alert border-0 alert-warning text-center mb-2 mx-2 mt-4" role="alert">
    Enter your email and instructions will be sent to you!
</div>

<div class="p-2 mt-4">
    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-4">
            <label class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" placeholder="Enter email address" 
                   value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-danger w-100" type="submit">Send Reset Link</button>
        </div>
    </form>
</div>

<div class="mt-4 text-center">
    <p class="mb-0">Wait, I remember my password... <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline"> Click here </a> </p>
</div>
@endsection
