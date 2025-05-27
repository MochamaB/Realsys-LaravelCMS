<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if content types already exist to prevent duplicate seeding
        if (ContentType::count() > 0) {
            $this->command->info('Content types already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding content types...');

        $contentTypes = [
            [
                'name' => 'Basic Page',
                'key' => 'basic-page',
                'description' => 'Simple content pages like Home and About',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Blog Post',
                'key' => 'blog-post',
                'description' => 'Blog articles with author and date',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Team Member',
                'key' => 'team-member',
                'description' => 'Staff profiles for the About page',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Service',
                'key' => 'service',
                'description' => 'Service offerings for the About page',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Contact',
                'key' => 'contact',
                'description' => 'Contact information',
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        foreach ($contentTypes as $contentType) {
            ContentType::create($contentType);
        }

        $this->command->info('Content types seeded successfully!');
    }
}
