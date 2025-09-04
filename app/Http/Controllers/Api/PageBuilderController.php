<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use App\Models\ContentType;
use App\Models\TemplateSection;
use App\Services\TemplateRenderer;
use App\Services\PageSectionManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Page Builder API Controller - ARCHIVED VERSION
 * 
 * ALL METHODS DISABLED DURING FRESH REBUILD
 * Original implementation archived to: Implementation/Archive/PageBuilder_v1_archived/
 * 
 * Fresh rebuild starting from scratch - September 2, 2025
 * Follow rebuild plan: Implementation/PageBuilder_Fresh_Rebuild_Plan.md
 */
class PageBuilderController extends Controller
{
    protected $templateRenderer;
    protected $pageSectionManager;

    public function __construct(TemplateRenderer $templateRenderer, PageSectionManager $pageSectionManager)
    {
        $this->templateRenderer = $templateRenderer;
        $this->pageSectionManager = $pageSectionManager;
    }

    /**
     * DISABLED - All methods disabled during fresh rebuild
     * Check Implementation/Archive/PageBuilder_v1_archived/controllers/ for original methods
     */
    private function disabled()
    {
        return response()->json([
            'success' => false,
            'message' => 'Page Builder API temporarily disabled during fresh rebuild',
            'archive_location' => 'Implementation/Archive/PageBuilder_v1_archived/',
            'rebuild_plan' => 'Implementation/PageBuilder_Fresh_Rebuild_Plan.md'
        ], 503);
    }

    // ALL API METHODS DISABLED - Return disabled() response
    public function getRenderedPage(Page $page): JsonResponse { return $this->disabled(); }
    public function getRenderedPageIframe(Page $page) { 
        return response('Page Builder temporarily disabled. Check Implementation/PageBuilder_Fresh_Rebuild_Plan.md')
            ->header('Content-Type', 'text/html');
    }
    public function getPageStructure() { return $this->disabled(); }
    public function getAvailableWidgets() { return $this->disabled(); }
    public function getWidgetContentTypes() { return $this->disabled(); }
    public function getContentTypeItems() { return $this->disabled(); }
    public function queryContentItems() { return $this->disabled(); }
    public function getWidgetFieldDefinitions() { return $this->disabled(); }
    public function getSectionConfiguration() { return $this->disabled(); }
    public function updateSectionConfiguration() { return $this->disabled(); }
    public function deleteSection() { return $this->disabled(); }
    public function createSection() { return $this->disabled(); }
    public function updateSectionPosition() { return $this->disabled(); }
    public function previewWidget() { return $this->disabled(); }
    public function getAvailableSectionTemplates() { return $this->disabled(); }
    public function addWidget() { return $this->disabled(); }
    public function getThemeAssets() { return $this->disabled(); }
    public function createDefaultContentItem() { return $this->disabled(); }
}