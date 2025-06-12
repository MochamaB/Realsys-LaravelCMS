@extends('admin.layouts.master')

@section('title', 'User Management')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">User Management</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle table-nowrap mb-0">
                        <thead>
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
                            @forelse($users as $user)
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
                                        <span class="badge bg-info">
                                            {{ $user->profile->profileType->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->membership)
                                        <span class="badge bg-{{ $user->membership->status === 'active' ? 'success' : 'warning' }}">
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
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" 
                                              method="POST" 
                                              class="d-inline"
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
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $users->links() }}
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