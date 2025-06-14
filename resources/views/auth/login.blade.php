@extends('auth.layouts.cover')

@section('title', 'Login')

@section('content')
<div>
    <h5 class="text-primary">Welcome Back !</h5>
    <p class="text-muted">Sign in to continue to your account.</p>
</div>

<div class="mt-4">
    <form action="{{ route('login.post') }}" method="POST" id="loginForm">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter email" value="{{ request()->get('email') ?: old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="float-end">
                <a href="{{ route('password.request') }}" class="text-muted">Forgot password?</a>
            </div>
            <label class="form-label" for="password-input">Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" placeholder="Enter password" id="password-input" name="password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <div class="mt-4">
            <button class="btn btn-danger w-100" type="submit">Sign In</button>
        </div>
        
        @if(request()->get('auto_login'))
        <script>
            // Auto-fill the default password when coming from registration
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('password-input').value = 'NPPK.123';
            });
        </script>
        @endif

        <div class="mt-4 text-center">
            <p class="mb-0">Don't have an account ? <a href="{{ route('usermanagement.register.wizard') }}" class="fw-semibold text-primary text-decoration-underline"> Register </a> </p>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Password show & hide
    $(document).ready(function() {
        $("#password-addon").click(function() {
            var input = $("#password-input");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                $(this).find("i").removeClass("ri-eye-fill").addClass("ri-eye-off-fill");
            } else {
                input.attr("type", "password");
                $(this).find("i").removeClass("ri-eye-off-fill").addClass("ri-eye-fill");
            }
        });

        // Check for default password on form submit
        $("#loginForm").on('submit', function(e) {
            var password = $("#password-input").val();
            if (password === 'NPPK.123') {
                e.preventDefault();
                // Store the form data in session storage
                sessionStorage.setItem('loginEmail', $("#email").val());
                sessionStorage.setItem('loginPassword', password);
                sessionStorage.setItem('loginRemember', $("#remember").is(':checked'));
                // Redirect to force change password
                window.location.href = "{{ route('password.force_change') }}";
            }
        });
    });
</script>
@endsection