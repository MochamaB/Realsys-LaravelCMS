
<nav>
    <ul>
        @if(isset($menu) && $menu->rootItems->isNotEmpty())
            @foreach($menu->rootItems as $item)
                @if($item->children->count() > 0)
                    <li>
                        <a href="{{ $item->full_url }}"
                            @if($item->target) target="{{ $item->target }}" @endif
                            @if($item->children && $item->children->isNotEmpty()) data-bs-toggle="dropdown" aria-expanded="false" @endif
                            @if(isset($item->scrollTo) && $item->scrollTo) 
                                data-scroll-to="{{ $item->dataAttributes['data-scroll-to'] }}"
                                data-offset="{{ $item->dataAttributes['data-offset'] }}"
                            @endif>
                            {{ $item->label }}
                        </a>
                        @if($item->children && $item->children->isNotEmpty())
                            <ul class="submenu-mainmenu">
                                @foreach($item->children as $child)
                                    <li>
                                        <a href="{{ $child->full_url }}"
                                            @if($child->target) target="{{ $child->target }}" @endif
                                            @if(isset($child->scrollTo) && $child->scrollTo) 
                                                data-scroll-to="{{ $child->dataAttributes['data-scroll-to'] }}"
                                                data-offset="{{ $child->dataAttributes['data-offset'] }}"
                                            @endif>
                                            {{ $child->label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @else
                    <li>
                        <a href="{{ $item->full_url }}">{{ $item->label }}</a>
                    </li>
                @endif
            @endforeach
        @else
            <!-- Fallback static menu if no dynamic menu is available -->
            <li><a href="{{ route('home') }}">Home</a></li>
            <li>
                <a href="#">Menu 1</a>
                <ul class="submenu-mainmenu">
                    <li><a href="#">Sub Menu 1</a></li>
                    <li><a href="#">Sub Menu 2</a></li>
                </ul>
            </li>
            <li><a href="#leadership">Menu 2</a></li>
            <li><a href="#">Menu 3</a></li>
            <li><a href="#">Menu 4</a></li>
            <li><a href="#">Menu 5</a></li>
            <li><a href="#">Menu 6</a></li>
        @endif
    </ul>
</nav>