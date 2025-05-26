<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user ID
        $adminId = DB::table('admins')->where('email', 'superadmin@realsyscms.co.ke')->value('id');
        if (!$adminId) {
            $this->command->error('Admin user not found. Please run AdminSeeder first.');
            return;
        }

        // Get all pages
        $pages = DB::table('pages')->get();
        if ($pages->isEmpty()) {
            $this->command->error('No pages found. Please run PageSeeder first.');
            return;
        }

        foreach ($pages as $page) {
            // Get template sections for this page's template
            $templateSections = DB::table('template_sections')
                ->where('template_id', $page->template_id)
                ->get();

            foreach ($templateSections as $templateSection) {
                // Delete existing page section if it exists
                DB::table('page_sections')
                    ->where('page_id', $page->id)
                    ->where('template_section_id', $templateSection->id)
                    ->delete();

                // Create page section
                DB::table('page_sections')->insert([
                    'page_id' => $page->id,
                    'template_section_id' => $templateSection->id,
                    'title' => $templateSection->name,
                    'is_visible' => true,
                    'order_index' => $templateSection->order_index,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
