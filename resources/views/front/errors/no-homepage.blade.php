@extends('layouts.error')

@section('title', 'No Homepage Found')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Error: No Homepage Found</div>

                <div class="card-body">
                    <div class="alert alert-warning">
                        <p>There is no homepage configured for this site.</p>
                        <p>Please create and publish a page and mark it as homepage in the admin panel.</p>
                    </div>
                    
                    @auth('admin')
                        <div class="mt-4">
                            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
                                Create a Page
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
