<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user ID
        $adminId = DB::table('admins')->where('email', 'superadmin@realsyscms.co.ke')->value('id');
        if (!$adminId) {
            $this->command->error('Admin user not found. Please run AdminSeeder first.');
            return;
        }

        // Get templates
        $homeTemplate = DB::table('templates')->where('slug', 'home')->first();
        $aboutTemplate = DB::table('templates')->where('slug', 'about')->first();
        $contactTemplate = DB::table('templates')->where('slug', 'contact')->first();

        if (!$homeTemplate || !$aboutTemplate || !$contactTemplate) {
            $this->command->error('Templates not found. Please run TemplateSeeder first.');
            return;
        }

        // Create pages
        $pages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'template_id' => $homeTemplate->id,
                'description' => 'Welcome to our website',
                'meta_title' => 'Home | Our Website',
                'meta_description' => 'Welcome to our website. Find the latest updates and information here.',
                'status' => 'published',
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'template_id' => $aboutTemplate->id,
                'description' => 'Learn more about us',
                'meta_title' => 'About Us | Our Website',
                'meta_description' => 'Learn more about our company, our mission, and our team.',
                'status' => 'published',
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'template_id' => $contactTemplate->id,
                'description' => 'Get in touch with us',
                'meta_title' => 'Contact Us | Our Website',
                'meta_description' => 'Contact us for any inquiries or support. We\'re here to help.',
                'status' => 'published',
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            // Delete existing page if it exists
            DB::table('pages')->where('slug', $page['slug'])->delete();
            
            // Create new page
            DB::table('pages')->insert($page);
        }
    }
}
