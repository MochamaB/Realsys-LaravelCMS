<?php

namespace Modules\UserManagement\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Begin transaction to ensure all operations succeed or fail together
        DB::beginTransaction();

        try {
            // Create permissions
            $memberPermissions = [
                'view profile',
                'edit profile',
                'view events',
                'register for events',
                'access member resources',
            ];
            
            $volunteerPermissions = [
                'view profile',
                'edit profile',
                'view events',
                'register for events',
                'access member resources',
                'volunteer for tasks',
                'track volunteer hours',
            ];
            
            $voterPermissions = [
                'view profile',
                'edit profile',
                'view events',
                'access public resources',
            ];
            
            // Create all permissions
            $allPermissions = array_unique(array_merge($memberPermissions, $volunteerPermissions, $voterPermissions));
            foreach ($allPermissions as $permission) {
                Permission::findOrCreate($permission);
            }
            
            // Create roles and assign permissions
            $partyMemberRole = Role::findOrCreate('party_member','web');
            foreach ($memberPermissions as $permission) {
                $partyMemberRole->givePermissionTo($permission);
            }
            
            $volunteerRole = Role::findOrCreate('volunteer','web');
            foreach ($volunteerPermissions as $permission) {
                $volunteerRole->givePermissionTo($permission);
            }
            
            $voterRole = Role::findOrCreate('voter','web');
            foreach ($voterPermissions as $permission) {
                $voterRole->givePermissionTo($permission);
            }
            
           
            
            // Commit transaction
            DB::commit();
            
            $this->command->info('Roles and permissions created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Failed to create roles and permissions: ' . $e->getMessage());
        }
    }
}
