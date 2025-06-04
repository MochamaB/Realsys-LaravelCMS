{{-- This file is meant to be included in your theme's layout or template --}}
{{-- It doesn't define its own structure, allowing themes to control positioning --}}

{{-- Inject the MenuService --}}
@inject('menuService', 'App\Services\MenuService')

{{-- Get the header menu --}}
@php
    $headerMenu = $menuService->getProcessedMenu('header', 
        $page->id ?? null, 
        $template->id ?? null, 
        $isOnePage ?? false);
@endphp

{{-- Include navigation with the menu --}}
@include('theme::partials.navigation', ['menu' => $headerMenu])
