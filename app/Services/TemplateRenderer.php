<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Template;
use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class TemplateRenderer
{
    /**
     * The theme manager instance
     *
     * @var ThemeManager
     */
    protected $themeManager;
    
    /**
     * The widget service instance
     *
     * @var WidgetService
     */
    protected $widgetService;
    
    /**
     * Create a new template renderer instance.
     *
     * @param ThemeManager $themeManager
     * @param WidgetService $widgetService
     * @return void
     */
    public function __construct(ThemeManager $themeManager, WidgetService $widgetService)
    {
        $this->themeManager = $themeManager;
        $this->widgetService = $widgetService;
    }
    
    /**
     * Render a page using its template
     *
     * @param Page $page
     * @param array $data Additional view data
     * @return string
     */
    public function renderPage(Page $page, array $data = []): string
    {
        $template = $page->template;
        
        if (!$template) {
            // Fallback to default template if no template is assigned
            $theme = $this->themeManager->getActiveTheme();
            $template = Template::where('theme_id', $theme->id)
                               ->where('is_default', true)
                               ->first();
            
            if (!$template) {
                return view('front.errors.no_template', ['page' => $page])->render();
            }
        }
        
        return $this->renderTemplate($template, $page, $data);
    }
    
    /**
     * Render a template
     *
     * @param Template $template
     * @param Page|null $page
     * @param array $data Additional view data
     * @return string
     */
    public function renderTemplate(Template $template, ?Page $page = null, array $data = []): string
    {
        // Get the template file path
        $templatePath = $this->getTemplatePath($template);
        
        if (!$templatePath) {
            return view('front.errors.template_not_found', ['template' => $template])->render();
        }
        
        // Prepare sections data
        $sections = [];
        
        if ($page) {
            // Load page sections with their widgets
            $pageSections = $page->sections()->with(['templateSection', 'widgets'])->get();
            
            foreach ($pageSections as $pageSection) {
                // Get widget data prepared for rendering
                $widgetData = $this->widgetService->getWidgetsForSection($pageSection->id);
                
                // Add section data with its widgets
                $sections[$pageSection->templateSection->slug] = [
                    'id' => $pageSection->id,
                    'name' => $pageSection->name,
                    'slug' => $pageSection->templateSection->slug,
                    'widgets' => $widgetData
                ];
            }
        }
        
        // Get active theme
        $activeTheme = $this->themeManager->getActiveTheme();
        
        // Prepare theme assets
        $themeAssets = [
            'slug' => $activeTheme->slug,
            'name' => $activeTheme->name,
            'css' => [
                theme_asset('css/styles.css'),
            ],
            'js' => [
                theme_asset('js/scripts.js'),
            ]
        ];
        
        // Prepare view data
        $viewData = array_merge([
            'page' => $page,
            'template' => $template,
            'sections' => $sections,
            'theme' => (object) $themeAssets,
        ], $data);
        
        // Don't render immediately - let Laravel handle the view hierarchy
        return view($templatePath, $viewData)->__toString();
    }
    
    /**
     * Get the template file path
     *
     * @param Template $template
     * @return string|null
     */
    protected function getTemplatePath(Template $template): ?string 
{
    $theme = $template->theme;
    
    if (!$theme) {
        return null;
    }
    
    // Remove 'templates/' prefix from file_path if it exists
    $cleanFilePath = $template->file_path;
    if (strpos($cleanFilePath, 'templates/') === 0) {
        $cleanFilePath = substr($cleanFilePath, strlen('templates/'));
    }
    
    // Correct path to look in views/themes directory
    $templateFilePath = resource_path('themes/' . $theme->slug . '/templates/' . $cleanFilePath);
    /*
    dump([
        'Theme Slug' => $theme->slug,
        'Original Template File' => $template->file_path,
        'Clean Template File' => $cleanFilePath,
        'Looking For File At' => $templateFilePath,
        'File Exists' => File::exists($templateFilePath),
        'View Path' => 'theme::templates.' . str_replace('.blade.php', '', $cleanFilePath)
    ]);
    */
    
    // Check if file exists physically
    if (!File::exists($templateFilePath)) {
        return null;
    }
    
    // Convert file path to view namespace
    $viewPath = 'theme::templates.' . str_replace('.blade.php', '', $cleanFilePath);
    
    return $viewPath;
}
    
    /**
     * Render a section
     *
     * @param string $sectionSlug The section slug
     * @param array $data Additional view data
     * @return string
     */
    public function renderSection(string $sectionSlug, array $data = []): string
    {
        // Get current page and template from view data
        $page = $data['page'] ?? null;
        $template = $data['template'] ?? null;
        
        if (!$page || !$template) {
            return '';
        }
        
        // Find the section
        $pageSection = $page->sections()
            ->whereHas('templateSection', function($query) use ($sectionSlug) {
                $query->where('slug', $sectionSlug);
            })
            ->with(['templateSection', 'widgets'])
            ->first();
        
        if (!$pageSection) {
            return '';
        }
        
        // Get widgets data from WidgetService to ensure consistent format
        $widgetData = $this->widgetService->getWidgetsForSection($pageSection->id);
        
        // Debug the widget data for this section
        \Log::debug('Section widget data in renderer', [
            'section_id' => $pageSection->id,
            'section_name' => $pageSection->name,
            'section_slug' => $pageSection->templateSection->slug,
            'widget_count' => is_array($widgetData) ? count($widgetData) : 0,
            'widget_data_type' => gettype($widgetData),
            'widget_sample' => !empty($widgetData) ? json_encode(array_slice($widgetData, 0, 1)) : 'empty'
        ]);
        
        // Prepare view data
        $sectionData = array_merge([
            'pageSection' => $pageSection,
            'section' => $pageSection->templateSection,
            'widgets' => $widgetData,
        ], $data);
        
        // Prepare any additional widget-specific data if needed
        // Note: This should now be handled by the WidgetService
        // and included in the widget data array already
        
        // Legacy fallback for specific widgets if needed
        foreach ($widgetData as $widget) {
            // Most data should be part of the widget content already
            // This is just for any legacy compatibility if needed
        }
        
        // Render the section
        $theme = $template->theme;
        
        // Log section details for debugging
        \Log::debug('Section template resolution', [
            'section_id' => $pageSection->id,
            'section_name' => $pageSection->name,
            'template_section_id' => $pageSection->templateSection->id,
            'template_section_slug' => $pageSection->templateSection->slug,
            'template_section_type' => $pageSection->templateSection->type
        ]);
        
        // Try to resolve section view using slug first (which should match the file names)
        $sectionSlugView = 'theme::sections.' . $pageSection->templateSection->slug;
        $sectionTypeView = 'theme::sections.' . $pageSection->templateSection->type;
        
        // Log view paths we're trying to resolve
        \Log::debug('Section view paths', [
            'section_slug_view' => $sectionSlugView,
            'section_type_view' => $sectionTypeView,
            'slug_view_exists' => View::exists($sectionSlugView) ? 'yes' : 'no',
            'type_view_exists' => View::exists($sectionTypeView) ? 'yes' : 'no',
        ]);
        
        // First try the slug-based view (matching our file names)
        if (View::exists($sectionSlugView)) {
            $sectionView = $sectionSlugView;
        }
        // Then try the type-based view
        else if (View::exists($sectionTypeView)) {
            $sectionView = $sectionTypeView;
        }
        // Finally fall back to default
        else {
            $sectionView = 'theme::sections.default';
            
            // If theme doesn't provide a default section view, use system default
            if (!View::exists($sectionView)) {
                $sectionView = 'front.sections.default';
            }
            
            \Log::warning('Falling back to default section template', [
                'section_id' => $pageSection->id,
                'section_slug' => $pageSection->templateSection->slug,
                'section_type' => $pageSection->templateSection->type
            ]);
        }
        
        return view($sectionView, $sectionData)->render();
    }
    
    /**
     * Check if a section exists in the current page
     *
     * @param string $sectionSlug The section slug
     * @param array $data View data
     * @return bool
     */
    public function sectionExists(string $sectionSlug, array $data = []): bool
    {
        $page = $data['page'] ?? null;
        
        if (!$page) {
            return false;
        }
        
        return $page->sections()
            ->whereHas('templateSection', function($query) use ($sectionSlug) {
                $query->where('slug', $sectionSlug);
            })
            ->exists();
    }
}
