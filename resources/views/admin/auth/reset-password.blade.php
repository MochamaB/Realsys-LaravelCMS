@extends('admin.layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="card mt-4">
            <div class="card-body p-4">
                <div class="text-center mt-2">
                    <h5 class="text-primary">Create New Password</h5>
                    <p class="text-muted">Your new password must be different from previous used password.</p>
                </div>

                <div class="p-2">
                    <form action="{{ route('admin.reset-password.post') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <div class="position-relative auth-pass-inputgroup">
                                <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" placeholder="Enter password" id="password" name="password" required autofocus>
                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button"><i class="ri-eye-fill align-middle"></i></button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password-confirm">Confirm Password</label>
                            <div class="position-relative auth-pass-inputgroup mb-3">
                                <input type="password" class="form-control pe-5" placeholder="Confirm password" id="password-confirm" name="password_confirmation" required>
                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button"><i class="ri-eye-fill align-middle"></i></button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-success w-100" type="submit">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <p class="mb-0">Wait, I remember my password... <a href="{{ route('admin.login') }}" class="fw-semibold text-primary text-decoration-underline">Click here</a></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Password show & hide
    $(document).ready(function() {
        $(".password-addon").click(function() {
            var input = $(this).siblings('input');
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                $(this).find("i").removeClass("ri-eye-fill").addClass("ri-eye-off-fill");
            } else {
                input.attr("type", "password");
                $(this).find("i").removeClass("ri-eye-off-fill").addClass("ri-eye-fill");
            }
        });
    });
</script>
@endpush