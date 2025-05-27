<?php

namespace Database\Seeders;

use App\Models\ContentType;
use App\Models\ContentTypeField;
use Illuminate\Database\Seeder;

class ContentTypeFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if content type fields already exist to prevent duplicate seeding
        if (ContentTypeField::count() > 0) {
            $this->command->info('Content type fields already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding content type fields...');

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

        // Basic Page Fields
        $fields = [
            // Basic Page Fields
            [
                'content_type_id' => $basicPage->id,
                'name' => 'Title',
                'key' => 'title',
                'type' => 'text',
                'required' => true,
                'description' => 'The page title',
                'order_index' => 0,
            ],
            [
                'content_type_id' => $basicPage->id,
                'name' => 'Body',
                'key' => 'body',
                'type' => 'rich_text',
                'required' => true,
                'description' => 'The main content of the page',
                'order_index' => 1,
            ],
            [
                'content_type_id' => $basicPage->id,
                'name' => 'Featured Image',
                'key' => 'featured_image',
                'type' => 'image',
                'required' => false,
                'description' => 'The main image for the page',
                'order_index' => 2,
            ],
            [
                'content_type_id' => $basicPage->id,
                'name' => 'Meta Description',
                'key' => 'meta_desc',
                'type' => 'textarea',
                'required' => false,
                'description' => 'SEO meta description',
                'order_index' => 3,
            ],
            
            // Blog Post Fields
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Title',
                'key' => 'title',
                'type' => 'text',
                'required' => true,
                'description' => 'The post title',
                'order_index' => 0,
            ],
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Body',
                'key' => 'body',
                'type' => 'rich_text',
                'required' => true,
                'description' => 'The main content of the post',
                'order_index' => 1,
            ],
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Featured Image',
                'key' => 'featured_image',
                'type' => 'image',
                'required' => true,
                'description' => 'The main image for the post',
                'order_index' => 2,
            ],
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Excerpt',
                'key' => 'excerpt',
                'type' => 'textarea',
                'required' => false,
                'description' => 'Short summary of the post',
                'order_index' => 3,
            ],
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Author',
                'key' => 'author',
                'type' => 'reference',
                'required' => true,
                'description' => 'The author of the post',
                'order_index' => 4,
            ],
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Publication Date',
                'key' => 'publish_date',
                'type' => 'date',
                'required' => true,
                'description' => 'When the post was published',
                'order_index' => 5,
            ],
            [
                'content_type_id' => $blogPost->id,
                'name' => 'Category',
                'key' => 'category',
                'type' => 'select',
                'required' => true,
                'description' => 'The category of the post',
                'order_index' => 6,
            ],
            
            // Team Member Fields
            [
                'content_type_id' => $teamMember->id,
                'name' => 'Name',
                'key' => 'name',
                'type' => 'text',
                'required' => true,
                'description' => 'Team member name',
                'order_index' => 0,
            ],
            [
                'content_type_id' => $teamMember->id,
                'name' => 'Position',
                'key' => 'position',
                'type' => 'text',
                'required' => true,
                'description' => 'Job position',
                'order_index' => 1,
            ],
            [
                'content_type_id' => $teamMember->id,
                'name' => 'Bio',
                'key' => 'bio',
                'type' => 'rich_text',
                'required' => true,
                'description' => 'Team member biography',
                'order_index' => 2,
            ],
            [
                'content_type_id' => $teamMember->id,
                'name' => 'Photo',
                'key' => 'photo',
                'type' => 'image',
                'required' => true,
                'description' => 'Team member photo',
                'order_index' => 3,
            ],
            [
                'content_type_id' => $teamMember->id,
                'name' => 'Department',
                'key' => 'department',
                'type' => 'select',
                'required' => true,
                'description' => 'Department the team member belongs to',
                'order_index' => 4,
            ],
            [
                'content_type_id' => $teamMember->id,
                'name' => 'Social Links',
                'key' => 'social_links',
                'type' => 'json',
                'required' => false,
                'description' => 'Links to social profiles',
                'order_index' => 5,
            ],
            
            // Service Fields
            [
                'content_type_id' => $service->id,
                'name' => 'Title',
                'key' => 'title',
                'type' => 'text',
                'required' => true,
                'description' => 'Service name',
                'order_index' => 0,
            ],
            [
                'content_type_id' => $service->id,
                'name' => 'Description',
                'key' => 'description',
                'type' => 'rich_text',
                'required' => true,
                'description' => 'Service description',
                'order_index' => 1,
            ],
            [
                'content_type_id' => $service->id,
                'name' => 'Icon',
                'key' => 'icon',
                'type' => 'text',
                'required' => false,
                'description' => 'Service icon (font awesome class)',
                'order_index' => 2,
            ],
            [
                'content_type_id' => $service->id,
                'name' => 'Features',
                'key' => 'features',
                'type' => 'json',
                'required' => false,
                'description' => 'List of features included in the service',
                'order_index' => 3,
            ],
            
            // Contact Fields
            [
                'content_type_id' => $contact->id,
                'name' => 'Title',
                'key' => 'title',
                'type' => 'text',
                'required' => true,
                'description' => 'Contact section title',
                'order_index' => 0,
            ],
            [
                'content_type_id' => $contact->id,
                'name' => 'Email',
                'key' => 'email',
                'type' => 'text',
                'required' => true,
                'description' => 'Contact email address',
                'order_index' => 1,
                'validation_rules' => 'email',
            ],
            [
                'content_type_id' => $contact->id,
                'name' => 'Phone',
                'key' => 'phone',
                'type' => 'text',
                'required' => false,
                'description' => 'Contact phone number',
                'order_index' => 2,
            ],
            [
                'content_type_id' => $contact->id,
                'name' => 'Address',
                'key' => 'address',
                'type' => 'textarea',
                'required' => false,
                'description' => 'Physical address',
                'order_index' => 3,
            ],
            [
                'content_type_id' => $contact->id,
                'name' => 'Map Coordinates',
                'key' => 'map_coordinates',
                'type' => 'text',
                'required' => false,
                'description' => 'Latitude,Longitude for map display',
                'order_index' => 4,
            ],
        ];

        foreach ($fields as $field) {
            ContentTypeField::create($field);
        }

        $this->command->info('Content type fields seeded successfully!');
    }
}
