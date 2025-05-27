<?php

namespace Database\Seeders;

use App\Models\Widget;
use App\Models\WidgetDisplaySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class WidgetDisplaySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if widget display settings already exist
        if (WidgetDisplaySetting::count() > 0) {
            $this->command->info('Widget display settings already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding widget display settings...');

        // First, make sure required migrations exist
        if (!Schema::hasTable('widget_display_settings')) {
            $this->command->error('widget_display_settings table does not exist. Please run migrations first.');
            return;
        }

        // Get existing widgets
        $widgets = Widget::all();
        if ($widgets->isEmpty()) {
            $this->command->error('No widgets found. Please run WidgetSeeder first.');
            return;
        }

        // Create display settings for widgets
        $displaySettings = [
            // Home page hero widget
            [
                'layout' => 'hero',
                'view_mode' => 'full',
                'pagination_type' => 'none',
                'items_per_page' => null,
                'empty_text' => 'No content available',
                'widget_name' => 'Home Hero',
            ],
            
            // Home content widget
            [
                'layout' => 'standard-content',
                'view_mode' => 'full',
                'pagination_type' => 'none',
                'items_per_page' => null,
                'empty_text' => 'No content available',
                'widget_name' => 'Home Content',
            ],
            
            // About header widget
            [
                'layout' => 'header',
                'view_mode' => 'full',
                'pagination_type' => 'none',
                'items_per_page' => null,
                'empty_text' => 'No content available',
                'widget_name' => 'About Header',
            ],
            
            // Team members widget
            [
                'layout' => 'grid',
                'view_mode' => 'teaser',
                'pagination_type' => 'none',
                'items_per_page' => 10,
                'empty_text' => 'No team members available',
                'widget_name' => 'Team Members',
            ],
            
            // Services widget
            [
                'layout' => 'list',
                'view_mode' => 'teaser',
                'pagination_type' => 'none',
                'items_per_page' => 10,
                'empty_text' => 'No services available',
                'widget_name' => 'Our Services',
            ],
            
            // Blog post widget
            [
                'layout' => 'standard',
                'view_mode' => 'full',
                'pagination_type' => 'none',
                'items_per_page' => null,
                'empty_text' => 'Blog post not found',
                'widget_name' => 'Sample Blog Post',
            ],
            
            // Contact form widget
            [
                'layout' => 'default',
                'view_mode' => 'full',
                'pagination_type' => 'none',
                'items_per_page' => null,
                'empty_text' => 'Contact form not available',
                'widget_name' => 'Contact Form',
            ],
            
            // Recent posts widget
            [
                'layout' => 'list',
                'view_mode' => 'teaser',
                'pagination_type' => 'simple',
                'items_per_page' => 5,
                'empty_text' => 'No recent posts available',
                'widget_name' => 'Recent Posts',
            ]
        ];

        foreach ($displaySettings as $settingData) {
            // Find matching widget
            $widgetName = $settingData['widget_name'];
            unset($settingData['widget_name']);
            $widget = $widgets->where('name', $widgetName)->first();
            
            if ($widget) {
                // Create the display settings
                $displaySetting = WidgetDisplaySetting::create($settingData);
                
                // Update the widget with the display settings ID
                $widget->display_settings_id = $displaySetting->id;
                $widget->save();
            }
        }

        $this->command->info('Widget display settings seeded successfully!');
    }
}
