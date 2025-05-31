<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if seeder has already been run
        if (Role::count() > 0) {
            $this->command->info('Roles already exist. Skipping RolesSeeder...');
            return;
        }
        
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions - grouped by feature
        $this->createThemePermissions();
        $this->createContentPermissions();
        $this->createPagePermissions();
        $this->createWidgetPermissions();
        $this->createUserPermissions();
        $this->createSystemPermissions();
        
        // Create roles and assign permissions
        $this->createRoles();
        
        // Assign roles to admins
        $this->assignRolesToAdmins();
        
        $this->command->info('Roles and permissions created successfully.');
    }
    
    
    /**
     * Create theme-related permissions
     */
    private function createThemePermissions(): void
    {
        $permissions = [
            'view themes',
            'create themes',
            'edit themes',
            'delete themes',
            'activate themes',
            'view templates',
            'create templates',
            'edit templates',
            'delete templates',
        ];
        
        $this->createPermissions($permissions);
    }
    
    /**
     * Create content-related permissions
     */
    private function createContentPermissions(): void
    {
        $permissions = [
            'view content types',
            'create content types',
            'edit content types',
            'delete content types',
            'view content type fields',
            'create content type fields',
            'edit content type fields',
            'delete content type fields',
            'view content items',
            'create content items',
            'edit content items',
            'delete content items',
            'publish content items',
            'view others content',
            'edit others content',
        ];
        
        $this->createPermissions($permissions);
    }
    
    /**
     * Create page-related permissions
     */
    private function createPagePermissions(): void
    {
        $permissions = [
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'view page sections',
            'edit page sections',
            'set homepage',
        ];
        
        $this->createPermissions($permissions);
    }
    
    /**
     * Create widget-related permissions
     */
    private function createWidgetPermissions(): void
    {
        $permissions = [
            'view widgets',
            'create widgets',
            'edit widgets',
            'delete widgets',
            'view widget fields',
            'edit widget fields',
            'configure widgets',
            'place widgets',
        ];
        
        $this->createPermissions($permissions);
    }
    
    /**
     * Create user/admin-related permissions
     */
    private function createUserPermissions(): void
    {
        $permissions = [
            'view admins',
            'create admins',
            'edit admins',
            'delete admins',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',
        ];
        
        $this->createPermissions($permissions);
    }
    
    /**
     * Create system-related permissions
     */
    private function createSystemPermissions(): void
    {
        $permissions = [
            'access settings',
            'edit settings',
            'view system logs',
            'run maintenance',
        ];
        
        $this->createPermissions($permissions);
    }
    
    /**
     * Helper method to create permissions
     */
    private function createPermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }
    }
    
    /**
     * Create roles and assign permissions
     */
    private function createRoles(): void
    {
        // Super Admin role - gets all permissions
        $superAdminRole = Role::findOrCreate('super-admin', 'admin');
        $superAdminRole->givePermissionTo(Permission::all());
        
        // Administrator role - can manage most things but not system settings
        $adminRole = Role::findOrCreate('administrator', 'admin');
        $adminRole->givePermissionTo([
            'view themes',
            'edit themes',
            'activate themes',
            'view templates',
            'edit templates',
            'view content types',
            'create content types',
            'edit content types',
            'view content type fields',
            'create content type fields',
            'edit content type fields',
            'view content items',
            'create content items',
            'edit content items',
            'delete content items',
            'publish content items',
            'view others content',
            'edit others content',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'view page sections',
            'edit page sections',
            'set homepage',
            'view widgets',
            'edit widgets',
            'configure widgets',
            'place widgets',
            'view admins',
        ]);
        
        // Editor role - manages content but not structure
        $editorRole = Role::findOrCreate('editor', 'admin');
        $editorRole->givePermissionTo([
            'view content items',
            'create content items',
            'edit content items',
            'delete content items',
            'publish content items',
            'view others content',
            'edit others content',
            'view pages',
            'create pages',
            'edit pages',
            'publish pages',
            'view page sections',
            'edit page sections',
            'view widgets',
            'configure widgets',
            'place widgets',
        ]);
        
        // Content Creator - create content but not publish
        $contentCreatorRole = Role::findOrCreate('content-creator', 'admin');
        $contentCreatorRole->givePermissionTo([
            'view content items',
            'create content items',
            'edit content items',
            'view pages',
            'create pages',
            'edit pages',
            'view page sections',
            'view widgets',
            'place widgets',
        ]);
    }
    
    /**
     * Assign roles to admin users
     */
    private function assignRolesToAdmins(): void
    {
        // Assign super-admin role to super admin users
        $superAdmins = Admin::where('is_super_admin', true)->get();
        foreach ($superAdmins as $admin) {
            $admin->assignRole('super-admin');
        }
    }
}