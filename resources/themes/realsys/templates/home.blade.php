@extends('theme::layouts.theme')

@section('content')
    @hassection('hero')
        @section('hero')
    @endhassection

    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5">
            <div class="col-md-8">
                @hassection('content')
                    @section('content')
                @endhassection
                
                @hassection('posts')
                    @section('posts')
                @endhassection
            </div>
            
            @hassection('sidebar')
                <div class="col-md-4">
                    @section('sidebar')
                </div>
            @endhassection
        </div>
    </div>
@endsection
