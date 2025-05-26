<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealSysThemeSeeder extends Seeder
{
    protected $adminUserId;
    protected $themePath;

    public function run()
    {
        $this->themePath = resource_path('themes/realsys');
        
        // Get the first admin user ID for created_by/updated_by fields
        $firstUser = DB::table('admins')->first();
        if (!$firstUser) {
            $this->command->error('No users found in the database. Please create a user first.');
            return;
        }
        $this->adminUserId = $firstUser->id;

        // Check if theme exists and update or create
        $existingTheme = DB::table('themes')->where('slug', 'realsys')->first();
        
        $themeData = [
            'name' => 'RealSys Default',
            'slug' => 'realsys',
            'description' => 'A clean, modern blog theme with beautiful typography and responsive design',
            'version' => '1.0.0',
            'author' => 'NPPK',
            'screenshot_path' => 'themes/realsys/assets/img/home-bg.jpg',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $themeId = $existingTheme ? $existingTheme->id : DB::table('themes')->insertGetId($themeData);
        
        if ($existingTheme) {
            DB::table('themes')->where('id', $themeId)->update($themeData);
        }

        // Delete existing templates for this theme to avoid duplicates
        DB::table('templates')->where('theme_id', $themeId)->delete();

        // Insert Templates
        $templates = [
            [
                'name' => 'Home',
                'slug' => 'home',
                'description' => 'Homepage template with blog listings',
                'file_path' => 'templates/home.blade.php',
                'theme_id' => $themeId,
                'thumbnail_path' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Post',
                'slug' => 'post',
                'description' => 'Single post template',
                'file_path' => 'templates/post.blade.php',
                'theme_id' => $themeId,
                'thumbnail_path' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'About',
                'slug' => 'about',
                'description' => 'About page template',
                'file_path' => 'templates/about.blade.php',
                'theme_id' => $themeId,
                'thumbnail_path' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Contact',
                'slug' => 'contact',
                'description' => 'Contact page template',
                'file_path' => 'templates/contact.blade.php',
                'theme_id' => $themeId,
                'thumbnail_path' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            $templateId = DB::table('templates')->insertGetId($template);

            // Create page sections and their widgets
            $sections = [];
            $sectionWidgets = [];
            switch ($template['slug']) {
                case 'home':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Hero section with background image'],
                        ['name' => 'Posts', 'slug' => 'posts', 'description' => 'Recent blog posts section'],
                    ];
                    $sectionWidgets = [
                        'hero' => [
                            [
                                'widget_type_id' => DB::table('widget_types')->where('slug', 'hero-header')->value('id'),
                                'settings' => json_encode([
                                    'title' => 'Welcome to Our Blog',
                                    'subtitle' => 'A place where ideas come to life',
                                    'background_image' => '/themes/realsys/assets/img/home-bg.jpg'
                                ])
                            ]
                        ],
                        'posts' => [
                            [
                                'widget_type_id' => DB::table('widget_types')->where('slug', 'post-list')->value('id'),
                                'settings' => json_encode([
                                    'title' => 'Latest Posts',
                                    'posts_per_page' => 6
                                ])
                            ]
                        ]
                    ];
                    break;
                case 'post':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Post header with background image'],
                        ['name' => 'Content', 'slug' => 'content', 'description' => 'Main post content area']
                    ];
                    $sectionWidgets = [
                        'hero' => [
                            [
                                'widget_type_id' => DB::table('widget_types')->where('slug', 'hero-header')->value('id'),
                                'settings' => json_encode([
                                    'title' => 'Sample Post',
                                    'subtitle' => 'A journey through our thoughts',
                                    'background_image' => '/themes/realsys/assets/img/post-bg.jpg'
                                ])
                            ]
                        ]
                    ];
                    break;
                case 'about':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Hero section with background image'],
                        ['name' => 'Content', 'slug' => 'content', 'description' => 'Main content section']
                    ];
                    $sectionWidgets = [
                        'hero' => [
                            [
                                'widget_type_id' => DB::table('widget_types')->where('slug', 'hero-header')->value('id'),
                                'settings' => json_encode([
                                    'title' => 'About Us',
                                    'subtitle' => 'Learn more about our story',
                                    'background_image' => '/themes/realsys/assets/img/about-bg.jpg'
                                ])
                            ]
                        ]
                    ];
                    break;
                case 'contact':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Hero section with background image'],
                        ['name' => 'Contact Form', 'slug' => 'form', 'description' => 'Contact form section'],
                        ['name' => 'Map', 'slug' => 'map', 'description' => 'Google Maps section'],
                        ['name' => 'Footer', 'slug' => 'footer', 'description' => 'Page footer with social links'],
                    ];
                    break;
            }

            foreach ($sections as $section) {
                $sectionId = DB::table('template_sections')->insertGetId(array_merge($section, [
                    'template_id' => $templateId,
                    'is_required' => false,
                    'max_widgets' => null,
                    'order_index' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                // Create widgets for this section if any
                if (isset($sectionWidgets[$section['slug']])) {
                    foreach ($sectionWidgets[$section['slug']] as $widget) {
                        // Create the widget first
                        $widgetId = DB::table('widgets')->insertGetId([
                            'widget_type_id' => $widget['widget_type_id'],
                            'name' => $widget['settings']['title'] ?? 'Default Widget',
                            'description' => 'Auto-generated widget',
                            'is_active' => true,
                            'status' => 'published',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Then create the page_widget relationship
                        DB::table('page_widgets')->insert([
                            'page_section_id' => $sectionId,
                            'widget_id' => $widgetId,
                            'order_index' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Delete existing widget types
        DB::table('widget_types')->delete();

        // Insert Widget Types
        $widgetTypes = [
            [
                'name' => 'Hero Header',
                'slug' => 'hero-header',
                'description' => 'Header with background image and text overlay',
                'component_path' => 'widgets/hero-header.blade.php',
                'icon' => 'image',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Post List',
                'slug' => 'post-list',
                'description' => 'List of blog posts with preview',
                'component_path' => 'widgets/post-list.blade.php',
                'icon' => 'list',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Contact Form',
                'slug' => 'contact-form',
                'description' => 'Contact form with email functionality',
                'component_path' => 'widgets/contact-form.blade.php',
                'icon' => 'envelope',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($widgetTypes as $widgetType) {
            $widgetTypeId = DB::table('widget_types')->insertGetId($widgetType);

            // Insert widget type fields
            $fields = [];
            switch ($widgetType['slug']) {
                case 'hero-header':
                    $fields = [
                        [
                            'widget_type_id' => $widgetTypeId,
                            'name' => 'title',
                            'label' => 'Title',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_repeatable' => false,
                            'validation_rules' => 'required|max:255',
                            'help_text' => 'The main title to display in the hero header',
                            'order_index' => 0,
                        ],
                        [
                            'widget_type_id' => $widgetTypeId,
                            'name' => 'subtitle',
                            'label' => 'Subtitle',
                            'field_type' => 'text',
                            'is_required' => false,
                            'is_repeatable' => false,
                            'validation_rules' => 'max:255',
                            'help_text' => 'Optional subtitle to display below the main title',
                            'order_index' => 1,
                        ],
                        [
                            'widget_type_id' => $widgetTypeId,
                            'name' => 'background',
                            'label' => 'Background Image',
                            'field_type' => 'image',
                            'is_required' => true,
                            'is_repeatable' => false,
                            'validation_rules' => 'required|image|max:2048',
                            'help_text' => 'Background image for the hero header',
                            'order_index' => 2,
                        ],
                    ];
                    break;
                case 'post-list':
                    $fields = [
                        [
                            'widget_type_id' => $widgetTypeId,
                            'name' => 'per_page',
                            'label' => 'Posts Per Page',
                            'field_type' => 'number',
                            'is_required' => true,
                            'is_repeatable' => false,
                            'validation_rules' => 'required|integer|min:1|max:50',
                            'help_text' => 'Number of posts to display per page',
                            'default_value' => '5',
                            'order_index' => 0,
                        ],
                    ];
                    break;
                case 'contact-form':
                    $fields = [
                        [
                            'widget_type_id' => $widgetTypeId,
                            'name' => 'title',
                            'label' => 'Form Title',
                            'field_type' => 'text',
                            'is_required' => true,
                            'is_repeatable' => false,
                            'validation_rules' => 'required|max:255',
                            'help_text' => 'Title to display above the contact form',
                            'order_index' => 0,
                        ],
                        [
                            'widget_type_id' => $widgetTypeId,
                            'name' => 'recipient',
                            'label' => 'Email Recipient',
                            'field_type' => 'email',
                            'is_required' => true,
                            'is_repeatable' => false,
                            'validation_rules' => 'required|email',
                            'help_text' => 'Email address where form submissions will be sent',
                            'order_index' => 1,
                        ],
                    ];
                    break;
            }

            foreach ($fields as $field) {
                DB::table('widget_type_fields')->insert(array_merge($field, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Create default pages
        $pages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'description' => 'Welcome to our website',
                'content' => null,
                'template_id' => DB::table('templates')->where('slug', 'home')->where('theme_id', $themeId)->value('id'),
                'parent_id' => null,
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 0,
                'meta_title' => 'Home | RealSys',
                'meta_description' => 'Welcome to RealSys - Your Modern Real Estate Solution',
                'meta_keywords' => 'real estate, property, home, realsys',
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $this->adminUserId,
                'updated_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'description' => 'Learn more about our company',
                'content' => null,
                'template_id' => DB::table('templates')->where('slug', 'about')->where('theme_id', $themeId)->value('id'),
                'parent_id' => null,
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 1,
                'meta_title' => 'About Us | RealSys',
                'meta_description' => 'Learn about RealSys and our mission to revolutionize real estate',
                'meta_keywords' => 'about, company, team, realsys',
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $this->adminUserId,
                'updated_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'description' => 'Get in touch with us',
                'content' => null,
                'template_id' => DB::table('templates')->where('slug', 'contact')->where('theme_id', $themeId)->value('id'),
                'parent_id' => null,
                'is_active' => true,
                'show_in_menu' => true,
                'menu_order' => 2,
                'meta_title' => 'Contact Us | RealSys',
                'meta_description' => 'Contact RealSys for any inquiries or support',
                'meta_keywords' => 'contact, support, help, realsys',
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $this->adminUserId,
                'updated_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $pageIds = [];
        foreach ($pages as $page) {
            $pageIds[$page['slug']] = DB::table('pages')->insertGetId($page);
        }

        // Create page sections for each page
        foreach ($pageIds as $slug => $pageId) {
            $templateId = DB::table('pages')->where('id', $pageId)->value('template_id');
            $templateSections = DB::table('template_sections')
                ->where('template_id', $templateId)
                ->get();

            foreach ($templateSections as $section) {
                DB::table('page_sections')->insert([
                    'page_id' => $pageId,
                    'template_section_id' => $section->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Check and create/update menus
        $menus = [
            [
                'name' => 'Primary Navigation',
                'location' => 'primary',
                'description' => 'Main navigation menu',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Footer Menu',
                'location' => 'footer',
                'description' => 'Footer navigation menu',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($menus as $menu) {
            $existingMenu = DB::table('menus')->where('location', $menu['location'])->first();
            
            if ($existingMenu) {
                $menuId = $existingMenu->id;
                DB::table('menus')->where('id', $menuId)->update($menu);
            } else {
                $menuId = DB::table('menus')->insertGetId($menu);
            }

            // Create menu items
            $menuItems = [];
            foreach ($pages as $page) {
                $menuItems[] = [
                    'menu_id' => $menuId,
                    'parent_id' => null,
                    'title' => $page['title'],
                    'link_type' => 'page',
                    'page_id' => $pageIds[$page['slug']],
                    'custom_url' => null,
                    'target' => '_self',
                    'css_class' => null,
                    'order_index' => $page['menu_order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('menu_items')->insert($menuItems);
        }

        // Check if contact form exists
        $existingForm = DB::table('forms')->where('name', 'contact')->first();
        
        $formData = [
            'name' => 'contact',
            'title' => 'Contact Us',
            'description' => 'Get in touch with us',
            'success_message' => 'Thank you for your message. We will get back to you shortly.',
            'email_recipients' => json_encode(['admin@example.com']),
            'store_submissions' => true,
            'captcha_enabled' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($existingForm) {
            $formId = $existingForm->id;
            DB::table('forms')->where('id', $formId)->update($formData);
            // Delete existing fields to avoid duplicates
            DB::table('form_fields')->where('form_id', $formId)->delete();
        } else {
            $formId = DB::table('forms')->insertGetId($formData);
        }

        // Create form fields
        $formFields = [
            [
                'form_id' => $formId,
                'name' => 'name',
                'label' => 'Full Name',
                'type' => 'text',
                'placeholder' => 'Enter your full name',
                'help_text' => null,
                'validation_rules' => 'required|max:255',
                'order_index' => 0,
                'is_required' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_id' => $formId,
                'name' => 'email',
                'label' => 'Email Address',
                'type' => 'email',
                'placeholder' => 'Enter your email address',
                'help_text' => null,
                'validation_rules' => 'required|email|max:255',
                'order_index' => 1,
                'is_required' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_id' => $formId,
                'name' => 'subject',
                'label' => 'Subject',
                'type' => 'text',
                'placeholder' => 'Enter message subject',
                'help_text' => null,
                'validation_rules' => 'required|max:255',
                'order_index' => 2,
                'is_required' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'form_id' => $formId,
                'name' => 'message',
                'label' => 'Message',
                'type' => 'textarea',
                'placeholder' => 'Enter your message',
                'help_text' => null,
                'validation_rules' => 'required',
                'order_index' => 3,
                'is_required' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($formFields as $field) {
            DB::table('form_fields')->insert($field);
        }

        // Add media for theme (only if files exist)
        $mediaItems = [
            'home-bg.jpg',
            'about-bg.jpg', 
            'contact-bg.jpg',
            'post-bg.jpg'
        ];

        foreach ($mediaItems as $fileName) {
            $filePath = $this->themePath . '/assets/img/' . $fileName;
            
            if (file_exists($filePath)) {
                $existingMedia = DB::table('media')
                    ->where('model_type', 'App\Models\Theme')
                    ->where('model_id', $themeId)
                    ->where('file_name', $fileName)
                    ->first();
                
                $mediaData = [
                    'name' => pathinfo($fileName, PATHINFO_FILENAME),
                    'file_name' => $fileName,
                    'mime_type' => 'image/jpeg',
                    'disk' => 'public',
                    'size' => filesize($filePath),
                    'manipulations' => json_encode([]),
                    'custom_properties' => json_encode([]),
                    'generated_conversions' => json_encode([]),
                    'responsive_images' => json_encode([]),
                    'collection_name' => 'theme-images',
                    'model_type' => 'App\Models\Theme',
                    'model_id' => $themeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($existingMedia) {
                    $mediaId = $existingMedia->id;
                    DB::table('media')->where('id', $mediaId)->update($mediaData);
                } else {
                    $mediaId = DB::table('media')->insertGetId($mediaData);
                }

                // Copy the actual file to the public disk
                $destinationPath = storage_path('app/public/' . $mediaId . '/' . $fileName);
                
                // Create directory if it doesn't exist
                if (!file_exists(dirname($destinationPath))) {
                    mkdir(dirname($destinationPath), 0755, true);
                }
                
                // Copy file
                copy($filePath, $destinationPath);
            } else {
                $this->command->warn("Image file not found: {$filePath}");
            }
        }

        $this->command->info('RealSys theme seeded successfully!');
    }
}