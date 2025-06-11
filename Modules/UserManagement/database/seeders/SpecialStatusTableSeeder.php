<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserManagement\Entities\SpecialStatus;

class SpecialStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'name' => 'Youth',
                'code' => 'YOUTH',
                'description' => 'Kenyan youth aged 18-35 years',
                'created_at' => now(),
                
            ],
            [
                'name' => 'Person with Disabilities',
                'code' => 'PWD',
                'description' => 'Persons with various forms of disabilities',
                'created_at' => now(),
               
            ],
            [
                'name' => 'Women',
                'code' => 'WOMEN',
                'description' => 'Female gender category for special representation',
                'created_at' => now(),
               
            ],
            [
                'name' => 'Elder',
                'code' => 'ELDER',
                'description' => 'Senior citizens aged 60 years and above',
                'created_at' => now(),
            ],
            [
                'name' => 'Marginalized Community',
                'code' => 'MARGINALIZED',
                'description' => 'Members of historically marginalized communities',
                'created_at' => now(),
            ]
        ];

        foreach ($statuses as $status) {
            SpecialStatus::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
