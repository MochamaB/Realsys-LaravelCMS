<?php

namespace Database\Seeders;

use App\Models\ContentItem;
use App\Models\ContentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if content items already exist to prevent duplicate seeding
        if (ContentItem::count() > 0) {
            $this->command->info('Content items already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding content items...');

        // Get content types
        $basicPage = ContentType::where('key', 'basic-page')->first();
        $blogPost = ContentType::where('key', 'blog-post')->first();
        $teamMember = ContentType::where('key', 'team-member')->first();
        $service = ContentType::where('key', 'service')->first();
        $contact = ContentType::where('key', 'contact')->first();

        if (!$basicPage || !$blogPost || !$teamMember || !$service || !$contact) {
            $this->command->error('Content types not found. Please run ContentTypeSeeder first.');
            return;
        }

        // Basic Pages
        $basicPages = [
            [
                'content_type_id' => $basicPage->id,
                'title' => 'Home',
                'slug' => 'home',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'content_type_id' => $basicPage->id,
                'title' => 'About Us',
                'slug' => 'about',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'content_type_id' => $basicPage->id,
                'title' => 'Contact',
                'slug' => 'contact',
                'status' => 'published',
                'published_at' => now(),
            ]
        ];

        // Blog Posts
        $blogPosts = [
            [
                'content_type_id' => $blogPost->id,
                'title' => 'Sample Post',
                'slug' => 'sample-post',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'content_type_id' => $blogPost->id,
                'title' => 'The Future of Web Development',
                'slug' => 'future-web-development',
                'status' => 'published',
                'published_at' => now()->subDays(7),
            ],
            [
                'content_type_id' => $blogPost->id,
                'title' => 'Design Trends for 2025',
                'slug' => 'design-trends-2025',
                'status' => 'published',
                'published_at' => now()->subDays(14),
            ],
        ];

        // Team Members
        $teamMembers = [
            [
                'content_type_id' => $teamMember->id,
                'title' => 'John Smith',
                'slug' => 'john-smith',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'content_type_id' => $teamMember->id,
                'title' => 'Jane Doe',
                'slug' => 'jane-doe',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'content_type_id' => $teamMember->id,
                'title' => 'Mike Johnson',
                'slug' => 'mike-johnson',
                'status' => 'published',
                'published_at' => now(),
            ],
        ];

        // Services
        $services = [
            [
                'content_type_id' => $service->id,
                'title' => 'Web Development',
                'slug' => 'web-development',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'content_type_id' => $service->id,
                'title' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'content_type_id' => $service->id,
                'title' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'status' => 'published',
                'published_at' => now(),
            ],
        ];

        // Contact
        $contactItems = [
            [
                'content_type_id' => $contact->id,
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'status' => 'published',
                'published_at' => now(),
            ],
        ];

        // Create all content items
        foreach (array_merge($basicPages, $blogPosts, $teamMembers, $services, $contactItems) as $item) {
            ContentItem::create($item);
        }

        $this->command->info('Content items seeded successfully!');
    }
}
