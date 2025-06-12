@extends('layouts.user')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">My Profile</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    @if(auth()->user()->getFirstMediaUrl('profile_photos'))
                        <img src="{{ auth()->user()->getFirstMediaUrl('profile_photos') }}" 
                             class="rounded-circle avatar-xl img-thumbnail" 
                             alt="Profile Photo">
                    @else
                        <div class="avatar-xl mx-auto">
                            <span class="avatar-title rounded-circle bg-primary">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <h5 class="mt-3 mb-1">{{ auth()->user()->name }}</h5>
                    <p class="text-muted">{{ auth()->user()->profile->profileType->name ?? 'Member' }}</p>

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="ri-edit-line align-bottom me-1"></i> Edit Profile
                        </button>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <h5 class="font-size-16 mb-3">Profile Information</h5>
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row">Full Name :</th>
                                    <td>{{ auth()->user()->name }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Email :</th>
                                    <td>{{ auth()->user()->email }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Phone :</th>
                                    <td>{{ auth()->user()->profile->mobile_number ?? 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Location :</th>
                                    <td>
                                        @if(auth()->user()->profile)
                                            {{ auth()->user()->profile->county->name ?? '' }}
                                            {{ auth()->user()->profile->constituency->name ? ', ' . auth()->user()->profile->constituency->name : '' }}
                                            {{ auth()->user()->profile->ward->name ? ', ' . auth()->user()->profile->ward->name : '' }}
                                        @else
                                            Not set
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Member Since :</th>
                                    <td>{{ auth()->user()->created_at->format('M d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Membership Details</h5>
                @if(auth()->user()->membership)
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row">Membership Number :</th>
                                    <td>{{ auth()->user()->membership->membership_number }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Status :</th>
                                    <td>
                                        <span class="badge bg-{{ auth()->user()->membership->status === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst(auth()->user()->membership->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Type :</th>
                                    <td>{{ ucfirst(auth()->user()->membership->membership_type) }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Start Date :</th>
                                    <td>{{ auth()->user()->membership->start_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">End Date :</th>
                                    <td>{{ auth()->user()->membership->end_date ? auth()->user()->membership->end_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Payment Status :</th>
                                    <td>
                                        <span class="badge bg-{{ auth()->user()->membership->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst(auth()->user()->membership->payment_status ?? 'unpaid') }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No membership record found. Please contact support for assistance.
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Roles & Permissions</h5>
                <div class="mb-4">
                    <h6 class="mb-3">Your Roles</h6>
                    <div>
                        @foreach(auth()->user()->roles as $role)
                            <span class="badge bg-primary me-1">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h6 class="mb-3">Key Permissions</h6>
                    <div>
                        @foreach(auth()->user()->getAllPermissions() as $permission)
                            <span class="badge bg-soft-info text-info me-1">{{ $permission->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 