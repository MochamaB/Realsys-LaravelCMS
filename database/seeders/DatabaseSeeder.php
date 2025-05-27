<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First create roles and users
        $this->call(RolesSeeder::class);
        $this->call(AdminSeeder::class);

        // Then seed theme structure
        $this->call(ThemeSeeder::class);
        $this->call(TemplateSeeder::class);
        $this->call(TemplateSectionSeeder::class);

        // Create pages and their sections
        $this->call(PageSeeder::class);
        $this->call(PageSectionSeeder::class);

        // Create content types and structure
        $this->call(ContentTypeSeeder::class);
        $this->call(ContentTypeFieldSeeder::class);
        $this->call(ContentTypeFieldOptionSeeder::class);
        $this->call(ContentItemSeeder::class);
        $this->call(ContentFieldValueSeeder::class);
        
        // Create widgets and link them to pages
        $this->call(WidgetTypeSeeder::class);
        $this->call(WidgetTypeFieldSeeder::class);
        $this->call(WidgetSeeder::class);
        
        // Link widgets to content
        if (class_exists('App\\Models\\WidgetContentQuery')) {
            $this->call(WidgetContentQuerySeeder::class);
            $this->call(WidgetDisplaySettingSeeder::class);
        }
        
        // Link widgets to pages
        $this->call(PageWidgetSeeder::class);
    }
}
