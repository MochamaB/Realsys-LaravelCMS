<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path to JSON file with county, constituency, and ward data
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

        $this->command->info('Seeding wards...');
        $count = 0;

        foreach ($counties as $county) { // ← this was missing
            $countyId = DB::table('counties')->where('code', $county['county_code'])->value('id');
            
            if (!$countyId) {
                $this->command->warn("County with code {$county['county_code']} not found in database. Skipping wards for {$county['county_name']}.");
                continue;
            }
        
            foreach ($county['constituencies'] as $cIndex => $constituencyData) {
                $constituencyName = $constituencyData['constituency_name'];
        
                $constituencyId = DB::table('constituencies')
                    ->where('name', $constituencyName)
                    ->where('county_id', $countyId)
                    ->value('id');
        
                if (!$constituencyId) {
                    $this->command->warn("Constituency '$constituencyName' not found in database. Skipping its wards.");
                    continue;
                }
        
                foreach ($constituencyData['wards'] as $wIndex => $wardName) {
                    $code = str_pad($county['county_code'], 2, '0', STR_PAD_LEFT)
                          . str_pad($cIndex + 1, 2, '0', STR_PAD_LEFT)
                          . str_pad($wIndex + 1, 2, '0', STR_PAD_LEFT); // e.g. 470401
        
                    $ward = [
                        'name' => trim($wardName),
                        'code' => $code,
                        'constituency_id' => $constituencyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
        
                    DB::table('wards')->updateOrInsert(
                        [
                            'name' => $ward['name'],
                            'constituency_id' => $ward['constituency_id']
                        ],
                        $ward
                    );
        
                    $count++;
                }
            }
        }
        
        

        $this->command->info("Seeded $count wards successfully.");
    }
}
