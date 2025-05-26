<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing theme
        DB::table('themes')->where('slug', 'realsys')->delete();

        // Create theme
        DB::table('themes')->insert([
            'name' => 'RealSys',
            'slug' => 'realsys',
            'description' => 'Default RealSys theme',
            'version' => '1.0.0',
            'author' => 'RealSys',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
