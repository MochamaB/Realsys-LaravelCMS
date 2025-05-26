<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WidgetTypeFieldSeeder extends Seeder
{
    public function run(): void
    {
        // Get widget types
        $heroType = DB::table('widget_types')->where('slug', 'hero-header')->first();
        $postListType = DB::table('widget_types')->where('slug', 'post-list')->first();
        $contactFormType = DB::table('widget_types')->where('slug', 'contact-form')->first();

        if (!$heroType || !$postListType || !$contactFormType) {
            $this->command->error('Widget types not found. Please run WidgetTypeSeeder first.');
            return;
        }

        // Create fields for hero header widget
        $heroFields = [
            [
                'widget_type_id' => $heroType->id,
                'name' => 'title',
                'label' => 'Title',
                'field_type' => 'text',
                'is_required' => true,
                'order_index' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_type_id' => $heroType->id,
                'name' => 'subtitle',
                'label' => 'Subtitle',
                'field_type' => 'text',
                'is_required' => false,
                'order_index' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_type_id' => $heroType->id,
                'name' => 'background_image',
                'label' => 'Background Image',
                'field_type' => 'image',
                'is_required' => true,
                'order_index' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Create fields for post list widget
        $postListFields = [
            [
                'widget_type_id' => $postListType->id,
                'name' => 'title',
                'label' => 'Section Title',
                'field_type' => 'text',
                'is_required' => true,
                'order_index' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_type_id' => $postListType->id,
                'name' => 'posts_per_page',
                'label' => 'Posts Per Page',
                'field_type' => 'number',
                'is_required' => true,
                'default_value' => '6',
                'order_index' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Create fields for contact form widget
        $contactFormFields = [
            [
                'widget_type_id' => $contactFormType->id,
                'name' => 'title',
                'label' => 'Form Title',
                'field_type' => 'text',
                'is_required' => true,
                'order_index' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'widget_type_id' => $contactFormType->id,
                'name' => 'form_id',
                'label' => 'Form ID',
                'field_type' => 'number',
                'is_required' => true,
                'order_index' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert all fields
        foreach (array_merge($heroFields, $postListFields, $contactFormFields) as $field) {
            DB::table('widget_type_fields')->insert($field);
        }
    }
}
