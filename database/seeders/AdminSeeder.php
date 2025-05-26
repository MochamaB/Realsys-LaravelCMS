<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin exists
        $admin = Admin::query()->firstOrNew(
            ['email' => 'superadmin@realsyscms.co.ke'],
            [
                'first_name' => 'super',
                'last_name' => 'admin',
                'password' => Hash::make('Brayan!9900731'),
                'is_super_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Save if it's a new instance
        if (!$admin->exists) {
            $admin->save();
        }

        // Sync the superadmin role (removes other roles and assigns superadmin)
        $admin->syncRoles(['superadmin']);
    }
}
