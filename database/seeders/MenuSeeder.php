<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menus and menu items for clean seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        MenuItem::truncate();
        Menu::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Create Header Menu
        // 'location' is a logical identifier used by the theme component,
        // not a file path or section name
        $headerMenu = Menu::create([
            'name' => 'Header Navigation', 
            'slug' => 'header-nav',
            'location' => 'header',  // This matches the location parameter in <x-theme-navigation location="header" />
            'description' => 'Main navigation displayed in the header',
            'is_active' => true
        ]);
        
        // Create Footer Menu
        $footerMenu = Menu::create([
            'name' => 'Footer Navigation', 
            'slug' => 'footer-nav',
            'location' => 'footer',  // This matches the location parameter in <x-theme-navigation location="footer" />
            'description' => 'Secondary navigation displayed in the footer',
            'is_active' => true
        ]);
        
        // Add items to Header Menu
        $home = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => null,
            'label' => 'Home',
            'link_type' => 'url',
            'url' => '/',
            'target' => '_self',
            'position' => 1,
            'is_active' => true,
        ]);
        
        $about = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => null,
            'label' => 'About',
            'link_type' => 'url',
            'url' => '/about',
            'target' => '_self',
            'position' => 2,
            'is_active' => true,
        ]);
        
        $services = MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => null,
            'label' => 'Services',
            'link_type' => 'url',
            'url' => '/services',
            'target' => '_self',
            'position' => 3,
            'is_active' => true,
        ]);
        
        // Add dropdown menu items under Services
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => $services->id,
            'label' => 'Web Development',
            'link_type' => 'url',
            'url' => '/services/web-development',
            'target' => '_self',
            'position' => 1,
            'is_active' => true,
        ]);
        
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => $services->id,
            'label' => 'Mobile Apps',
            'link_type' => 'url',
            'url' => '/services/mobile-apps',
            'target' => '_self',
            'position' => 2,
            'is_active' => true,
        ]);
        
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => null,
            'label' => 'Contact',
            'link_type' => 'url',
            'url' => '/contact',
            'target' => '_self',
            'position' => 4,
            'is_active' => true,
        ]);
        
        // Add items to Footer Menu
        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => null,
            'label' => 'Privacy Policy',
            'link_type' => 'url',
            'url' => '/privacy',
            'target' => '_self',
            'position' => 1,
            'is_active' => true,
        ]);
        
        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => null,
            'label' => 'Terms of Service',
            'link_type' => 'url',
            'url' => '/terms',
            'target' => '_self',
            'position' => 2,
            'is_active' => true,
        ]);
        
        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'parent_id' => null,
            'label' => 'Contact Us',
            'link_type' => 'url',
            'url' => '/contact',
            'target' => '_self',
            'position' => 3,
            'is_active' => true,
        ]);
        
        // Example of a menu item with section-based navigation (for one-page themes)
        MenuItem::create([
            'menu_id' => $headerMenu->id,
            'parent_id' => null,
            'label' => 'Features',
            'link_type' => 'section',
            'section_id' => 'features-section',
            'target' => '_self',
            'position' => 5,
            'is_active' => true,
        ]);
    }
}
