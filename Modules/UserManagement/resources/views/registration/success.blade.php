<x-usermanagement::layouts.master>
<x-slot name="title">Registration Successful - {{ config('app.name') }}</x-slot>

<x-slot name="styles">
<style>
    .card {
        border-radius: 10px;
    }
    .card-header {
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
</style>
</x-slot>
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">Registration Successful!</h3>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h4 class="mb-3">Thank you for joining NPK Party</h4>
                    
                    @if(session('membership'))
                        <div class="alert alert-info mb-4 mx-auto" style="max-width: 400px;">
                            <p class="mb-1"><strong>Your Membership Number:</strong></p>
                            <h3 class="mb-0">{{ session('membership') }}</h3>
                        </div>
                    @endif
                    
                    <p class="mb-4">Your registration has been received and is being processed. You will receive a confirmation email shortly.</p>
                    
                    <div class="alert alert-warning mb-4 mx-auto" style="max-width: 450px;">
                        <p class="mb-0"><strong>Next Step:</strong> Please complete your membership fee payment to activate your membership.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-outline-primary me-2">Return to Homepage</a>
                        <a href="#" class="btn btn-primary">Make Payment</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-usermanagement::layouts.master>
