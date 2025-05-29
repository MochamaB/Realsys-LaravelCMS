@extends('layouts.error')

@section('title', 'Page Error')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Error: Page Rendering Failed</div>

                <div class="card-body">
                    <div class="alert alert-danger">
                        <p>{{ $message ?? 'There was an error rendering this page.' }}</p>
                        <p>Please contact the site administrator if this problem persists.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
