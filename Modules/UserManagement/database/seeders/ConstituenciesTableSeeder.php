<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConstituenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path to JSON file with county and constituency data
        $jsonPath = module_path('UserManagement', 'ModuleData/county.json');
        
        // Check if file exists
        if (!file_exists($jsonPath)) {
            $this->command->error('County JSON file not found at: ' . $jsonPath);
            return;
        }

        $jsonData = file_get_contents($jsonPath);
        $counties = json_decode($jsonData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Error parsing JSON file: ' . json_last_error_msg());
            return;
        }

        $this->command->info('Seeding constituencies...');
        $count = 0;

        foreach ($counties as $county) {
            $countyId = DB::table('counties')->where('code', $county['county_code'])->value('id');
            
            if (!$countyId) {
                $this->command->warn("County with code {$county['county_code']} not found in database. Skipping constituencies for {$county['county_name']}.");
                continue;
            }
            
            foreach ($county['constituencies'] as $index => $constituencyData) {
                $code = str_pad($county['county_code'], 2, '0', STR_PAD_LEFT) . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $constituency = [
                    'name' => $constituencyData['constituency_name'],
                    'code' => $code,
                    'county_id' => $countyId,
                    'created_at' => now(),
                   
                ];

                // Use firstOrCreate to avoid duplicates
                DB::table('constituencies')->updateOrInsert(
                    [
                        'name' => $constituency['name'],
                        'county_id' => $constituency['county_id']
                    ],
                    $constituency
                );
                
                $count++;
            }
        }

        $this->command->info("Seeded $count constituencies successfully.");
    }
}
