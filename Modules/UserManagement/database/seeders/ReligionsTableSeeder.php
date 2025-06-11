<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserManagement\Entities\Religion;

class ReligionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $religions = [
            [
                'name' => 'Christianity',
                'created_at' => now(),
            
            ],
            [
                'name' => 'Islam',
                'created_at' => now(),
            ],
            [
                'name' => 'Hinduism',
                'created_at' => now(),
            ],
            [
                'name' => 'Traditional African Religion',
                'created_at' => now(),
            ],
            [
                'name' => 'Sikhism',
            
            ],
            [
                'name' => 'Buddhism',
            
            ],
            [
                'name' => 'Baha\'i Faith',
            ],
            [
                'name' => 'Jainism',
            ],
            [
                'name' => 'None',
            ],
            [
                'name' => 'Other',
            ]
        ];

        foreach ($religions as $religion) {
            Religion::firstOrCreate(['name' => $religion['name']], $religion);
        }
    }
}
