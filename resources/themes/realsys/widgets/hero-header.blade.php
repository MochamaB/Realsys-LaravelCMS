<!-- Page Header-->
<header class="masthead" style="background-image: url('{{ $widget->background }}')">
    <div class="container position-relative px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                <div class="site-heading">
                    <h1>{{ $widget->title }}</h1>
                    @if($widget->subtitle)
                        <span class="subheading">{{ $widget->subtitle }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</header>
