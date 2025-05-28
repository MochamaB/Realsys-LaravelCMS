@extends('theme::layouts.theme')

@section('content')
    @hassection('hero')
        @section('hero')
    @endhassection

    <article class="mb-4">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-md-8">
                    @hassection('content')
                        @section('content')
                    @endhassection
                    
                    @hassection('author')
                        @section('author')
                    @endhassection
                    
                    @hassection('comments')
                        @section('comments')
                    @endhassection
                </div>
                
                @hassection('sidebar')
                    <div class="col-md-4">
                        @section('sidebar')
                    </div>
                @endhassection
            </div>
        </div>
    </article>
@endsection
