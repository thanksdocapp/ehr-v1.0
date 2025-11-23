<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class NavbarBackgroundImageSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        SiteSetting::create([
            'key' => 'navbar_background_image',
            'label' => 'Navbar Background Image',
            'type' => 'image',
            'group' => 'appearance',
            'value' => null,
            'description' => 'Background image for the navigation bar across all pages',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }
}
