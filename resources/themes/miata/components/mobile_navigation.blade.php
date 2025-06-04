<nav id="dropdown">
    <ul>
        @if(isset($mainMenu) && $mainMenu)
            @foreach($mainMenu->topLevelItems as $item)
                @if($item->children->count() > 0)
                    <li>
                        <a href="{{ $item->getFullUrl() }}">{{ $item->title }}</a>
                        <ul class="sub-menu">
                            @foreach($item->children as $child)
                                <li><a href="{{ $child->getFullUrl() }}">{{ $child->title }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li><a href="{{ $item->getFullUrl() }}">{{ $item->title }}</a></li>
                @endif
            @endforeach
        @else
            <!-- Fallback static menu if no dynamic menu is available -->
            <li><a href="{{ route('home') }}">Home</a></li>
            <li>
                <a href="#">Menu 1</a>
                <ul class="sub-menu">
                    <li><a href="#">Sub Menu 1 </a></li>
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