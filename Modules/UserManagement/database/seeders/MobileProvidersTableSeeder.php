<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserManagement\Entities\MobileProvider;

class MobileProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = [
            [
                'name' => 'Safaricom',
                'prefix' => '07',
                'created_at' => now(),
                
            ],
            [
                'name' => 'Airtel',
                'prefix' => '07',
                'created_at' => now(),
                
            ],
            [
                'name' => 'Telkom',
                'prefix' => '07',
                'created_at' => now(),
                
            ],
            [
                'name' => 'Equitel',
                'prefix' => '07',
                'created_at' => now(),
                
            ],
            [
                'name' => 'Faiba 4G',
                'prefix' => '07',
                'created_at' => now(),
                
            ],
            [
                'name' => 'Other',
                'prefix' => '07',
                'created_at' => now(),
                
            ]
        ];

        foreach ($providers as $provider) {
            MobileProvider::firstOrCreate(['name' => $provider['name']], $provider);
        }
    }
}
