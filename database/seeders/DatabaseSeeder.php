<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First create roles and users
        $this->call(RolesSeeder::class);
        $this->call(AdminSeeder::class);

       $this->call(ThemeSeeder::class);
       
    }
}
