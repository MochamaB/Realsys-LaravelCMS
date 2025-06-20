@extends('layouts.user')

@section('title', 'Dashboard')

@section('content')


<div class="row">
<div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">Welcome back, {{ auth()->user()->name }}!</h4>
                                <p class="text-muted mb-0">Here's what's happening with your website today.</p>
                            </div>
                        </div>
                    </div>
                </div>
    <div class="col-xl-4">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Membership Status</span>
                        <h4 class="mb-3">
                            @if(auth()->user()->membership)
                                <span class="badge bg-{{ auth()->user()->membership->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst(auth()->user()->membership->status) }}
                                </span>
                            @else
                                <span class="badge bg-danger">No Membership</span>
                            @endif
                        </h4>
                        <div class="text-nowrap">
                            <span class="badge bg-soft-success text-success">{{ auth()->user()->profile->profileType->name ?? 'Member' }}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                <i class="ri-group-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Profile Completion</span>
                        <h4 class="mb-3">
                            @php
                                $profile = auth()->user()->profile;
                                $totalFields = 8; // Adjust based on your profile fields
                                $filledFields = 0;
                                if($profile) {
                                    $filledFields = count(array_filter([
                                        $profile->id_passport_number,
                                        $profile->mobile_number,
                                        $profile->gender,
                                        $profile->date_of_birth,
                                        $profile->postal_address,
                                        $profile->county_id,
                                        $profile->constituency_id,
                                        $profile->ward_id
                                    ]));
                                }
                                $completion = ($filledFields / $totalFields) * 100;
                            @endphp
                            {{ number_format($completion, 0) }}%
                        </h4>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completion }}%" aria-valuenow="{{ $completion }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                <i class="ri-user-settings-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Your Roles</span>
                        <h4 class="mb-3">
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                            @endforeach
                        </h4>
                        <div class="text-nowrap">
                            <span class="badge bg-soft-info text-info">Member since {{ auth()->user()->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary rounded-circle fs-3">
                                <i class="ri-shield-user-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Recent Activities</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Activity</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Profile Update</td>
                                <td>{{ auth()->user()->updated_at->format('M d, Y') }}</td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>Membership Registration</td>
                                <td>{{ auth()->user()->created_at->format('M d, Y') }}</td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 