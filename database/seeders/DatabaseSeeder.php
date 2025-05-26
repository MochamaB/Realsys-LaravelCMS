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

        // Finally, create widgets and link them to pages
        $this->call(WidgetTypeSeeder::class);
        $this->call(WidgetTypeFieldSeeder::class);
        $this->call(WidgetSeeder::class);
        $this->call(PageWidgetSeeder::class);
    }
}
