<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserManagement\Entities\Ethnicity;

class EthnicitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ethnicities = [
            'Kikuyu', 'Luhya', 'Kalenjin', 'Luo', 'Kamba', 
            'Kisii', 'Mijikenda', 'Meru', 'Turkana', 'Maasai',
            'Teso', 'Embu', 'Taita', 'Kuria', 'Samburu',
            'Tharaka', 'Mbeere', 'Borana', 'Basuba', 'Swahili',
            'Gabra', 'Orma', 'Rendille', 'Somali', 'Gosha',
            'Burji', 'Daasanach', 'El Molo', 'Konso', 'Sakuye',
            'Galjeel', 'Ajuran', 'Degodia', 'Ogaden', 'Murulle',
            'Pokot', 'Endorois', 'Nubi', 'Yaaku', 'Bajuni',
            'Dahalo', 'Taveta', 'Pokomo', 'Boni', 'Sabaot',
            'Ilchamus', 'Sengwer'
        ];

        foreach ($ethnicities as $ethnicity) {
            Ethnicity::firstOrCreate(['name' => $ethnicity], [
                'name' => $ethnicity,
                'code' => $ethnicity,
                'created_at' => now(),
                
            ]);
        }
    }
}
