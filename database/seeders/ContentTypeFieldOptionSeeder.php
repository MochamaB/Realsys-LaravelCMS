<?php

namespace Database\Seeders;

use App\Models\ContentTypeField;
use App\Models\ContentTypeFieldOption;
use Illuminate\Database\Seeder;

class ContentTypeFieldOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if content type field options already exist to prevent duplicate seeding
        if (ContentTypeFieldOption::count() > 0) {
            $this->command->info('Content type field options already exist. Skipping seeding.');
            return;
        }

        $this->command->info('Seeding content type field options...');

        // Get the fields that need options
        $blogCategoryField = ContentTypeField::where('key', 'category')
            ->whereHas('contentType', function($query) {
                $query->where('key', 'blog-post');
            })
            ->first();

        $teamDepartmentField = ContentTypeField::where('key', 'department')
            ->whereHas('contentType', function($query) {
                $query->where('key', 'team-member');
            })
            ->first();

        if (!$blogCategoryField || !$teamDepartmentField) {
            $this->command->error('Required fields not found. Please run ContentTypeFieldSeeder first.');
            return;
        }

        // Blog post categories
        $blogCategories = [
            [
                'field_id' => $blogCategoryField->id,
                'label' => 'Technology',
                'value' => 'technology',
                'order_index' => 0,
            ],
            [
                'field_id' => $blogCategoryField->id,
                'label' => 'Business',
                'value' => 'business',
                'order_index' => 1,
            ],
            [
                'field_id' => $blogCategoryField->id,
                'label' => 'Design',
                'value' => 'design',
                'order_index' => 2,
            ],
            [
                'field_id' => $blogCategoryField->id,
                'label' => 'Marketing',
                'value' => 'marketing',
                'order_index' => 3,
            ],
        ];

        // Team member departments
        $teamDepartments = [
            [
                'field_id' => $teamDepartmentField->id,
                'label' => 'Development',
                'value' => 'development',
                'order_index' => 0,
            ],
            [
                'field_id' => $teamDepartmentField->id,
                'label' => 'Marketing',
                'value' => 'marketing',
                'order_index' => 1,
            ],
            [
                'field_id' => $teamDepartmentField->id,
                'label' => 'Sales',
                'value' => 'sales',
                'order_index' => 2,
            ],
            [
                'field_id' => $teamDepartmentField->id,
                'label' => 'Management',
                'value' => 'management',
                'order_index' => 3,
            ],
        ];

        // Insert blog categories
        foreach ($blogCategories as $option) {
            ContentTypeFieldOption::create($option);
        }

        // Insert team departments
        foreach ($teamDepartments as $option) {
            ContentTypeFieldOption::create($option);
        }

        $this->command->info('Content type field options seeded successfully!');
    }
}
