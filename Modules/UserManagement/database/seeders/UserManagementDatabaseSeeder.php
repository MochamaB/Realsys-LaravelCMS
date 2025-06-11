<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;

class UserManagementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            EthnicitiesTableSeeder::class,
            SpecialStatusTableSeeder::class,
            MobileProvidersTableSeeder::class,
            ReligionsTableSeeder::class,
            CountiesTableSeeder::class,
            ConstituenciesTableSeeder::class,
            WardTableSeeder::class,
            ProfileTypesTableSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
