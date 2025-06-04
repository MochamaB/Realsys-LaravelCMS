{{-- 
    Dynamic Navigation Component
    This component can be included in any part of your theme layout
    Usage: <x-theme-navigation location="header" :page-id="$page->id" :template-id="$template->id" />
--}}

@if(isset($menu) && $menu->rootItems->isNotEmpty())
    <nav class="navbar navbar-expand-lg navbar-light" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto py-4 py-lg-0">
                    @foreach($menu->rootItems as $item)
                        <li class="nav-item {{ $item->children && $item->children->isNotEmpty() ? 'dropdown' : '' }}">
                            <a class="nav-link px-lg-3 py-3 py-lg-4 {{ $item->is_current ? 'active' : '' }} {{ $item->has_active_child ? 'parent-active' : '' }}" 
                               href="{{ $item->full_url }}"
                               @if($item->target) target="{{ $item->target }}" @endif
                               @if($item->children && $item->children->isNotEmpty()) data-bs-toggle="dropdown" aria-expanded="false" @endif
                               @if(isset($item->scrollTo) && $item->scrollTo) 
                                   data-scroll-to="{{ $item->dataAttributes['data-scroll-to'] }}"
                                   data-offset="{{ $item->dataAttributes['data-offset'] }}"
                               @endif>
                                {{ $item->label }}
                            </a>
                            
                            @if($item->children && $item->children->isNotEmpty())
                                <ul class="dropdown-menu">
                                    @foreach($item->children as $child)
                                        <li>
                                            <a class="dropdown-item {{ $child->is_current ? 'active' : '' }}" 
                                               href="{{ $child->full_url }}"
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
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>
@endif
