@extends('admin.layouts.master')

@section('title', 'User Profile')

@section('content')
<div class="row">
    <div class="col-xxl-4">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
               
                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                        <x-profile-photo-edit :user="$user" :size="'lg'" />
                        
                </div>
                    <h5 class="fs-16 mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->profile->profileType->name ?? 'Member' }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">User Information</h5>
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" scope="row">Email :</th>
                                <td class="text-muted">{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Status :</th>
                                <td class="text-muted">
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Membership :</th>
                                <td class="text-muted">
                                    <span class="badge bg-{{ $user->membership->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($user->membership->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Expiry Date :</th>
                                <td class="text-muted"> {{ $user->membership?->expiry_date?->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Roles :</th>
                                <td class="text-muted">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Joined :</th>
                                <td class="text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-8">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs-custom" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#personal-details" role="tab">
                            <i class="ri-user-2-line align-bottom me-1"></i> Personal Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#membership-payments" role="tab">
                            <i class="ri-money-dollar-circle-line align-bottom me-1"></i> Membership & Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#change-password" role="tab">
                            <i class="ri-lock-line align-bottom me-1"></i> Change Password
                        </a>
                    </li>
                </ul>
                <div class="tab-content p-4">
                    <div class="tab-pane active" id="personal-details" role="tabpanel">
                        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="" name="status">
                                            <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="profile_type_id" class="form-label">Profile Type</label>
                                        <select class="form-select" id="profile_type_id" name="profile_type_id">
                                            @foreach($profileTypes as $type)
                                                <option value="{{ $type->id }}" {{ $user->profile->profile_type_id === $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="membership_status" class="form-label">Membership Status</label>
                                        <select class="form-select" id="membership_status" name="membership_status">
                                            <option value="active" {{ $user->membership->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $user->membership->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="membership_expiry" class="form-label">Membership Expiry</label>
                                        <input type="date" class="form-control" id="membership_expiry" name="membership_expiry" 
                                               value="{{ $user->membership?->expiry_date?->format('Y-m-d') ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="roles" class="form-label">Roles</label>
                                        <select class="form-select" id="roles" name="roles[]" multiple>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $user->roles->contains($role->id) ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="membership-payments" role="tabpanel">
                        <form action="{{ route('admin.users.update-membership', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="membership_status" class="form-label">Membership Status</label>
                                        <select class="form-select" id="membership_status" name="membership_status">
                                            <option value="pending" {{ $user->membership?->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="active" {{ $user->membership?->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $user->membership?->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="expired" {{ $user->membership?->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="membership_expiry" class="form-label">Membership Expiry</label>
                                        <input type="date" class="form-control" id="membership_expiry" name="membership_expiry" 
                                               value="{{ $user->membership?->expiry_date?->format('Y-m-d') ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="payment_status" class="form-label">Payment Status</label>
                                        <select class="form-select" id="payment_status" name="payment_status">
                                            <option value="pending" {{ $user->membership?->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="paid" {{ $user->membership?->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="failed" {{ $user->membership?->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                                            <option value="refunded" {{ $user->membership?->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="payment_amount" class="form-label">Payment Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" class="form-control" id="payment_amount" name="payment_amount" 
                                                   value="{{ $user->membership?->payment_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="payment_date" class="form-label">Payment Date</label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                               value="{{ $user->membership?->payment_date?->format('Y-m-d') ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method">
                                            <option value="">Select Method</option>
                                            <option value="credit_card" {{ $user->membership?->payment_method === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                            <option value="bank_transfer" {{ $user->membership?->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="paypal" {{ $user->membership?->payment_method === 'paypal' ? 'selected' : '' }}>PayPal</option>
                                            <option value="cash" {{ $user->membership?->payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="payment_notes" class="form-label">Payment Notes</label>
                                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3">{{ $user->membership?->payment_notes ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Update Membership & Payment</button>
                            </div>
                        </form>

                        <div class="mt-4">
                            <h5 class="card-title mb-4">Payment History</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->membership?->paymentHistory ?? [] as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                                <td>${{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ ucfirst($payment->method) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $payment->status === 'paid' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $payment->notes }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No payment history found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="change-password" role="tabpanel">
                        <form action="{{ route('admin.users.update-password', $user->id) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize select2 for roles
    $(document).ready(function() {
        $('#roles').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush 