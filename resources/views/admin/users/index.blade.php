@extends('admin.layouts.master')

@section('title', 'User Management')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Statistics Widgets -->
        <div class="row mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-3">Total {{ ucfirst($category) }}</h6>
                                <h2 class="mb-0">{{ $stats['total'] }}</h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-primary rounded fs-3">
                                    <i class="ri-user-line text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-3">Active {{ ucfirst($category) }}</h6>
                                <h2 class="mb-0">{{ $stats['active'] }}</h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-success rounded fs-3">
                                    <i class="ri-user-follow-line text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-3">Inactive {{ ucfirst($category) }}</h6>
                                <h2 class="mb-0">{{ $stats['inactive'] }}</h2>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-warning rounded fs-3">
                                    <i class="ri-user-unfollow-line text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-3">Roles Distribution</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($stats['byRole'] as $role => $count)
                                        <span class="badge bg-primary">{{ $role }}: {{ $count }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-soft-info rounded fs-3">
                                    <i class="ri-shield-user-line text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users List Card -->
        <div class="card">
            
            <div class="card-body">
                <!-- Custom Tabs -->
                <ul class="nav nav-tabs-custom " role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $category === 'all' ? 'active' : '' }}" 
                           href="{{ route('admin.users.index', ['category' => 'all']) }}" 
                           role="tab">
                            <i class="ri-group-line align-bottom me-1"></i>
                                All Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $category === 'admins' ? 'active' : '' }}" 
                           href="{{ route('admin.users.index', ['category' => 'admins']) }}" 
                           role="tab">
                           <i class="ri-shield-user-line align-bottom me-1"></i>
                            Admins
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $category === 'users' ? 'active' : '' }}" 
                           href="{{ route('admin.users.index', ['category' => 'users']) }}" 
                           role="tab">
                           <i class="ri-user-3-line align-bottom me-1"></i>
                             Users
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content p-3 text-muted">
                    <div class="tab-pane active" id="all" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table  table-striped align-middle table-nowrap mb-0">
                                <thead class="text-muted table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Profile Type</th>
                                        <th scope="col">Membership Status</th>
                                        <th scope="col">Roles</th>
                                        <th scope="col">Created At</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($user->getFirstMediaUrl('profile_photos'))
                                                        <img src="{{ $user->getFirstMediaUrl('profile_photos') }}" 
                                                             class="rounded-circle avatar-xs me-2" 
                                                             alt="{{ $user->name }}">
                                                    @else
                                                        <div class="avatar-xs me-2">
                                                            <span class="avatar-title rounded-circle bg-primary">
                                                                {{ substr($user->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <span>{{ $user->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->profile && $user->profile->profileType)
                                                    
                                                        {{ $user->profile->profileType->name }}
                                                    
                                                @else
                                                    <span class="text-muted">Not Set</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->membership)
                                                    <span class="badge rounded-pill bg-{{ $user->membership->status === 'active' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($user->membership->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">No Membership</span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($user->roles as $role)
                                                    <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.users.show', $user) }}" 
                                                       class="btn btn-sm btn-info">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.show', $user) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <form action="{{ route('admin.users.destroy', $user) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-end mt-3">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-xs {
        width: 2rem;
        height: 2rem;
        line-height: 2rem;
        font-size: 0.75rem;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@push('scripts')
<script>
    // Add any JavaScript for dynamic updates here
    document.addEventListener('DOMContentLoaded', function() {
        // You can add AJAX calls here to update the statistics when tabs change
        const tabs = document.querySelectorAll('.nav-tabs .nav-link');
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                // The page will reload with new data, so no need for AJAX here
            });
        });
    });
</script>
@endpush 