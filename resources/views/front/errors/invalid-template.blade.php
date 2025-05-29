@extends('layouts.error')

@section('title', 'Invalid Template')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Error: Invalid Template</div>

                <div class="card-body">
                    <div class="alert alert-warning">
                        <p>{{ $message ?? 'The page template is invalid or belongs to an inactive theme.' }}</p>
                        <p>Please update the page to use a template from the active theme.</p>
                    </div>
                    
                    @auth('admin')
                        <div class="mt-4">
                            @if(isset($page))
                                <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-primary">
                                    Edit This Page
                                </a>
                            @else
                                <a href="{{ route('admin.pages.index') }}" class="btn btn-primary">
                                    Manage Pages
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
