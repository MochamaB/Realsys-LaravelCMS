<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageWidgetSeeder extends Seeder
{
    public function run(): void
    {
        // Get all pages
        $pages = DB::table('pages')->get();
        if ($pages->isEmpty()) {
            $this->command->error('No pages found. Please run PageSeeder first.');
            return;
        }

        foreach ($pages as $page) {
            // Get page sections
            $pageSections = DB::table('page_sections')
                ->where('page_id', $page->id)
                ->get();

            foreach ($pageSections as $pageSection) {
                // Get template section to determine the type of widget needed
                $templateSection = DB::table('template_sections')
                    ->where('id', $pageSection->template_section_id)
                    ->first();

                if (!$templateSection) {
                    continue;
                }

                // Get appropriate widget based on section
                $widget = null;
                switch ($templateSection->slug) {
                    case 'hero':
                        $widget = DB::table('widgets')
                            ->where('widget_type_id', DB::table('widget_types')->where('slug', 'hero-header')->value('id'))
                            ->where('name', 'LIKE', "%{$page->title}%")
                            ->first();
                        break;
                    case 'posts':
                        $widget = DB::table('widgets')
                            ->where('widget_type_id', DB::table('widget_types')->where('slug', 'post-list')->value('id'))
                            ->first();
                        break;
                    case 'form':
                        $widget = DB::table('widgets')
                            ->where('widget_type_id', DB::table('widget_types')->where('slug', 'contact-form')->value('id'))
                            ->first();
                        break;
                }

                if ($widget) {
                    // Delete existing page widget if any
                    DB::table('page_widgets')
                        ->where('page_section_id', $pageSection->id)
                        ->delete();

                    // Create page widget relationship
                    DB::table('page_widgets')->insert([
                        'page_section_id' => $pageSection->id,
                        'widget_id' => $widget->id,
                        'order_index' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
