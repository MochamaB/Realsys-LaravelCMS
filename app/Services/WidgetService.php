<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\PageSectionWidget;
use App\Models\WidgetFieldDefinition;
use App\Models\WidgetContentTypeAssociation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;

class WidgetService
{
    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * Constructor
     *
     * @param ThemeManager $themeManager
     */
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
    }

    /**
     * Get widgets for a page section
     *
     * @param int $pageSectionId
     * @return array
     */
    public function getWidgetsForSection(int $pageSectionId): array
    {
        // Debug info for section widgets
        \Log::debug('Getting widgets for section', ['section_id' => $pageSectionId]);
        
        // Get widget relationships through the pivot table
        $pivotRecords = PageSectionWidget::where('page_section_id', $pageSectionId)
            ->orderBy('position')
            ->get();
        
        \Log::debug('Pivot records found', [
            'section_id' => $pageSectionId, 
            'count' => $pivotRecords->count()
        ]);
        
        $widgetData = [];
        
        foreach ($pivotRecords as $pivot) {
            $widget = Widget::find($pivot->widget_id);
            
            if ($widget) {
                $widgetInfo = [
                    'widget_id' => $widget->id,
                    'widget_name' => $widget->name,
                    'widget_slug' => $widget->slug
                ];
                \Log::debug('Widget found', $widgetInfo);
                
                $widgetData[] = $this->prepareWidgetData($widget);
            } else {
                \Log::warning('Widget not found', ['widget_id' => $pivot->widget_id]);
            }
        }
        
        \Log::debug('Widget data prepared', [
            'section_id' => $pageSectionId,
            'widgets_count' => count($widgetData)
        ]);
        
        return $widgetData;
    }
    
    /**
     * Prepare widget data for rendering
     *
     * @param Widget $widget
     * @return array
     */
    public function prepareWidgetData(Widget $widget): array
    {
        // Basic widget data
        $data = [
            'id' => $widget->id,
            'name' => $widget->name,
            'slug' => $widget->slug,
            'view_path' => $this->resolveWidgetViewPath($widget),
            'fields' => $this->getWidgetFieldValues($widget),
        ];
        
        // Add content data if this widget has content associations
        if ($widget->contentTypeAssociations()->exists()) {
            $data['content'] = $this->getWidgetContent($widget);
        }
        
        return $data;
    }
    
    /**
     * Get field values for a widget
     *
     * @param Widget $widget
     * @return array
     */
    public function getWidgetFieldValues(Widget $widget): array
    {
        $fieldValues = [];
        
        // Get field definitions for this widget
        $fieldDefinitions = WidgetFieldDefinition::where('widget_id', $widget->id)->get();
        
        foreach ($fieldDefinitions as $field) {
            $fieldValues[$field->field_name] = $this->formatFieldValue($field);
        }
        
        return $fieldValues;
    }
    
    /**
     * Format a field value based on its type
     *
     * @param WidgetFieldDefinition $field
     * @return mixed
     */
    protected function formatFieldValue(WidgetFieldDefinition $field)
    {
        switch ($field->field_type) {
            case 'boolean':
                return (bool) $field->field_value;
            case 'integer':
                return (int) $field->field_value;
            case 'json':
                return json_decode($field->field_value, true) ?: [];
            case 'array':
                return explode(',', $field->field_value);
            default:
                return $field->field_value;
        }
    }
    
    /**
     * Resolve the view path for a widget
     *
     * @param Widget $widget
     * @return string
     */
    public function resolveWidgetViewPath(Widget $widget): string
    {
        // If widget has explicit view_path, use that
        if (!empty($widget->view_path)) {
            return $this->formatViewPath($widget->view_path);
        }
        
        // Otherwise use the widget slug to determine view path
        $theme = $this->themeManager->getActiveTheme();
        
        // Check theme-specific path first
        $themeWidgetPath = "theme::widgets.{$widget->slug}";
        if ($this->viewExists($themeWidgetPath)) {
            return $themeWidgetPath;
        }
        
        // Check global widget path
        $globalWidgetPath = "widgets.{$widget->slug}";
        if ($this->viewExists($globalWidgetPath)) {
            return $globalWidgetPath;
        }
        
        // Fallback to a default widget template
        return "theme::widgets.default";
    }
    
    /**
     * Format view path to ensure it's properly namespaced
     *
     * @param string $path
     * @return string
     */
    protected function formatViewPath(string $path): string
    {
        // If path already has a namespace, return as is
        if (strpos($path, '::') !== false) {
            return $path;
        }
        
        // If path starts with a slash, treat as absolute path
        if (strpos($path, '/') === 0) {
            return $path;
        }
        
        // If path already starts with 'widgets.', just add theme namespace
        if (strpos($path, 'widgets.') === 0) {
            return 'theme::' . $path;
        }
        
        // Otherwise, prefix with theme namespace and widgets directory
        return "theme::widgets.{$path}";
    }
    
    /**
     * Check if a view exists
     *
     * @param string $view
     * @return bool
     */
    protected function viewExists(string $view): bool
    {
        return View::exists($view);
    }
    
    /**
     * Get content for a widget based on its content type associations
     *
     * @param Widget $widget
     * @return array
     */
    protected function getWidgetContent(Widget $widget): array
    {
        // First try to get actual content from database based on content type associations
        $content = $this->fetchContentFromDatabase($widget);
        
        // If no content was found in the database, use default content
        if (empty($content)) {
            // Check the widget slug to determine what default content to provide
            switch ($widget->slug) {
                case 'post-list':
                    $content = $this->getDefaultPostListContent();
                    break;
                    
                case 'page-header':
                    $content = $this->getDefaultHeaderContent();
                    break;
                    
                case 'contact-form':
                    $content = $this->getDefaultContactContent();
                    break;
                    
                // Add more widget types as needed
                
                default:
                    // Default empty content
                    break;
            }
        }
        
        return $content;
    }
    
    /**
     * Fetch content from database based on widget content type associations
     *
     * @param Widget $widget
     * @return array
     */
    protected function fetchContentFromDatabase(Widget $widget): array
    {
        $content = [];
        
        // Get content type associations for this widget
        $associations = $widget->contentTypeAssociations()->with('contentType')->get();
        
        // Debug info
        $debugData = [
            'widget_id' => $widget->id,
            'widget_name' => $widget->name,
            'widget_slug' => $widget->slug,
            'association_count' => $associations->count()
        ];
        
        // Log to the Laravel debug file
        \Log::debug('Widget content debug info', $debugData);
        
        if ($associations->isEmpty()) {
            return $content;
        }
        
        // Process each content type association
        foreach ($associations as $association) {
            $contentType = $association->contentType;
            
            if (!$contentType) {
                \Log::warning('Content type not found for association', [
                    'widget_id' => $widget->id,
                    'association_id' => $association->id,
                    'content_type_id' => $association->content_type_id
                ]);
                continue;
            }
            
            // Different content handling based on widget slug
            switch ($widget->slug) {
                case 'post-list':
                    $content = $this->fetchPostListContent($contentType, $association);
                    break;
                
                case 'page-header':
                case 'hero-header':
                    $content = $this->fetchHeaderContent($contentType, $association);
                    break;
                    
                case 'contact-form':
                    $content = $this->fetchContactContent($contentType, $association);
                    break;
                    
                default:
                    // Generic content fetch
                    $content[$contentType->slug] = $this->fetchGenericContent($contentType, $association);
                    break;
            }
            
            // Log content result
            \Log::debug('Content fetch result', [
                'widget_id' => $widget->id,
                'widget_slug' => $widget->slug,
                'content_type' => $contentType->slug,
                'content_found' => !empty($content)
            ]);
        }
        
        return $content;
    }
    
    /**
     * Fetch post list content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchPostListContent($contentType, $association): array
    {
        // Using DB facade since we're in early implementation phase
        // In a full implementation, we'd use proper models and repositories
        $posts = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy($association->sort_field ?: 'published_at', $association->getSortDirection())
            ->limit($association->limit ?: 5)
            ->get();
        
        if ($posts->isEmpty()) {
            return [];
        }
        
        $formattedPosts = [];
        
        foreach ($posts as $post) {
            // Get field values for this post
            $fieldValues = \DB::table('content_field_values')
                ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
                ->where('content_field_values.content_item_id', $post->id)
                ->select('content_type_fields.slug', 'content_field_values.value')
                ->get()
                ->keyBy('slug')
                ->map(function ($item) {
                    return $item->value;
                })
                ->toArray();
            
            // Format the post data
            $formattedPosts[] = [
                'id' => $post->id,
                'title' => $post->title,
                'subtitle' => $fieldValues['subtitle'] ?? '',
                'excerpt' => $fieldValues['content'] ? $this->createExcerpt($fieldValues['content']) : '',
                'url' => $fieldValues['url'] ?? '/content/' . $post->slug,
                'created_at' => $post->created_at,
                'author' => ['name' => $fieldValues['author_name'] ?? 'Admin']
            ];
        }
        
        return [
            'posts' => $formattedPosts,
            'category' => null,
            'total_count' => count($formattedPosts)
        ];
    }
    
    /**
     * Fetch header content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchHeaderContent($contentType, $association): array
    {
        // Get the latest header content
        $header = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$header) {
            return [];
        }
        
        // Get field values for this header
        $fieldValues = \DB::table('content_field_values')
            ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
            ->where('content_field_values.content_item_id', $header->id)
            ->select('content_type_fields.slug', 'content_field_values.value')
            ->get()
            ->keyBy('slug')
            ->map(function ($item) {
                return $item->value;
            })
            ->toArray();
        
        return [
            'header' => [
                'title' => $header->title,
                'subtitle' => $fieldValues['subtitle'] ?? '',
                'background' => $fieldValues['background'] ?? '',
                'cta_text' => $fieldValues['cta_text'] ?? '',
                'cta_url' => $fieldValues['cta_url'] ?? ''
            ]
        ];
    }
    
    /**
     * Fetch contact content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchContactContent($contentType, $association): array
    {
        // Get the latest contact settings
        $contact = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->first();
        
        if (!$contact) {
            return [];
        }
        
        // Get field values for this contact
        $fieldValues = \DB::table('content_field_values')
            ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
            ->where('content_field_values.content_item_id', $contact->id)
            ->select('content_type_fields.slug', 'content_field_values.value')
            ->get()
            ->keyBy('slug')
            ->map(function ($item) {
                return $item->value;
            })
            ->toArray();
        
        return [
            'contact' => [
                'email' => $fieldValues['email'] ?? '',
                'phone' => $fieldValues['phone'] ?? '',
                'address' => $fieldValues['address'] ?? '',
                'form_recipient' => $fieldValues['form_recipient'] ?? '',
                'show_map' => filter_var($fieldValues['show_map'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'map_location' => $fieldValues['map_location'] ?? ''
            ]
        ];
    }
    
    /**
     * Fetch generic content from database
     *
     * @param ContentType $contentType
     * @param WidgetContentTypeAssociation $association
     * @return array
     */
    protected function fetchGenericContent($contentType, $association): array
    {
        // Get content items based on association settings
        $items = \DB::table('content_items')
            ->where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->orderBy($association->sort_field ?: 'created_at', $association->getSortDirection())
            ->limit($association->limit ?: 10)
            ->get();
        
        if ($items->isEmpty()) {
            return [];
        }
        
        $formattedItems = [];
        
        foreach ($items as $item) {
            // Get field values for this item
            $fieldValues = \DB::table('content_field_values')
                ->join('content_type_fields', 'content_field_values.content_type_field_id', '=', 'content_type_fields.id')
                ->where('content_field_values.content_item_id', $item->id)
                ->select('content_type_fields.slug', 'content_field_values.value')
                ->get()
                ->keyBy('slug')
                ->map(function ($item) {
                    return $item->value;
                })
                ->toArray();
            
            // Add base item data
            $itemData = [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'url' => '/content/' . $contentType->slug . '/' . $item->slug,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'published_at' => $item->published_at,
            ];
            
            // Add all field values
            foreach ($fieldValues as $key => $value) {
                $itemData[$key] = $value;
            }
            
            $formattedItems[] = $itemData;
        }
        
        return $formattedItems;
    }
    
    /**
     * Create excerpt from HTML content
     *
     * @param string $content
     * @param int $length
     * @return string
     */
    protected function createExcerpt(string $content, int $length = 150): string
    {
        // Remove HTML tags
        $text = strip_tags($content);
        
        // Trim to length
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }
        
        return $text;
    }
    
    /**
     * Get default content for post-list widget
     *
     * @return array
     */
    protected function getDefaultPostListContent(): array
    {
        // Create sample post data
        $now = now();
        $yesterday = $now->copy()->subDay();
        $twoDaysAgo = $now->copy()->subDays(2);
        
        return [
            'posts' => [
                [
                    'id' => 1,
                    'title' => 'Getting Started with Laravel CMS',
                    'subtitle' => 'A beginner\'s guide to our CMS',
                    'excerpt' => 'Learn how to quickly set up and customize your first website using our powerful Laravel-based CMS platform.',
                    'url' => '/blog/getting-started',
                    'created_at' => $twoDaysAgo->toDateTimeString(),
                    'author' => ['name' => 'Admin']
                ],
                [
                    'id' => 2,
                    'title' => 'Working with Widgets',
                    'subtitle' => 'Create dynamic content with widgets',
                    'excerpt' => 'Widgets are a powerful way to add dynamic content to your pages. This guide explains how to use and customize them.',
                    'url' => '/blog/widgets-guide',
                    'created_at' => $yesterday->toDateTimeString(),
                    'author' => ['name' => 'Editor']
                ],
                [
                    'id' => 3,
                    'title' => 'Theming Your Website',
                    'subtitle' => 'Create beautiful custom themes',
                    'excerpt' => 'Learn how to create custom themes for your website, including templates, asset management, and responsive design principles.',
                    'url' => '/blog/theming',
                    'created_at' => $now->toDateTimeString(),
                    'author' => ['name' => 'Designer']
                ],
                [
                    'id' => 4,
                    'title' => 'Advanced Content Management',
                    'subtitle' => 'Taking your content to the next level',
                    'excerpt' => 'Discover advanced techniques for content management, including custom content types, taxonomies, and content relationships.',
                    'url' => '/blog/advanced-content',
                    'created_at' => $now->toDateTimeString(),
                    'author' => ['name' => 'Developer']
                ],
                [
                    'id' => 5,
                    'title' => 'SEO Best Practices',
                    'subtitle' => 'Optimize your site for search engines',
                    'excerpt' => 'Implement SEO best practices to improve your website\'s visibility in search engines and attract more visitors.',
                    'url' => '/blog/seo-practices',
                    'created_at' => $now->toDateTimeString(),
                    'author' => ['name' => 'Marketing']
                ],
            ],
            'category' => [
                'id' => 1,
                'name' => 'Tutorials',
                'slug' => 'tutorials',
                'url' => '/blog/category/tutorials'
            ],
            'total_count' => 5
        ];
    }
    
    /**
     * Get default content for page-header widget
     *
     * @return array
     */
    protected function getDefaultHeaderContent(): array
    {
        return [
            'header' => [
                'title' => 'Welcome to RealSys CMS',
                'subtitle' => 'A modern, flexible content management system',
                'background' => 'assets/img/home-bg.jpg',
                'cta_text' => 'Learn More',
                'cta_url' => '/about'
            ]
        ];
    }
    
    /**
     * Get default content for contact-form widget
     *
     * @return array
     */
    protected function getDefaultContactContent(): array
    {
        return [
            'contact' => [
                'email' => 'contact@example.com',
                'phone' => '+1 (555) 123-4567',
                'address' => '123 Main Street, Anytown, AN 12345',
                'form_recipient' => 'info@example.com',
                'show_map' => true,
                'map_location' => '40.7128,-74.0060'
            ]
        ];
    }
}
