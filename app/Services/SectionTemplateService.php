<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class SectionTemplateService
{
    /**
     * Get all available section templates.
     *
     * @return Collection
     */
    public function getAllTemplates(): Collection
    {
        $templates = Config::get('section_templates.templates', []);
        return collect($templates)->map(function ($template, $key) {
            return array_merge($template, ['key' => $key]);
        });
    }

    /**
     * Get templates by category.
     *
     * @param string $category
     * @return Collection
     */
    public function getTemplatesByCategory(string $category): Collection
    {
        return $this->getAllTemplates()->filter(function ($template) use ($category) {
            return $template['category'] === $category;
        });
    }

    /**
     * Get a specific template by key.
     *
     * @param string $key
     * @return array|null
     */
    public function getTemplate(string $key): ?array
    {
        $templates = Config::get('section_templates.templates', []);
        
        if (!isset($templates[$key])) {
            return null;
        }

        return array_merge($templates[$key], ['key' => $key]);
    }

    /**
     * Get the default template.
     *
     * @return array|null
     */
    public function getDefaultTemplate(): ?array
    {
        $defaultKey = Config::get('section_templates.default_template', 'full-width');
        return $this->getTemplate($defaultKey);
    }

    /**
     * Get template categories.
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return collect(Config::get('section_templates.categories', []));
    }

    /**
     * Apply a template to a section with overrides.
     *
     * @param string $templateKey
     * @param array $overrides
     * @return array
     */
    public function applyTemplateToSection(string $templateKey, array $overrides = []): array
    {
        $template = $this->getTemplate($templateKey);
        
        if (!$template) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found.");
        }

        // Generate unique grid ID with timestamp and random string
        $gridId = 'section_' . time() . '_' . uniqid() . '_' . substr(md5($templateKey), 0, 8);

        // Build section data from template
        $sectionData = [
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_w' => $template['default_size']['w'] ?? 12,
            'grid_h' => $template['default_size']['h'] ?? 4,
            'grid_id' => $gridId,
            'grid_config' => $template['grid_config'],
            'allows_widgets' => $template['grid_config']['acceptWidgets'] ?? true,
            'widget_types' => $template['widget_constraints']['allowed_types'] ?? [],
            'css_classes' => $template['styling']['container_class'] ?? 'container',
            'background_color' => $template['styling']['default_background'] ?? '#ffffff',
            'padding' => $template['styling']['default_padding'] ?? ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
            'margin' => $template['styling']['default_margin'] ?? ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
            'locked_position' => false,
            'resize_handles' => $template['grid_config']['resizable']['handles'] ?? ['se', 'sw']
        ];

        // Apply any overrides
        return array_merge($sectionData, $overrides);
    }

    /**
     * Get widget constraints for a template.
     *
     * @param string $templateKey
     * @return array
     */
    public function getWidgetConstraints(string $templateKey): array
    {
        $template = $this->getTemplate($templateKey);
        
        if (!$template) {
            return [
                'allowed_types' => ['text', 'image', 'counter', 'gallery'],
                'max_widgets' => null,
                'default_widget_size' => ['w' => 6, 'h' => 3],
                'column_layout' => false
            ];
        }

        return $template['widget_constraints'];
    }

    /**
     * Validate if a widget type is allowed in a template.
     *
     * @param string $templateKey
     * @param string $widgetType
     * @return bool
     */
    public function isWidgetTypeAllowed(string $templateKey, string $widgetType): bool
    {
        $constraints = $this->getWidgetConstraints($templateKey);
        $allowedTypes = $constraints['allowed_types'] ?? [];
        
        return in_array($widgetType, $allowedTypes);
    }

    /**
     * Get default widget size for a template.
     *
     * @param string $templateKey
     * @return array
     */
    public function getDefaultWidgetSize(string $templateKey): array
    {
        $constraints = $this->getWidgetConstraints($templateKey);
        return $constraints['default_widget_size'] ?? ['w' => 6, 'h' => 3];
    }

    /**
     * Get column layout configuration for a template.
     *
     * @param string $templateKey
     * @return array|null
     */
    public function getColumnLayout(string $templateKey): ?array
    {
        $constraints = $this->getWidgetConstraints($templateKey);
        return $constraints['columns'] ?? null;
    }

    /**
     * Check if template has column layout.
     *
     * @param string $templateKey
     * @return bool
     */
    public function hasColumnLayout(string $templateKey): bool
    {
        $constraints = $this->getWidgetConstraints($templateKey);
        return $constraints['column_layout'] ?? false;
    }

    /**
     * Get template preview data for UI.
     *
     * @param string $templateKey
     * @return array
     */
    public function getTemplatePreview(string $templateKey): array
    {
        $template = $this->getTemplate($templateKey);
        
        if (!$template) {
            return [];
        }

        return [
            'key' => $templateKey,
            'name' => $template['name'],
            'icon' => $template['icon'],
            'description' => $template['description'],
            'category' => $template['category'],
            'preview_html' => $this->generatePreviewHtml($template),
            'grid_config' => $template['grid_config'],
            'widget_constraints' => $template['widget_constraints']
        ];
    }

    /**
     * Generate preview HTML for template.
     *
     * @param array $template
     * @return string
     */
    private function generatePreviewHtml(array $template): string
    {
        $layout = $template['key'] ?? 'full-width';
        
        switch ($layout) {
            case 'full-width':
                return '<div class="template-preview full-width"><div class="content-area"></div></div>';
            
            case 'multi-column':
                return '<div class="template-preview multi-column"><div class="col-1"></div><div class="col-2"></div><div class="col-3"></div></div>';
            
            case 'sidebar-left':
                return '<div class="template-preview sidebar-left"><div class="sidebar"></div><div class="main-content"></div></div>';
            
            case 'sidebar-right':
                return '<div class="template-preview sidebar-right"><div class="main-content"></div><div class="sidebar"></div></div>';
            
            default:
                return '<div class="template-preview default"><div class="content-area"></div></div>';
        }
    }

    /**
     * Validate template configuration.
     *
     * @param array $template
     * @return bool
     */
    public function validateTemplate(array $template): bool
    {
        $requiredFields = Config::get('section_templates.validation.required_fields', []);
        
        foreach ($requiredFields as $field) {
            if (!isset($template[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get template statistics.
     *
     * @return array
     */
    public function getTemplateStats(): array
    {
        $templates = $this->getAllTemplates();
        
        return [
            'total_templates' => $templates->count(),
            'categories' => $this->getCategories()->count(),
            'templates_by_category' => $templates->groupBy('category')->map->count()
        ];
    }
} 