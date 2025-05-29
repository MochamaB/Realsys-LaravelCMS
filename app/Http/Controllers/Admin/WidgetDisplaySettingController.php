<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\WidgetDisplaySetting;
use Illuminate\Http\Request;

class WidgetDisplaySettingController extends Controller
{
    /**
     * Display a listing of the display settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $displaySettings = WidgetDisplaySetting::orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.widget_display_settings.index', compact('displaySettings'));
    }

    /**
     * Show the form for creating a new display setting.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $layoutOptions = $this->getLayoutOptions();
        $viewModeOptions = $this->getViewModeOptions();
        $paginationOptions = $this->getPaginationOptions();
        
        return view('admin.widget_display_settings.create', compact(
            'layoutOptions',
            'viewModeOptions',
            'paginationOptions'
        ));
    }

    /**
     * Store a newly created display setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'layout' => 'nullable|string',
            'view_mode' => 'nullable|string',
            'pagination_type' => 'nullable|string',
            'items_per_page' => 'nullable|integer|min:1',
            'empty_text' => 'nullable|string|max:255',
        ]);
        
        $displaySetting = WidgetDisplaySetting::create($validated);
        
        return redirect()
            ->route('admin.widget-display-settings.edit', $displaySetting)
            ->with('success', 'Display settings created successfully.');
    }

    /**
     * Display the specified display setting.
     *
     * @param  \App\Models\WidgetDisplaySetting  $displaySetting
     * @return \Illuminate\Http\Response
     */
    public function show(WidgetDisplaySetting $displaySetting)
    {
        $displaySetting->load('widgets');
        
        return view('admin.widget_display_settings.show', compact('displaySetting'));
    }

    /**
     * Show the form for editing the specified display setting.
     *
     * @param  \App\Models\WidgetDisplaySetting  $displaySetting
     * @return \Illuminate\Http\Response
     */
    public function edit(WidgetDisplaySetting $displaySetting)
    {
        $layoutOptions = $this->getLayoutOptions();
        $viewModeOptions = $this->getViewModeOptions();
        $paginationOptions = $this->getPaginationOptions();
        
        // Get widgets using these display settings
        $widgets = Widget::where('display_settings_id', $displaySetting->id)
            ->get();
        
        return view('admin.widget_display_settings.edit', compact(
            'displaySetting',
            'layoutOptions',
            'viewModeOptions',
            'paginationOptions',
            'widgets'
        ));
    }

    /**
     * Update the specified display setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetDisplaySetting  $displaySetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WidgetDisplaySetting $displaySetting)
    {
        $validated = $request->validate([
            'layout' => 'nullable|string',
            'view_mode' => 'nullable|string',
            'pagination_type' => 'nullable|string',
            'items_per_page' => 'nullable|integer|min:1',
            'empty_text' => 'nullable|string|max:255',
        ]);
        
        $displaySetting->update($validated);
        
        return redirect()
            ->route('admin.widget-display-settings.edit', $displaySetting)
            ->with('success', 'Display settings updated successfully.');
    }

    /**
     * Remove the specified display setting from storage.
     *
     * @param  \App\Models\WidgetDisplaySetting  $displaySetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(WidgetDisplaySetting $displaySetting)
    {
        // Check if any widgets are using these display settings
        $widgetsUsingSettings = Widget::where('display_settings_id', $displaySetting->id)->count();
        
        if ($widgetsUsingSettings > 0) {
            return redirect()
                ->route('admin.widget-display-settings.index')
                ->with('error', "Cannot delete display settings because they're being used by {$widgetsUsingSettings} widgets.");
        }
        
        $displaySetting->delete();
        
        return redirect()
            ->route('admin.widget-display-settings.index')
            ->with('success', 'Display settings deleted successfully.');
    }
    
    /**
     * Preview the display settings.
     *
     * @param  \App\Models\WidgetDisplaySetting  $displaySetting
     * @return \Illuminate\Http\Response
     */
    public function preview(WidgetDisplaySetting $displaySetting)
    {
        // Create sample content items for preview
        $sampleItems = $this->getSampleContentItems();
        
        return view('admin.widget_display_settings.preview', compact('displaySetting', 'sampleItems'));
    }
    
    /**
     * Get available layout options.
     *
     * @return array
     */
    private function getLayoutOptions()
    {
        return [
            'grid' => 'Grid Layout',
            'list' => 'List Layout',
            'card' => 'Card Layout',
            'slider' => 'Slider/Carousel',
            'table' => 'Table Layout',
            'masonry' => 'Masonry Grid'
        ];
    }
    
    /**
     * Get available view mode options.
     *
     * @return array
     */
    private function getViewModeOptions()
    {
        return [
            'full' => 'Full Content',
            'teaser' => 'Teaser/Summary',
            'compact' => 'Compact (Title Only)',
            'featured' => 'Featured (Large First Item)',
            'custom' => 'Custom Template'
        ];
    }
    
    /**
     * Get available pagination options.
     *
     * @return array
     */
    private function getPaginationOptions()
    {
        return [
            'none' => 'No Pagination',
            'simple' => 'Simple (Previous/Next)',
            'numbered' => 'Numbered Pages',
            'load_more' => 'Load More Button',
            'infinite' => 'Infinite Scroll'
        ];
    }
    
    /**
     * Get sample content items for preview.
     *
     * @return array
     */
    private function getSampleContentItems()
    {
        // Create a collection of sample items for display testing
        return collect([
            [
                'id' => 1,
                'title' => 'Sample Article 1',
                'content' => 'This is sample content for the first article. It demonstrates how content would appear in the selected layout and view mode.',
                'image' => 'sample1.jpg',
                'date' => now()->subDays(2)
            ],
            [
                'id' => 2,
                'title' => 'Sample Article 2',
                'content' => 'Another sample article with different content. This helps visualize how multiple items will appear when rendered together.',
                'image' => 'sample2.jpg',
                'date' => now()->subDays(4)
            ],
            [
                'id' => 3,
                'title' => 'Sample Article 3',
                'content' => 'A third sample article to demonstrate pagination and multi-item layouts. Having multiple items is essential for testing grid and list views.',
                'image' => 'sample3.jpg',
                'date' => now()->subDays(7)
            ],
            [
                'id' => 4,
                'title' => 'Sample Article 4',
                'content' => 'The fourth sample item for testing layouts and display settings. This will help ensure that the design works with various content lengths.',
                'image' => 'sample4.jpg',
                'date' => now()->subDays(10)
            ],
        ]);
    }
}
