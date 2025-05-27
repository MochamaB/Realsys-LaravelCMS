<?php

namespace Database\Seeders;

use App\Models\ContentType;
use App\Models\Widget;
use App\Models\WidgetContentQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class WidgetContentQuerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if widget content queries already exist
        if (WidgetContentQuery::count() > 0) {
            $this->command->info('Widget content queries already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding widget content queries...');

        // First, make sure required migrations exist
        if (!Schema::hasTable('widget_content_queries')) {
            $this->command->error('widget_content_queries table does not exist. Please run migrations first.');
            return;
        }

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

        // Get existing widgets (we'll assume they exist from previous seeders)
        $widgets = Widget::all();
        if ($widgets->isEmpty()) {
            $this->command->error('No widgets found. Please run WidgetSeeder first.');
            return;
        }

        // Create content queries for widgets
        $queries = [
            // Home page hero widget
            [
                'content_type_id' => $basicPage->id,
                'limit' => 1,
                'offset' => 0,
                'order_by' => 'id',
                'order_direction' => 'asc',
                'widget_name' => 'Home Hero', // This will be used to find matching widget
                'filters' => [
                    [
                        'field_key' => 'slug',
                        'operator' => 'equals',
                        'value' => 'home'
                    ]
                ]
            ],
            
            // Home content widget
            [
                'content_type_id' => $basicPage->id,
                'limit' => 1,
                'offset' => 0,
                'order_by' => 'id',
                'order_direction' => 'asc',
                'widget_name' => 'Home Content',
                'filters' => [
                    [
                        'field_key' => 'slug',
                        'operator' => 'equals',
                        'value' => 'home'
                    ]
                ]
            ],
            
            // About header widget
            [
                'content_type_id' => $basicPage->id,
                'limit' => 1,
                'offset' => 0,
                'order_by' => 'id',
                'order_direction' => 'asc',
                'widget_name' => 'About Header',
                'filters' => [
                    [
                        'field_key' => 'slug',
                        'operator' => 'equals',
                        'value' => 'about'
                    ]
                ]
            ],
            
            // Team members widget
            [
                'content_type_id' => $teamMember->id,
                'limit' => 10,
                'offset' => 0,
                'order_by' => 'title',
                'order_direction' => 'asc',
                'widget_name' => 'Team Members',
                'filters' => []
            ],
            
            // Services widget
            [
                'content_type_id' => $service->id,
                'limit' => 10,
                'offset' => 0,
                'order_by' => 'title',
                'order_direction' => 'asc',
                'widget_name' => 'Our Services',
                'filters' => []
            ],
            
            // Blog post widget
            [
                'content_type_id' => $blogPost->id,
                'limit' => 1,
                'offset' => 0,
                'order_by' => 'published_at',
                'order_direction' => 'desc',
                'widget_name' => 'Sample Blog Post',
                'filters' => [
                    [
                        'field_key' => 'slug',
                        'operator' => 'equals',
                        'value' => 'sample-post'
                    ]
                ]
            ],
            
            // Contact form widget
            [
                'content_type_id' => $contact->id,
                'limit' => 1,
                'offset' => 0,
                'order_by' => 'id',
                'order_direction' => 'asc',
                'widget_name' => 'Contact Form',
                'filters' => []
            ],
            
            // Recent posts widget
            [
                'content_type_id' => $blogPost->id,
                'limit' => 5,
                'offset' => 0,
                'order_by' => 'published_at',
                'order_direction' => 'desc',
                'widget_name' => 'Recent Posts',
                'filters' => [
                    [
                        'field_key' => 'status',
                        'operator' => 'equals',
                        'value' => 'published'
                    ]
                ]
            ]
        ];

        foreach ($queries as $queryData) {
            // Find matching widget
            $widgetName = $queryData['widget_name'];
            unset($queryData['widget_name']);
            $widget = $widgets->where('name', $widgetName)->first();
            
            if ($widget) {
                // Extract filters
                $filters = isset($queryData['filters']) ? $queryData['filters'] : [];
                unset($queryData['filters']);
                
                // Create the query
                $query = WidgetContentQuery::create($queryData);
                
                // Create filters for the query
                if ($filters && Schema::hasTable('widget_content_query_filters')) {
                    foreach ($filters as $filter) {
                        $query->filters()->create($filter);
                    }
                }
                
                // Update the widget with the query ID
                $widget->content_query_id = $query->id;
                $widget->save();
            }
        }

        $this->command->info('Widget content queries seeded successfully!');
    }
}
