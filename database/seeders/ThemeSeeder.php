<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if seeder has already been run
        if (DB::table('themes')->count() > 0) {
            $this->command->info('Themes already exist. Skipping ThemeSeeder...');
            return;
        }

        $this->command->info('Starting theme registration process...');

        // Get default theme data
        $themePath = resource_path('themes/default');
        $themeConfigPath = $themePath . '/theme.json';

        if (!File::exists($themeConfigPath)) {
            $this->command->error('Theme configuration file not found at: ' . $themeConfigPath);
            return;
        }

        $themeConfig = json_decode(File::get($themeConfigPath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Error parsing theme.json: ' . json_last_error_msg());
            return;
        }

        // Register theme following the implementation order:
        // 1. Theme
        // 2. Templates
        // 3. Template Sections
        // 4. Widgets
        // 5. Content Types
        // 6. Widget-Content Type Associations
        // 7. Test Content
        
        // Step 1: Register Theme
        $themeId = $this->registerTheme($themeConfig);
        
        // Step 2-3: Register Templates and Template Sections
        $this->registerTemplates($themeConfig, $themeId);
        
        // Step 4: Register Widgets
        $this->registerWidgets($themeConfig, $themeId);
        
        // Step 5: Register Content Types
        $this->registerContentTypes($themeConfig);
        
        // Step 6: Register Widget-Content Type Associations
        $this->registerWidgetContentTypeAssociations($themeConfig);
        
        // Step 7: Create Test Content
        $this->createTestContent($themeConfig);
        
        $this->command->info('Theme registration completed successfully!');
    }

    /**
     * Register theme in the database
     */
    private function registerTheme(array $themeConfig): int
    {
        $this->command->info('Registering theme: ' . $themeConfig['name']);
        
        $themeId = DB::table('themes')->insertGetId([
            'name' => $themeConfig['name'],
            'slug' => $themeConfig['identifier'],
            'directory' => $themeConfig['identifier'],
            'description' => $themeConfig['description'],
            'version' => $themeConfig['version'],
            'author' => $themeConfig['author'],
            'is_active' => $themeConfig['active'] ?? false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Theme registered with ID: ' . $themeId);
        
        return $themeId;
    }

    /**
     * Register templates and their sections
     */
    private function registerTemplates(array $themeConfig, int $themeId): void
    {
        $this->command->info('Registering templates and sections...');
        
        foreach ($themeConfig['templates'] as $templateConfig) {
            // Register template
            $templateId = DB::table('templates')->insertGetId([
                'theme_id' => $themeId,
                'name' => $templateConfig['name'],
                'slug' => $templateConfig['identifier'],
                'file_path' => $templateConfig['file'],
                'description' => $templateConfig['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Registered template: ' . $templateConfig['name'] . ' (ID: ' . $templateId . ')');
            
            // Register template sections
            foreach ($templateConfig['sections'] as $sectionConfig) {
                $layoutSettings = json_encode($sectionConfig['layout'] ?? []);
                
                DB::table('template_sections')->insert([
                    'template_id' => $templateId,
                    'name' => $sectionConfig['name'],
                    'slug' => $sectionConfig['identifier'],
                    'position' => $sectionConfig['position'] ?? 0,
                    'section_type' => $sectionConfig['section_type'] ?? 'full-width',
                    'column_layout' => $sectionConfig['column_layout'] ?? null,
                    'description' => $sectionConfig['description'] ?? null,
                    'is_repeatable' => $sectionConfig['repeatable'] ?? false,
                    'max_widgets' => $sectionConfig['max_widgets'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info('  - Registered section: ' . $sectionConfig['name']);
            }
        }
    }

    /**
     * Register widgets from theme configuration
     */
    private function registerWidgets(array $themeConfig, int $themeId): void
    {
        $this->command->info('Registering widgets...');
        
        foreach ($themeConfig['widgets'] as $widgetConfig) {
            // Register widget
            $widgetId = DB::table('widgets')->insertGetId([
                'theme_id' => $themeId,
                'name' => $widgetConfig['name'],
                'slug' => $widgetConfig['identifier'],
                'description' => $widgetConfig['description'] ?? null,
                'icon' => $widgetConfig['icon'] ?? null,
                'view_path' => $widgetConfig['view'] ?? 'theme::widgets.' . Str::slug($widgetConfig['identifier']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Registered widget: ' . $widgetConfig['name'] . ' (ID: ' . $widgetId . ')');
            
            // Register widget field definitions
            foreach ($widgetConfig['fields'] as $fieldConfig) {
                DB::table('widget_field_definitions')->insert([
                    'widget_id' => $widgetId,
                    'name' => $fieldConfig['name'],
                    'slug' => $fieldConfig['identifier'],
                    'field_type' => $fieldConfig['type'],
                    'is_required' => $fieldConfig['required'] ?? false,
                    'position' => $fieldConfig['position'] ?? 0,
                    'description' => $fieldConfig['description'] ?? null,
                    'validation_rules' => $fieldConfig['validation'] ?? null,
                    'settings' => json_encode([
                        'default' => $fieldConfig['default'] ?? null,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info('  - Registered field: ' . $fieldConfig['name']);
            }
        }
    }

    /**
     * Register content types from theme configuration
     */
    private function registerContentTypes(array $themeConfig): void
    {
        $this->command->info('Registering content types...');
        
        foreach ($themeConfig['content_types'] as $contentTypeConfig) {
            // Register content type
            $contentTypeId = DB::table('content_types')->insertGetId([
                'name' => $contentTypeConfig['name'],
                'slug' => $contentTypeConfig['identifier'],
                'description' => $contentTypeConfig['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Registered content type: ' . $contentTypeConfig['name'] . ' (ID: ' . $contentTypeId . ')');
            
            // Register content type fields
            foreach ($contentTypeConfig['fields'] as $fieldConfig) {
                DB::table('content_type_fields')->insert([
                    'content_type_id' => $contentTypeId,
                    'name' => $fieldConfig['name'],
                    'slug' => $fieldConfig['identifier'],
                    'field_type' => $fieldConfig['type'],
                    'is_required' => $fieldConfig['required'] ?? false,
                    'position' => $fieldConfig['position'] ?? 0,
                    'description' => $fieldConfig['description'] ?? null,
                    'is_unique' => $fieldConfig['unique'] ?? false,
                    'validation_rules' => $fieldConfig['validation'] ?? null,
                    'settings' => json_encode([
                        'default' => $fieldConfig['default'] ?? null,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info('  - Registered field: ' . $fieldConfig['name']);
            }
        }
    }

    /**
     * Register widget-content type associations
     */
    private function registerWidgetContentTypeAssociations(array $themeConfig): void
    {
        $this->command->info('Registering widget-content type associations...');
        
        foreach ($themeConfig['widgets'] as $widgetConfig) {
            if (!isset($widgetConfig['content_types']) || empty($widgetConfig['content_types'])) {
                continue;
            }
            
            $widget = DB::table('widgets')
                ->where('slug', $widgetConfig['identifier'])
                ->first();
                
            if (!$widget) {
                $this->command->error('Widget not found with slug: ' . $widgetConfig['identifier']);
                continue;
            }
            
            foreach ($widgetConfig['content_types'] as $contentTypeIdentifier) {
                $contentType = DB::table('content_types')
                    ->where('slug', $contentTypeIdentifier)
                    ->first();
                    
                if (!$contentType) {
                    $this->command->error('Content type not found with slug: ' . $contentTypeIdentifier);
                    continue;
                }
                
                DB::table('widget_content_type_associations')->insert([
                    'widget_id' => $widget->id,
                    'content_type_id' => $contentType->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->command->info("Associated widget '{$widget->name}' with content type '{$contentType->name}'");
            }
        }
    }

    /**
     * Create test content for the theme
     */
    private function createTestContent(array $themeConfig): void
    {
        $this->command->info('Creating test content...');
        
        // Create article content
        $this->createArticleContent();
        
        // Create page header content
        $this->createPageHeaderContent();
        
        // Create contact settings content
        $this->createContactSettingsContent();
        
        // Create pages using the templates and test content
        $this->createPages($themeConfig);
    }

    /**
     * Create article test content
     */
    private function createArticleContent(): void
    {
        $contentType = DB::table('content_types')
            ->where('slug', 'article')
            ->first();
            
        if (!$contentType) 
        {
            $this->command->error('Content type not found with slug: article');
            return;
        }
        
        $articles = [
            [
                'title' => 'Getting Started with Laravel',
                'subtitle' => 'A beginner\'s guide to the popular PHP framework',
                'content' => '<p>Laravel is a web application framework with expressive, elegant syntax. We\'ve already laid the foundation — freeing you to create without sweating the small things.</p><p>Laravel strives to provide an amazing developer experience while providing powerful features such as thorough dependency injection, an expressive database abstraction layer, queues and scheduled jobs, unit and integration testing, and more.</p>',
                'author_name' => 'John Doe',
                'published_date' => Carbon::now()->subDays(5),
                'url' => '/blog/getting-started-with-laravel'
            ],
            [
                'title' => 'Building Modern UIs with TailwindCSS',
                'subtitle' => 'Learn how to create beautiful interfaces without writing custom CSS',
                'content' => '<p>Tailwind CSS is a utility-first CSS framework packed with classes like flex, pt-4, text-center and rotate-90 that can be composed to build any design, directly in your markup.</p><p>Instead of opinionated predesigned components, Tailwind provides low-level utility classes that let you build completely custom designs without ever leaving your HTML.</p>',
                'author_name' => 'Jane Smith',
                'published_date' => Carbon::now()->subDays(3),
                'url' => '/blog/building-modern-uis-with-tailwindcss'
            ],
            [
                'title' => 'Introduction to Content Management Systems',
                'subtitle' => 'Understanding the core concepts behind modern CMS platforms',
                'content' => '<p>A content management system (CMS) is a software application that can be used to manage the creation and modification of digital content. CMSs are typically used for enterprise content management (ECM) and web content management (WCM).</p><p>ECM typically supports multiple users in a collaborative environment by integrating document management, digital asset management, and record retention. Alternatively, WCM is the collaborative authoring for websites and may include text and embed graphics, photos, video, audio, maps, and program code that display content and interact with the user.</p>',
                'author_name' => 'Michael Johnson',
                'published_date' => Carbon::now()->subDays(1),
                'url' => '/blog/introduction-to-content-management-systems'
            ]
        ];
        
        foreach ($articles as $index => $articleData) {
            // Create content item
            $contentItemId = DB::table('content_items')->insertGetId([
                'content_type_id' => $contentType->id,
                'title' => $articleData['title'],
                'slug' => Str::slug($articleData['title']),
                'status' => 'published',
                'published_at' => $articleData['published_date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get field definitions for this content type
            $fields = DB::table('content_type_fields')
                ->where('content_type_id', $contentType->id)
                ->get();
                
            // Create field values
            foreach ($fields as $field) {
                $value = $articleData[$field->slug] ?? null;
                
                if ($value !== null) {
                    DB::table('content_field_values')->insert([
                        'content_item_id' => $contentItemId,
                        'content_type_field_id' => $field->id,
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info('Created article: ' . $articleData['title']);
        }
    }

    /**
     * Create page header test content
     */
    private function createPageHeaderContent(): void
    {
        $contentType = DB::table('content_types')
            ->where('slug', 'page_header')
            ->first();
            
        if (!$contentType) {
            $this->command->error('Content type not found with slug: page_header');
            return;
        }
        
        $headers = [
            [
                'title' => 'Welcome to RealSys CMS',
                'subtitle' => 'A modern, flexible content management system',
                'background' => 'assets/img/home-bg.jpg'
            ],
            [
                'title' => 'About Us',
                'subtitle' => 'Learn more about our team and mission',
                'background' => 'assets/img/about-bg.jpg'
            ],
            [
                'title' => 'Contact Us',
                'subtitle' => 'Get in touch with our team',
                'background' => 'assets/img/contact-bg.jpg'
            ]
        ];
        
        foreach ($headers as $index => $headerData) {
            // Create content item
            $contentItemId = DB::table('content_items')->insertGetId([
                'content_type_id' => $contentType->id,
                'title' => $headerData['title'],
                'slug' => Str::slug($headerData['title']),
                'status' => 'published',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get field definitions for this content type
            $fields = DB::table('content_type_fields')
                ->where('content_type_id', $contentType->id)
                ->get();
                
            // Create field values
            foreach ($fields as $field) {
                $value = $headerData[$field->slug] ?? null;
                
                if ($value !== null) {
                    DB::table('content_field_values')->insert([
                        'content_item_id' => $contentItemId,
                        'content_type_field_id' => $field->id,
                        'value' => is_array($value) ? json_encode($value) : $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info('Created page header: ' . $headerData['title']);
        }
    }

    /**
     * Create contact settings test content
     */
    private function createContactSettingsContent(): void
    {
        $contentType = DB::table('content_types')
            ->where('slug', 'contact_settings')
            ->first();
            
        if (!$contentType) {
            $this->command->error('Content type not found with slug: contact_settings');
            return;
        }
        
        $contactSettings = [
            'title' => 'Get In Touch',
            'recipient' => 'contact@realsyscms.co.ke',
            'success_message' => 'Thank you for your message. We will get back to you shortly.',
            'button_text' => 'Send Message'
        ];
        
        // Create content item
        $contentItemId = DB::table('content_items')->insertGetId([
            'content_type_id' => $contentType->id,
            'title' => $contactSettings['title'],
            'slug' => Str::slug($contactSettings['title']),
            'status' => 'published',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Get field definitions for this content type
        $fields = DB::table('content_type_fields')
            ->where('content_type_id', $contentType->id)
            ->get();
            
        // Create field values
        foreach ($fields as $field) {
            $value = $contactSettings[$field->slug] ?? null;
            
            if ($value !== null) {
                DB::table('content_field_values')->insert([
                    'content_item_id' => $contentItemId,
                    'content_type_field_id' => $field->id,
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Created contact settings: ' . $contactSettings['title']);
    }

    /**
     * Create pages using templates
     */
    private function createPages(array $themeConfig): void
    {
        $this->command->info('Creating demo pages...');
        
        $templates = DB::table('templates')->get();
        
        // Create home page
        $homeTemplate = $templates->where('slug', 'home')->first();
        if ($homeTemplate) {
            $homePageId = DB::table('pages')->insertGetId([
                'template_id' => $homeTemplate->id,
                'title' => 'Home',
                'slug' => 'home',
                'is_homepage' => true,
                'status' => 'published',
                'meta_keywords' => 'RealSys CMS - Home',
                'meta_description' => 'Welcome to RealSys CMS, a modern content management system',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Created home page');
            
            // Add sections to the home page
            $this->addSectionsToPage($homePageId, $homeTemplate->id);
        }
        
        // Create about page
        $aboutTemplate = $templates->where('slug', 'about')->first();
        if ($aboutTemplate) {
            $aboutPageId = DB::table('pages')->insertGetId([
                'template_id' => $aboutTemplate->id,
                'title' => 'About Us',
                'slug' => 'about',
                'is_homepage' => false,
                'status' => 'published',
                'meta_keywords' => 'RealSys CMS - About Us',
                'meta_description' => 'Learn more about the RealSys CMS team and our mission',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Created about page');
            
            // Add sections to the about page
            $this->addSectionsToPage($aboutPageId, $aboutTemplate->id);
        }
        
        // Create contact page
        $contactTemplate = $templates->where('slug', 'contact')->first();
        if ($contactTemplate) {
            $contactPageId = DB::table('pages')->insertGetId([
                'template_id' => $contactTemplate->id,
                'title' => 'Contact Us',
                'slug' => 'contact',
                'is_homepage' => false,
                'status' => 'published',
                'meta_keywords' => 'RealSys CMS - Contact Us',
                'meta_description' => 'Get in touch with the RealSys CMS team',
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Created contact page');
            
            // Add sections to the contact page
            $this->addSectionsToPage($contactPageId, $contactTemplate->id);
        }
    }

    /**
     * Add template sections to a page
     */
    private function addSectionsToPage(int $pageId, int $templateId): void
    {
        // Get template sections
        $templateSections = DB::table('template_sections')
            ->where('template_id', $templateId)
            ->get();
            
        foreach ($templateSections as $templateSection) {
            // Create page section
            $pageSectionId = DB::table('page_sections')->insertGetId([
                'page_id' => $pageId,
                'template_section_id' => $templateSection->id,
                'position' => $templateSection->position ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Add widgets to page section
            $this->addWidgetsToPageSection($pageSectionId, $templateSection->slug);
        }
    }

    /**
     * Add appropriate widgets to page sections
     */
    private function addWidgetsToPageSection(int $pageSectionId, string $sectionIdentifier): void
    {
        // Map section identifiers to appropriate widgets
        $sectionWidgetMap = [
            'hero' => 'hero-header',
            'posts' => 'post-list',
            'contact' => 'contact-form',
            'content' => null,
            'footer' => null,
            'author' => null,
        ];
        
        if (!isset($sectionWidgetMap[$sectionIdentifier]) || $sectionWidgetMap[$sectionIdentifier] === null) {
            return;
        }
        
        $widgetIdentifier = $sectionWidgetMap[$sectionIdentifier];
        
        $widget = DB::table('widgets')
            ->where('slug', $widgetIdentifier)
            ->first();
            
        if (!$widget) {
            return;
        }
        
        // Add widget to page section
        DB::table('page_section_widgets')->insert([
            'page_section_id' => $pageSectionId,
            'widget_id' => $widget->id,
            'settings' => json_encode([
                'order' => 1,
                'width' => 'full',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info("Added widget '{$widget->name}' to section '{$sectionIdentifier}'");
    }
}
