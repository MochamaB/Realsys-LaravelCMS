@extends('layouts.error')

@section('title', 'No Active Theme')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Error: No Active Theme</div>

                <div class="card-body">
                    <div class="alert alert-danger">
                        <p>There is no active theme configured for this site.</p>
                        <p>Please activate a theme in the admin panel.</p>
                    </div>
                    
                    @auth('admin')
                        <div class="mt-4">
                            <a href="{{ route('admin.themes.index') }}" class="btn btn-primary">
                                Go to Theme Management
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
