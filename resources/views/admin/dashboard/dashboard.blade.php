@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('css')
    <!-- jsvectormap css -->
    <link href="{{ asset('assets/admin/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('assets/admin/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <div class="h-100">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">Welcome back, {{ $admin ? $admin->first_name : 'Admin' }}!</h4>
                                <p class="text-muted mb-0">Here's what's happening with your website today.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Pages</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="{{ $stats['pages']['total'] }}">0</span></h4>
                                        <div class="mb-2">
                                            <span class="badge bg-success me-1">{{ $stats['pages']['published'] }} Published</span>
                                            <span class="badge bg-warning">{{ $stats['pages']['draft'] }} Draft</span>
                                        </div>
                                        <a href="{{ route('admin.pages.index') }}" class="text-decoration-underline">View all pages</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-primary rounded fs-3">
                                            <i class="bx bx-copy-alt text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Widgets</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="{{ $stats['widgets']['total'] }}">0</span></h4>
                                        <div class="mb-2">
                                            <span class="badge bg-success"> Published</span>
                                        </div>
                                        <a href="{{ route('admin.widgets.index') }}" class="text-decoration-underline">View all widgets</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-info rounded fs-3">
                                            <i class="bx bx-cube text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Users</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="{{ $stats['users']['total'] }}">0</span></h4>
                                        <div class="mb-2">
                                            <span class="badge bg-success">{{ $stats['users']['active'] }} Active</span>
                                        </div>
                                        <a href="{{ route('admin.users.index') }}" class="text-decoration-underline">View all users</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-soft-warning rounded fs-3">
                                            <i class="bx bx-user-circle text-warning"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Recent Pages</h4>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.pages.index') }}" class="btn btn-soft-info btn-sm">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-borderless table-hover table-nowrap align-middle mb-0">
                                        <thead class="table-light">
                                            <tr class="text-muted">
                                                <th scope="col">Title</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['recent_pages'] as $page)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-body fw-medium">
                                                            {{ $page->title }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-soft-{{ $page->status === 'published' ? 'success' : 'warning' }} fs-11">
                                                            {{ ucfirst($page->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $page->created_at->diffForHumans() }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Recent Users</h4>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-soft-info btn-sm">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-borderless table-hover table-nowrap align-middle mb-0">
                                        <thead class="table-light">
                                            <tr class="text-muted">
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stats['recent_users'] as $user)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">{{ $user->first_name }} {{ $user->last_name }}</div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->created_at->diffForHumans() }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- apexcharts -->
    <script src="{{ asset('assets/admin/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/admin/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('assets/admin/js/pages/dashboard-analytics.init.js') }}"></script>
@endsection
