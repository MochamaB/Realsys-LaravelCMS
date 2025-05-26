<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WidgetTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing widget types
        DB::table('widget_types')->delete();

        // Create widget types
        $widgetTypes = [
            [
                'name' => 'Hero Header',
                'slug' => 'hero-header',
                'description' => 'Hero section with background image',
                'icon' => 'image',
                'component_path' => 'widgets/hero-header',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Post List',
                'slug' => 'post-list',
                'description' => 'Display a list of posts',
                'icon' => 'list',
                'component_path' => 'widgets/post-list',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Contact Form',
                'slug' => 'contact-form',
                'description' => 'Contact form widget',
                'icon' => 'mail',
                'component_path' => 'widgets/contact-form',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($widgetTypes as $widgetType) {
            DB::table('widget_types')->insert($widgetType);
        }
    }
}
