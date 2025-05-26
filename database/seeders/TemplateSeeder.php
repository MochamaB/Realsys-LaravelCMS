<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $themeId = DB::table('themes')->where('slug', 'realsys')->value('id');
        if (!$themeId) {
            $this->command->error('Theme not found. Please run ThemeSeeder first.');
            return;
        }

        // Delete existing templates for this theme
        DB::table('templates')->where('theme_id', $themeId)->delete();

        // Create templates
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
            DB::table('templates')->insert($template);
        }
    }
}
