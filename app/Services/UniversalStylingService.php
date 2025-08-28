<?php

namespace App\Services;

use App\Models\PageSection;
use App\Models\PageSectionWidget;

class UniversalStylingService
{
    /**
     * Build CSS classes for a page section
     *
     * @param PageSection $pageSection
     * @return string
     */
    public function buildSectionClasses(PageSection $pageSection): string
    {
        $classes = [
            'cms-section',
            'section-' . ($pageSection->templateSection->section_type ?? 'default')
        ];
        
        // Add container class only if theme section doesn't handle containers
        // This prevents double container-fluid nesting
        $sectionType = $pageSection->templateSection->section_type ?? 'default';
        if (!$this->themeHandlesContainer($sectionType)) {
            $classes[] = 'container-fluid';
        }
        
        // Add custom CSS classes
        if ($pageSection->css_classes) {
            $classes[] = $pageSection->css_classes;
        }
        
        return collect($classes)->filter()->implode(' ');
    }

    /**
     * Build inline styles for a page section
     *
     * @param PageSection $pageSection
     * @return string
     */
    public function buildSectionStyles(PageSection $pageSection): string
    {
        $styles = [];
        
        if ($pageSection->background_color && $this->isValidColor($pageSection->background_color)) {
            $styles[] = "background-color: {$pageSection->background_color}";
        }
        
        if ($pageSection->padding) {
            $padding = is_array($pageSection->padding) 
                ? implode(' ', $pageSection->padding) 
                : $pageSection->padding;
            $styles[] = "padding: " . $this->ensureUnits($padding);
        }
        
        if ($pageSection->margin) {
            $margin = is_array($pageSection->margin)
                ? implode(' ', $pageSection->margin)
                : $pageSection->margin;
            $styles[] = "margin: " . $this->ensureUnits($margin);
        }
        
        return implode('; ', $styles);
    }

    /**
     * Build GridStack attributes for a page section
     *
     * @param PageSection $pageSection
     * @return string
     */
    public function buildGridAttributes(PageSection $pageSection): string
    {
        if ($pageSection->grid_x === null) {
            return '';
        }
        
        return sprintf(
            'data-gs-x="%d" data-gs-y="%d" data-gs-w="%d" data-gs-h="%d" data-gs-id="%s"',
            $pageSection->grid_x,
            $pageSection->grid_y,
            $pageSection->grid_w,
            $pageSection->grid_h,
            $pageSection->grid_id
        );
    }

    /**
     * Build CSS classes for a page section widget
     *
     * @param PageSectionWidget $widget
     * @return string
     */
    public function buildWidgetClasses(PageSectionWidget $widget): string
    {
        return collect([
            'cms-widget',
            'widget-' . $widget->widget->slug,
            $widget->css_classes
        ])->filter()->implode(' ');
    }

    /**
     * Build inline styles for a page section widget
     *
     * @param PageSectionWidget $widget
     * @return string
     */
    public function buildWidgetStyles(PageSectionWidget $widget): string
    {
        $styles = [];
        
        if ($widget->padding) {
            $padding = is_array($widget->padding) 
                ? implode(' ', $widget->padding) 
                : $widget->padding;
            $styles[] = "padding: " . $this->ensureUnits($padding);
        }
        
        if ($widget->margin) {
            $margin = is_array($widget->margin)
                ? implode(' ', $widget->margin)
                : $widget->margin;
            $styles[] = "margin: " . $this->ensureUnits($margin);
        }
        
        if ($widget->min_height) {
            $styles[] = "min-height: " . $this->ensureUnits($widget->min_height);
        }
        
        if ($widget->max_height) {
            $styles[] = "max-height: " . $this->ensureUnits($widget->max_height);
        }
        
        return implode('; ', $styles);
    }

    /**
     * Build GridStack attributes for a page section widget
     *
     * @param PageSectionWidget $widget
     * @return string
     */
    public function buildWidgetGridAttributes(PageSectionWidget $widget): string
    {
        if ($widget->grid_x === null) {
            return '';
        }
        
        return sprintf(
            'data-gs-x="%d" data-gs-y="%d" data-gs-w="%d" data-gs-h="%d" data-gs-id="%s"',
            $widget->grid_x,
            $widget->grid_y,
            $widget->grid_w,
            $widget->grid_h,
            $widget->grid_id
        );
    }

    /**
     * Ensure CSS values have proper units (px, rem, em, %, etc.)
     * Converts "40 0 0 40" to "40px 0 0 40px"
     * 
     * @param string $value
     * @return string
     */
    private function ensureUnits(string $value): string
    {
        // If already has units, return as is
        if (preg_match('/(px|rem|em|%|vh|vw|pt|pc|in|cm|mm|ex|ch|vmin|vmax)/', $value)) {
            return $value;
        }
        
        // Handle space-separated values (like "40 0 0 40")
        $parts = preg_split('/\s+/', trim($value));
        $result = [];
        
        foreach ($parts as $part) {
            if (is_numeric($part)) {
                // Add px unit to numeric values
                $result[] = $part === '0' ? '0' : $part . 'px';
            } else {
                // Keep non-numeric values as is (auto, inherit, etc.)
                $result[] = $part;
            }
        }
        
        return implode(' ', $result);
    }

    /**
     * Check if theme section template handles its own container classes
     * This prevents double container-fluid nesting
     * 
     * @param string $sectionType
     * @return bool
     */
    private function themeHandlesContainer(string $sectionType): bool
    {
        // List of section types where theme templates handle containers
        // Based on actual analysis of theme files in resources/themes/miata/sections/
        $themeContainerMap = [
            'full-width' => true,    // has container-fluid
            'multi-column' => true,  // has container
            'sidebar-left' => true,  // has container
            'sidebar-right' => true, // has container
            'default' => false       // has NO container, just col-12
        ];
        
        return $themeContainerMap[$sectionType] ?? false;
    }

    /**
     * Convert padding/margin array to CSS string
     *
     * @param array|string|null $spacing
     * @return string|null
     */
    protected function formatSpacing($spacing): ?string
    {
        if (empty($spacing)) {
            return null;
        }

        if (is_string($spacing)) {
            return $spacing;
        }

        if (is_array($spacing)) {
            // Handle different array formats
            if (isset($spacing['top'])) {
                // Object format: {top: '10px', right: '15px', bottom: '10px', left: '15px'}
                return sprintf(
                    '%s %s %s %s',
                    $spacing['top'] ?? '0',
                    $spacing['right'] ?? '0',
                    $spacing['bottom'] ?? '0',
                    $spacing['left'] ?? '0'
                );
            } else {
                // Array format: ['10px', '15px', '10px', '15px']
                return implode(' ', array_slice($spacing, 0, 4));
            }
        }

        return null;
    }

    /**
     * Get default grid configuration for sections
     *
     * @return array
     */
    public function getDefaultSectionGridConfig(): array
    {
        return [
            'x' => 0,
            'y' => 0,
            'w' => 12,
            'h' => 4
        ];
    }

    /**
     * Get default grid configuration for widgets
     *
     * @return array
     */
    public function getDefaultWidgetGridConfig(): array
    {
        return [
            'x' => 0,
            'y' => 0,
            'w' => 6,
            'h' => 2
        ];
    }

    /**
     * Generate responsive classes based on grid width
     *
     * @param int $gridWidth
     * @return string
     */
    public function generateResponsiveClasses(int $gridWidth): string
    {
        $classes = [];
        
        // Bootstrap column classes based on grid width
        if ($gridWidth <= 3) {
            $classes[] = 'col-sm-12 col-md-6 col-lg-3';
        } elseif ($gridWidth <= 6) {
            $classes[] = 'col-sm-12 col-md-6';
        } elseif ($gridWidth <= 9) {
            $classes[] = 'col-sm-12 col-md-9';
        } else {
            $classes[] = 'col-12';
        }
        
        return implode(' ', $classes);
    }

    /**
     * Validate and sanitize CSS class string
     *
     * @param string $classes
     * @return string
     */
    public function sanitizeCSSClasses(string $classes): string
    {
        // Remove potentially dangerous characters and normalize
        $sanitized = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $classes);
        
        // Split, filter empty, and rejoin
        return collect(explode(' ', $sanitized))
            ->filter()
            ->unique()
            ->implode(' ');
    }

    /**
     * Validate color value
     *
     * @param string $color
     * @return bool
     */
    public function isValidColor(string $color): bool
    {
        // Check for hex colors
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return true;
        }
        
        // Check for RGB/RGBA
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+(?:\s*,\s*[\d.]+)?\s*\)$/', $color)) {
            return true;
        }
        
        // Check for HSL/HSLA
        if (preg_match('/^hsla?\(\s*\d+\s*,\s*\d+%\s*,\s*\d+%(?:\s*,\s*[\d.]+)?\s*\)$/', $color)) {
            return true;
        }
        
        // Check for named colors (basic validation)
        $namedColors = ['red', 'blue', 'green', 'yellow', 'black', 'white', 'gray', 'orange', 'purple', 'pink'];
        return in_array(strtolower($color), $namedColors);
    }
}