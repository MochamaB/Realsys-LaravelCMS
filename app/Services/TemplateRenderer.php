<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Template;
use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Services\MenuService;
use App\Services\UniversalStylingService;

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
            $pageSections = $page->sections()->with(['templateSection', 'widgets'])->orderBy('position')->get();
            
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
        // Get all active menus
        $menuService = app(MenuService::class);
        $menus = $menuService->getAllActiveMenus($page->id ?? null, $template->id ?? null);
        
        // Collect widget assets for the page
        $widgetAssets = $this->widgetService->collectPageWidgetAssets($sections);
        
        // Prepare view data
        $viewData = array_merge([
            'page' => $page,
            'template' => $template,
            'sections' => $sections,
            'theme' => $activeTheme,
            'menus' => $menus,
            'widgetAssets' => $widgetAssets,
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
    public function getTemplatePath(Template $template): ?string 
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
            \Log::warning('Missing page or template in renderSection', [
                'section_slug' => $sectionSlug,
                'has_page' => !is_null($page),
                'has_template' => !is_null($template)
            ]);
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
            \Log::warning('Page section not found', [
                'page_id' => $page->id,
                'section_slug' => $sectionSlug
            ]);
            return '';
        }
        
        // Get widgets data from WidgetService to ensure consistent format
        $widgetData = $this->widgetService->getWidgetsForSection($pageSection->id);
        
        // Debug the widget data for this section
        \Log::debug('Section widget data in renderer', [
            'section_id' => $pageSection->id,
            'section_name' => $pageSection->name ?? 'Unnamed',
            'section_slug' => $pageSection->templateSection->slug,
            'widget_count' => is_array($widgetData) ? count($widgetData) : 0,
            'widget_data_type' => gettype($widgetData)
        ]);
        
        // Get active theme
        $theme = $template->theme;
        if (!$theme) {
            \Log::error('No theme found for template', ['template_id' => $template->id]);
            return '<div class="alert alert-danger">Error: Template has no associated theme</div>';
        }
        
        // Verify the theme namespace is registered
        $this->ensureThemeNamespaceIsRegistered($theme);
        
        // Prepare view data
        $sectionData = array_merge([
            'pageSection' => $pageSection,
            'section' => $pageSection->templateSection,
            'widgets' => $widgetData,
            
            // NEW: Universal styling support
            'universalStyling' => app(UniversalStylingService::class)
        ], $data);
        
        // Try to resolve section view using slug first (which should match the file names)
        $sectionSlugView = 'theme::sections.' . $pageSection->templateSection->slug;
        $sectionTypeView = 'theme::sections.' . $pageSection->templateSection->section_type;
        $defaultView = 'theme::sections.default';
        $systemDefaultView = 'front.sections.default';
        
        // Check if physical files exist for better debugging
        $themeViewsPath = resource_path('themes/' . $theme->slug);
        $sectionSlugFilePath = $themeViewsPath . '/sections/' . $pageSection->templateSection->slug . '.blade.php';
        $sectionTypeFilePath = $themeViewsPath . '/sections/' . $pageSection->templateSection->section_type . '.blade.php';
        $defaultViewFilePath = $themeViewsPath . '/sections/default.blade.php';
        
        // Log view paths we're trying to resolve
        \Log::debug('Section view resolution paths', [
            'theme_slug' => $theme->slug,
            'section_slug_view' => $sectionSlugView,
            'section_type_view' => $sectionTypeView,
            'section_type' => $pageSection->templateSection->section_type,
            'default_view' => $defaultView,
            'system_default' => $systemDefaultView,
            'slug_view_exists' => View::exists($sectionSlugView) ? 'yes' : 'no',
            'type_view_exists' => View::exists($sectionTypeView) ? 'yes' : 'no',
            'default_view_exists' => View::exists($defaultView) ? 'yes' : 'no',
            'system_default_exists' => View::exists($systemDefaultView) ? 'yes' : 'no',
            'slug_file_exists' => file_exists($sectionSlugFilePath) ? 'yes' : 'no',
            'type_file_exists' => file_exists($sectionTypeFilePath) ? 'yes' : 'no',
            'default_file_exists' => file_exists($defaultViewFilePath) ? 'yes' : 'no',
        ]);
        
        // First try the slug-based view (matching our file names)
        if (View::exists($sectionSlugView)) {
            \Log::debug("Using section slug view: {$sectionSlugView}");
            $sectionView = $sectionSlugView;
        }
        // Then try the type-based view
        else if (View::exists($sectionTypeView)) {
            \Log::debug("Using section type view: {$sectionTypeView}");
            $sectionView = $sectionTypeView;
        }
        // Then try the theme default
        else if (View::exists($defaultView)) {
            \Log::debug("Using theme default view: {$defaultView}");
            $sectionView = $defaultView;
        }
        // Finally fall back to system default
        else {
            \Log::warning("Falling back to system default view: {$systemDefaultView}");
            $sectionView = $systemDefaultView;
        }
        
        try {
            return view($sectionView, $sectionData)->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering section view', [
                'section_id' => $pageSection->id,
                'view' => $sectionView,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return error message in development, simple message in production
            if (config('app.debug')) {
                return "<div class='alert alert-danger'>"
                      . "<strong>Error rendering section:</strong> {$e->getMessage()}"
                      . "<pre>{$e->getTraceAsString()}</pre>"
                      . "</div>";
            } else {
                return "<div class='alert alert-danger'>There was an error rendering this section.</div>";
            }
        }
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
    
    /**
     * Ensure theme namespace is properly registered
     * 
     * @param \App\Models\Theme $theme
     * @return void
     */
    public function ensureThemeNamespaceIsRegistered($theme): void
    {
        if (!$theme) {
            return;
        }
        
        // Use ThemeManager to properly register theme paths
        $this->themeManager->registerThemeViewPaths($theme);
        
        $themePath = resource_path('themes/' . $theme->slug);
        
        // Verify registration worked
        if (file_exists($themePath)) {
            \Log::debug("Theme namespace registration verified", [
                'theme_slug' => $theme->slug,
                'theme_path' => $themePath,
                'sections_available' => \View::exists('theme::sections.default'),
                'widgets_available' => \View::exists('theme::widgets.default.view')
            ]);
        } else {
            \Log::error("Theme path does not exist", [
                'theme_slug' => $theme->slug,
                'expected_path' => $themePath
            ]);
        }
    }
    
    /**
     * Render all sections for a page and template
     *
     * @param array $data View data containing page and template
     * @return string HTML output of all rendered sections
     */
    public function renderAllSections(array $data = []): string
    {
        $page = $data['page'] ?? null;
        $template = $data['template'] ?? null;
        
        if (!$page || !$template) {
            \Log::warning('Missing page or template in renderAllSections');
            return '';
        }
        
        $output = '<div class="page-sections-container">';
        
        // Load page sections with template sections
        $pageSections = $page->sections()
            ->with(['templateSection'])
            ->orderBy('position')
            ->get();
        
        // If no sections found, check if we need to sync from template
        if ($pageSections->isEmpty() && $template->sections->isNotEmpty()) {
            \Log::warning('No page sections found, template might not be synchronized', [
                'page_id' => $page->id,
                'template_id' => $template->id
            ]);
            $output .= '<div class="alert alert-warning">No sections found for this page.</div>';
        }
        
        \Log::debug('Rendering all sections', [
            'page_id' => $page->id,
            'page_sections_count' => $pageSections->count()
        ]);
        
        // Loop through PAGE sections (not template sections)
        foreach ($pageSections as $index => $pageSection) {
            $templateSection = $pageSection->templateSection;
            
            if (!$templateSection) {
                \Log::warning('PageSection has no TemplateSection reference', ['page_section_id' => $pageSection->id]);
                continue;
            }
            
            // Add debug information
            \Log::debug("Rendering section #{$index}", [
                'section_id' => $pageSection->id,
                'section_name' => $templateSection->name,
                'section_type' => $templateSection->section_type
            ]);
            
            $sectionClasses = 'section-' . $templateSection->section_type;
            
            if ($templateSection->section_type === 'multi-column' && $templateSection->column_layout) {
                $sectionClasses .= ' columns-' . $templateSection->column_layout;
            }
            
            // Add any custom classes from the PageSection
            if ($pageSection->css_classes) {
                $sectionClasses .= ' ' . $pageSection->css_classes;
            }
            
            // Create the section wrapper with appropriate classes
            $output .= "<div id='section-wrapper-{$pageSection->id}' class='section-wrapper {$sectionClasses} mb-5'>";
            
            try {
                // Pass the pageSection ID directly instead of looking up by slug
                $sectionOutput = $this->renderSectionById($pageSection->id, $data);
                $output .= $sectionOutput;
                
                // Add a debug comment at the end of each section to help with troubleshooting
                $output .= "<!-- End of section: {$templateSection->name} (ID: {$pageSection->id}) -->";
            } catch (\Exception $e) {
                \Log::error("Error rendering section {$pageSection->id}: {$e->getMessage()}");
                $output .= "<div class='alert alert-danger'>Error rendering section '{$templateSection->name}': {$e->getMessage()}</div>";
            }
            
            $output .= "</div>\n";
        }
        
        $output .= '</div>';
        
        // Log the final HTML output to help with debugging
        \Log::debug("Rendered {$pageSections->count()} sections for page {$page->id}");
        
        return $output;
    }
    
    /**
     * Render a section by its PageSection ID
     *
     * @param int $pageSectionId The PageSection ID
     * @param array $data Additional view data
     * @return string
     */
    public function renderSectionById(int $pageSectionId, array $data = []): string
    {
        $page = $data['page'] ?? null;
        $template = $data['template'] ?? null;
        
        if (!$page || !$template) {
            \Log::warning('Missing page or template in renderSectionById');
            return '';
        }
        
        // Find the section by ID
        $pageSection = $page->sections()
            ->with(['templateSection'])
            ->where('id', $pageSectionId)
            ->first();
        
        if (!$pageSection) {
            \Log::warning('PageSection not found by ID', ['page_section_id' => $pageSectionId]);
            return '';
        }
        
        // Get widgets data from WidgetService
        $widgetData = $this->widgetService->getWidgetsForSection($pageSection->id);
        
        // Debug the widget data for this section
        \Log::debug('Section widget data in renderSectionById', [
            'section_id' => $pageSection->id,
            'section_name' => $pageSection->templateSection->name ?? 'Unnamed',
            'section_slug' => $pageSection->templateSection->slug ?? 'unknown',
            'widget_count' => is_array($widgetData) ? count($widgetData) : 0
        ]);
        
        // Get active theme
        $theme = $template->theme;
        if (!$theme) {
            \Log::error('No theme found for template', ['template_id' => $template->id]);
            return '<div class="alert alert-danger">Error: Template has no associated theme</div>';
        }
        
        // Verify the theme namespace is registered
        $this->ensureThemeNamespaceIsRegistered($theme);
        
        // Prepare view data
        $sectionData = array_merge([
            'pageSection' => $pageSection,
            'section' => $pageSection->templateSection,
            'widgets' => $widgetData,
            
            // NEW: Universal styling support
            'universalStyling' => app(UniversalStylingService::class)
        ], $data);
        
        // Try to resolve section view using section slug first
        $templateSection = $pageSection->templateSection;
        if (!$templateSection) {
            \Log::error('PageSection has no TemplateSection', ['page_section_id' => $pageSection->id]);
            return '<div class="alert alert-danger">Error: Section has no template definition</div>';
        }
        
        // View resolution cascade
        $sectionSlugView = 'theme::sections.' . $templateSection->slug;
        $sectionTypeView = 'theme::sections.' . $templateSection->section_type;
        $defaultView = 'theme::sections.default';
        $systemDefaultView = 'front.sections.default';
        
        // Log view resolution attempts
        \Log::debug('Section view resolution in renderSectionById', [
            'section_id' => $pageSection->id,
            'slug_view' => $sectionSlugView,
            'slug_exists' => \View::exists($sectionSlugView) ? 'yes' : 'no',
            'type_view' => $sectionTypeView,
            'type_exists' => \View::exists($sectionTypeView) ? 'yes' : 'no',
            'default_exists' => \View::exists($defaultView) ? 'yes' : 'no'
        ]);
        
        // View resolution cascade
        if (\View::exists($sectionSlugView)) {
            $sectionView = $sectionSlugView;
            \Log::debug("Using section slug view: {$sectionSlugView}");
        } 
        else if (\View::exists($sectionTypeView)) {
            $sectionView = $sectionTypeView;
            \Log::debug("Using section type view: {$sectionTypeView}");
        } 
        else if (\View::exists($defaultView)) {
            $sectionView = $defaultView;
            \Log::debug("Using theme default view: {$defaultView}");
        }
        else {
            $sectionView = $systemDefaultView;
            \Log::warning("Falling back to system default view: {$systemDefaultView}");
        }
        
        try {
            return \view($sectionView, $sectionData)->render();
        } catch (\Exception $e) {
            \Log::error('Error rendering section view', [
                'section_id' => $pageSection->id,
                'view' => $sectionView,
                'error' => $e->getMessage()
            ]);
            
            // Return error message in development, simple message in production
            if (config('app.debug')) {
                return "<div class='alert alert-danger'>"
                      . "<strong>Error rendering section:</strong> {$e->getMessage()}"
                      . "<pre>{$e->getTraceAsString()}</pre>"
                      . "</div>";
            } else {
                return "<div class='alert alert-danger'>There was an error rendering this section.</div>";
            }
        }
    }
}
