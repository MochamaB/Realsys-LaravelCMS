<nav class="navbar navbar-expand-lg navbar-light" id="mainNav">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto py-4 py-lg-0">
                @foreach($navigation->items as $item)
                    <li class="nav-item">
                        <a class="nav-link px-lg-3 py-3 py-lg-4 {{ $item->isActive ? 'active' : '' }}" 
                           href="{{ $item->url }}"
                           @if($item->target) target="{{ $item->target }}" @endif>
                            {{ $item->label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
