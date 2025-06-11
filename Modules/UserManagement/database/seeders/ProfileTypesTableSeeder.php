<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfileTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $profileTypes = [
            [
                'name' => 'Party Member',
                'code' => 'PM',
                'description' => 'Standard party member with basic privileges',
                'created_at' => now(),
            ],
            [
                'name' => 'Party Official',
                'code' => 'PO',
                'description' => 'Elected or appointed party official',
                'created_at' => now(),
            ],
            [
                'name' => 'Party Staff',
                'code' => 'PS',
                'description' => 'Party secretariat staff member',
                'created_at' => now(),
            ],
            [
                'name' => 'Volunteer',
                'code' => 'VOLUNTEER',
                'description' => 'Volunteer for the party',
                'created_at' => now(),
            ],
            [
                'name' => 'Party Aspirant',
                'code' => 'PA',
                'description' => 'Member seeking party nomination for elective position',
                'created_at' => now(),
            ],
            [
                'name' => 'Voter',
                'code' => 'VOTER',
                'description' => 'Voter who wants to see party information',
                'created_at' => now(),
            ]
        ];

        foreach ($profileTypes as $type) {
            DB::table('profile_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('Profile types seeded successfully.');
    }
}
