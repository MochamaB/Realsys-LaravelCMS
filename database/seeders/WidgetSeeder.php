<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WidgetSeeder extends Seeder
{
    public function run(): void
    {
        // Get widget types
        $heroTypeId = DB::table('widget_types')->where('slug', 'hero-header')->value('id');
        $postListTypeId = DB::table('widget_types')->where('slug', 'post-list')->value('id');
        $contactFormTypeId = DB::table('widget_types')->where('slug', 'contact-form')->value('id');

        if (!$heroTypeId || !$postListTypeId || !$contactFormTypeId) {
            $this->command->error('Widget types not found. Please run WidgetTypeSeeder first.');
            return;
        }

        // Create widgets
        $widgets = [
            // Home page widgets
            [
                'widget_type_id' => $heroTypeId,
                'name' => 'Welcome to Our Blog',
                'description' => 'Homepage hero section',
                'is_active' => true,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_type_id' => $postListTypeId,
                'name' => 'Latest Posts',
                'description' => 'Homepage post listings',
                'is_active' => true,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // About page widgets
            [
                'widget_type_id' => $heroTypeId,
                'name' => 'About Us',
                'description' => 'About page hero section',
                'is_active' => true,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Contact page widgets
            [
                'widget_type_id' => $heroTypeId,
                'name' => 'Contact Us',
                'description' => 'Contact page hero section',
                'is_active' => true,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_type_id' => $contactFormTypeId,
                'name' => 'Contact Form',
                'description' => 'Contact form widget',
                'is_active' => true,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($widgets as $widget) {
            DB::table('widgets')->insert($widget);
        }
    }
}
