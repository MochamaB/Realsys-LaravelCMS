<?php

namespace Database\Seeders;

use App\Models\ContentFieldValue;
use App\Models\ContentItem;
use App\Models\ContentTypeField;
use Illuminate\Database\Seeder;

class ContentFieldValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if content field values already exist to prevent duplicate seeding
        if (ContentFieldValue::count() > 0) {
            $this->command->info('Content field values already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding content field values...');

        // Home Page
        $homePage = ContentItem::where('slug', 'home')->first();
        if ($homePage) {
            $this->seedBasicPageFields($homePage, [
                'title' => 'Welcome to RealSys',
                'body' => '<p>RealSys is a modern, fully-featured content management system designed for flexibility and ease of use. This homepage provides an overview of our key features and services.</p><p>Our platform is perfect for businesses looking to establish a strong online presence with minimal technical overhead.</p>',
                'featured_image' => 'home-header.jpg',
                'meta_desc' => 'Welcome to RealSys - A modern content management system for businesses and individuals.',
            ]);
        }

        // About Page
        $aboutPage = ContentItem::where('slug', 'about')->first();
        if ($aboutPage) {
            $this->seedBasicPageFields($aboutPage, [
                'title' => 'About Us',
                'body' => '<h2>Our Story</h2><p>RealSys was founded in 2020 with a mission to simplify content management for businesses of all sizes. Our team of dedicated professionals brings years of experience in web development, design, and digital marketing.</p><h2>Our Mission</h2><p>We believe that every business deserves a powerful, easy-to-use platform to share their story with the world. Our mission is to provide tools that empower our clients to succeed online.</p>',
                'featured_image' => 'about-header.jpg',
                'meta_desc' => 'Learn about RealSys - Our history, mission, and the team behind our success.',
            ]);
        }

        // Contact Page
        $contactPage = ContentItem::where('slug', 'contact')->first();
        if ($contactPage) {
            $this->seedBasicPageFields($contactPage, [
                'title' => 'Contact Us',
                'body' => '<p>We would love to hear from you! Please use the form below to get in touch with our team, or reach out directly using the contact information provided.</p>',
                'featured_image' => 'contact-header.jpg',
                'meta_desc' => 'Get in touch with the RealSys team - Contact information and inquiry form.',
            ]);
        }

        // Sample Post
        $samplePost = ContentItem::where('slug', 'sample-post')->first();
        if ($samplePost) {
            $this->seedBlogPostFields($samplePost, [
                'title' => 'Sample Post',
                'body' => '<p>This is a sample blog post that demonstrates the formatting capabilities of our content management system. When you create your own posts, you\'ll have access to a rich text editor that allows you to format text, add images, embed videos, and more.</p><p>You can organize your content into sections with headings, create lists, add blockquotes, and even insert code snippets if needed. The system also supports adding featured images that will appear at the top of your posts.</p>',
                'featured_image' => 'sample-post.jpg',
                'excerpt' => 'A demonstration of the blog formatting capabilities in RealSys CMS.',
                'author' => 'John Smith',
                'publish_date' => now()->subDays(3)->format('Y-m-d'),
                'category' => 'technology',
            ]);
        }

        // Team Members
        $johnSmith = ContentItem::where('slug', 'john-smith')->first();
        if ($johnSmith) {
            $this->seedTeamMemberFields($johnSmith, [
                'name' => 'John Smith',
                'position' => 'CEO & Founder',
                'bio' => '<p>John founded RealSys in 2020 after 15 years in the software industry. With a background in both development and business management, he leads our team with a focus on innovation and client satisfaction.</p>',
                'photo' => 'team-john.jpg',
                'department' => 'management',
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/johnsmith',
                    'twitter' => 'https://twitter.com/johnsmith',
                ]),
            ]);
        }

        $janeDoe = ContentItem::where('slug', 'jane-doe')->first();
        if ($janeDoe) {
            $this->seedTeamMemberFields($janeDoe, [
                'name' => 'Jane Doe',
                'position' => 'Lead Developer',
                'bio' => '<p>Jane oversees all technical aspects of our platform development. With expertise in multiple programming languages and frameworks, she ensures that RealSys remains cutting-edge and reliable.</p>',
                'photo' => 'team-jane.jpg',
                'department' => 'development',
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/janedoe',
                    'github' => 'https://github.com/janedoe',
                ]),
            ]);
        }

        // Services
        $webDev = ContentItem::where('slug', 'web-development')->first();
        if ($webDev) {
            $this->seedServiceFields($webDev, [
                'title' => 'Web Development',
                'description' => '<p>Our web development services cover everything from simple websites to complex web applications. We use the latest technologies and best practices to ensure your project is fast, secure, and user-friendly.</p>',
                'icon' => 'fa-code',
                'features' => json_encode([
                    'Custom website development',
                    'E-commerce solutions',
                    'Web application development',
                    'API integration',
                    'Database design and optimization',
                ]),
            ]);
        }

        $design = ContentItem::where('slug', 'ui-ux-design')->first();
        if ($design) {
            $this->seedServiceFields($design, [
                'title' => 'UI/UX Design',
                'description' => '<p>Our design team creates beautiful, intuitive user interfaces that enhance the user experience. We focus on creating designs that not only look great but also function seamlessly.</p>',
                'icon' => 'fa-paint-brush',
                'features' => json_encode([
                    'User interface design',
                    'User experience optimization',
                    'Wireframing and prototyping',
                    'Responsive design',
                    'Design system creation',
                ]),
            ]);
        }

        // Contact Item
        $contactItem = ContentItem::where('slug', 'contact-us')->first();
        if ($contactItem) {
            $this->seedContactFields($contactItem, [
                'title' => 'Get in Touch',
                'email' => 'info@realsys.example',
                'phone' => '+1 (555) 123-4567',
                'address' => "123 Business Street\nSuite 100\nTech City, TC 12345",
                'map_coordinates' => '40.7128,-74.0060',
            ]);
        }

        $this->command->info('Content field values seeded successfully!');
    }

    /**
     * Seed fields for a Basic Page content item.
     *
     * @param \App\Models\ContentItem $contentItem
     * @param array $values
     */
    private function seedBasicPageFields($contentItem, $values)
    {
        foreach ($values as $key => $value) {
            $field = ContentTypeField::where('content_type_id', $contentItem->content_type_id)
                ->where('key', $key)
                ->first();

            if ($field) {
                ContentFieldValue::create([
                    'content_item_id' => $contentItem->id,
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Seed fields for a Blog Post content item.
     *
     * @param \App\Models\ContentItem $contentItem
     * @param array $values
     */
    private function seedBlogPostFields($contentItem, $values)
    {
        foreach ($values as $key => $value) {
            $field = ContentTypeField::where('content_type_id', $contentItem->content_type_id)
                ->where('key', $key)
                ->first();

            if ($field) {
                ContentFieldValue::create([
                    'content_item_id' => $contentItem->id,
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Seed fields for a Team Member content item.
     *
     * @param \App\Models\ContentItem $contentItem
     * @param array $values
     */
    private function seedTeamMemberFields($contentItem, $values)
    {
        foreach ($values as $key => $value) {
            $field = ContentTypeField::where('content_type_id', $contentItem->content_type_id)
                ->where('key', $key)
                ->first();

            if ($field) {
                ContentFieldValue::create([
                    'content_item_id' => $contentItem->id,
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Seed fields for a Service content item.
     *
     * @param \App\Models\ContentItem $contentItem
     * @param array $values
     */
    private function seedServiceFields($contentItem, $values)
    {
        foreach ($values as $key => $value) {
            $field = ContentTypeField::where('content_type_id', $contentItem->content_type_id)
                ->where('key', $key)
                ->first();

            if ($field) {
                ContentFieldValue::create([
                    'content_item_id' => $contentItem->id,
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
    }

    /**
     * Seed fields for a Contact content item.
     *
     * @param \App\Models\ContentItem $contentItem
     * @param array $values
     */
    private function seedContactFields($contentItem, $values)
    {
        foreach ($values as $key => $value) {
            $field = ContentTypeField::where('content_type_id', $contentItem->content_type_id)
                ->where('key', $key)
                ->first();

            if ($field) {
                ContentFieldValue::create([
                    'content_item_id' => $contentItem->id,
                    'field_id' => $field->id,
                    'value' => $value,
                ]);
            }
        }
    }
}
