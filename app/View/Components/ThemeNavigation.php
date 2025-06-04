<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\MenuService;

class ThemeNavigation extends Component
{
    /**
     * The location of the menu to display.
     *
     * @var string
     */
    public $location;
    
    /**
     * The menu object.
     *
     * @var mixed
     */
    public $menu;

    /**
     * Create a new component instance.
     *
     * @param  string  $location
     * @param  int|null  $pageId
     * @param  int|null  $templateId
     * @param  bool  $isOnePage
     * @param  bool  $useCache
     * @return void
     */
    public function __construct(
        string $location = 'header',
        ?int $pageId = null,
        ?int $templateId = null,
        bool $isOnePage = false,
        bool $useCache = true
    ) {
        $this->location = $location;
        $menuService = app(MenuService::class);
        $this->menu = $menuService->getProcessedMenu(
            $location,
            $pageId,
            $templateId,
            $isOnePage,
            $useCache
        );
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('theme::components.navigation');
    }
}
