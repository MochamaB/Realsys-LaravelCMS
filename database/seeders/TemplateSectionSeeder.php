<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSectionSeeder extends Seeder
{
    public function run(): void
    {
        $templates = DB::table('templates')->get();
        if ($templates->isEmpty()) {
            $this->command->error('No templates found. Please run TemplateSeeder first.');
            return;
        }

        foreach ($templates as $template) {
            // Delete existing sections for this template
            DB::table('template_sections')->where('template_id', $template->id)->delete();

            // Create sections based on template
            $sections = [];
            switch ($template->slug) {
                case 'home':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Hero section with background image'],
                        ['name' => 'Posts', 'slug' => 'posts', 'description' => 'Recent blog posts section'],
                    ];
                    break;
                case 'post':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Post header with background image'],
                        ['name' => 'Content', 'slug' => 'content', 'description' => 'Main post content area'],
                    ];
                    break;
                case 'about':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Hero section with background image'],
                        ['name' => 'Content', 'slug' => 'content', 'description' => 'Main content section'],
                    ];
                    break;
                case 'contact':
                    $sections = [
                        ['name' => 'Hero', 'slug' => 'hero', 'description' => 'Hero section with background image'],
                        ['name' => 'Contact Form', 'slug' => 'form', 'description' => 'Contact form section'],
                    ];
                    break;
            }

            foreach ($sections as $section) {
                DB::table('template_sections')->insert(array_merge($section, [
                    'template_id' => $template->id,
                    'is_required' => false,
                    'max_widgets' => null,
                    'order_index' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
