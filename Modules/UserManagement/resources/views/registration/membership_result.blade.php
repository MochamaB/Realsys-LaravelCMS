<x-usermanagement::layouts.master>
<x-slot name="title">Verification Successful - {{ config('app.name') }}</x-slot>

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
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Membership Verification Result</div>

                <div class="card-body">
                    @if($member_found)
                        <div class="alert alert-success">
                            <h4>Member Found!</h4>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th>ID Number</th>
                                    <td>{{ $id_number }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $name }}</td>
                                </tr>
                                <tr>
                                    <th>Membership ID</th>
                                    <td>{{ $membership_id }}</td>
                                </tr>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <h4>No member found with the provided details</h4>
                            <p>ID/Phone: {{ $input['id_number'] ?? $input['phone_number'] }}</p>
                        </div>
                        <a href="{{ route('verify-membership') }}" class="btn btn-primary">Try Again</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-usermanagement::layouts.master>
