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
     * Create a new template renderer instance.
     *
     * @param ThemeManager $themeManager
     * @return void
     */
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
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
            $pageSections = $page->sections()->with('widgets.widgetType')->get();
            
            foreach ($pageSections as $pageSection) {
                $sections[$pageSection->templateSection->slug] = $pageSection;
            }
        }
        
        // Prepare view data
        $viewData = array_merge([
            'page' => $page,
            'template' => $template,
            'sections' => $sections,
        ], $data);
        
        // Render the template
        return view($templatePath, $viewData)->render();
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
        
        // Get the template file from theme's templates directory
        $templateFilePath = $theme->path . '/resources/views/templates/' . $template->file_path;
        
        // Check if file exists physically
        if (!File::exists($templateFilePath)) {
            return null;
        }
        
        // Convert file path to view namespace
        $viewPath = 'theme::templates.' . str_replace('.blade.php', '', $template->file_path);
        
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
            ->with(['templateSection', 'widgets.widgetType'])
            ->first();
        
        if (!$pageSection) {
            return '';
        }
        
        // Prepare view data
        $sectionData = array_merge([
            'pageSection' => $pageSection,
            'section' => $pageSection->templateSection,
            'widgets' => $pageSection->widgets,
        ], $data);
        
        // Render the section
        $theme = $template->theme;
        $sectionView = 'theme::sections.' . $pageSection->templateSection->type;
        
        // Check if section view exists, otherwise fall back to default
        if (!View::exists($sectionView)) {
            $sectionView = 'theme::sections.default';
            
            // If theme doesn't provide a default section view, use system default
            if (!View::exists($sectionView)) {
                $sectionView = 'front.sections.default';
            }
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
