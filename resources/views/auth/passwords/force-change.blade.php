@extends('auth.layouts.cover')

@section('title', 'Change Password')

@section('content')
<div>
    <h5 class="text-primary">Change Your Password</h5>
    <p class="text-muted">For security reasons, you must change your default password.</p>
</div>

<div class="mt-4">
    <form action="{{ route('password.force_change.update') }}" method="POST" id="forceChangeForm">
        @csrf

        <div class="mb-3">
            <label class="form-label" for="current-password">Current Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 @error('current_password') is-invalid @enderror" 
                    id="current-password" name="current_password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="current-password-addon">
                    <i class="ri-eye-fill align-middle"></i>
                </button>
                @error('current_password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="new-password">New Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" 
                    id="new-password" name="password" required>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="new-password-addon">
                    <i class="ri-eye-fill align-middle"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password-confirm">Confirm New Password</label>
            <div class="position-relative auth-pass-inputgroup mb-3">
                <input type="password" class="form-control pe-5" 
                    id="password-confirm" name="password_confirmation" required>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-danger w-100" type="submit">Change Password</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Password show & hide functionality
        $(".password-addon").click(function() {
            var input = $(this).closest('.auth-pass-inputgroup').find('input');
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                $(this).find("i").removeClass("ri-eye-fill").addClass("ri-eye-off-fill");
            } else {
                input.attr("type", "password");
                $(this).find("i").removeClass("ri-eye-off-fill").addClass("ri-eye-fill");
            }
        });

        // Check if we have stored login credentials
        var storedEmail = sessionStorage.getItem('loginEmail');
        var storedPassword = sessionStorage.getItem('loginPassword');
        var storedRemember = sessionStorage.getItem('loginRemember');

        if (storedEmail && storedPassword) {
            // Auto-fill the current password
            $("#current-password").val(storedPassword);
            
            // Clear stored credentials
            sessionStorage.removeItem('loginEmail');
            sessionStorage.removeItem('loginPassword');
            sessionStorage.removeItem('loginRemember');
        }

        // Form submission
        $("#forceChangeForm").on('submit', function(e) {
            e.preventDefault();
            
            // Validate passwords match
            var newPassword = $("#new-password").val();
            var confirmPassword = $("#password-confirm").val();
            
            if (newPassword !== confirmPassword) {
                alert("New passwords do not match!");
                return;
            }

            // Submit the form
            this.submit();
        });
    });
</script>
@endsection