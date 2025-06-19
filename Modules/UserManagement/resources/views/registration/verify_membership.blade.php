<x-usermanagement::layouts.master>
<x-slot name="title">Verify Membership - {{ config('app.name') }}</x-slot>

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
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h2 class="text-center">Verify Your Membership</h2>
        <p class="text-center text-muted mb-4">
            You can also confirm your membership status by dialing *509#<br>
            or visiting ORRP register: 
            <a class="text-danger" href="https://ippms.orpp.or.ke/auth/login?ReturnUrl=%2F" target="_blank">ORPP IPPMS</a>
        </p>
        <form action="{{ route('verify-membership.post') }}" method="post">
            @csrf
            <div class="form-group">
                <label for="id_number" class="form-label">ID Number</label>
                <input type="text" class="form-control" id="id_number" name="id_number" 
                    value="{{ old('id_number') }}" placeholder="Enter ID number">
            </div>
    
            <div class="form-group mt-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" 
                    value="{{ old('phone_number') }}" placeholder="Enter phone number">
                <small class="text-muted">Please provide at least one of the fields</small>
            </div>
            <div class="text-center mt-3">
                <button class="btn btn-primary"><i class="fa fa-check"></i> Verify</button>
            </div>
        </form>
    </div>
</div>
</x-usermanagement::layouts.master>
