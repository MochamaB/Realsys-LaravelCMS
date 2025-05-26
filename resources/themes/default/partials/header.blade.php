<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    @foreach($navigation->items as $item)
                        <li class="nav-item">
                            <a class="nav-link {{ $item->isActive ? 'active' : '' }}" 
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
</header>
