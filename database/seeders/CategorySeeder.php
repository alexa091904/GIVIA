<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Souvenirs', 'slug' => 'souvenirs', 'description' => 'Memorable keepsakes and travel mementos'],
            ['name' => 'Handmade Gifts', 'slug' => 'handmade-gifts', 'description' => 'Unique handcrafted items'],
            ['name' => 'Personalized Items', 'slug' => 'personalized-items', 'description' => 'Customizable gifts with personal touch'],
            ['name' => 'Birthday Gifts', 'slug' => 'birthday-gifts', 'description' => 'Perfect gifts for birthday celebrations'],
            ['name' => 'Wedding Gifts', 'slug' => 'wedding-gifts', 'description' => 'Romantic gifts for couples'],
            ['name' => 'Corporate Gifts', 'slug' => 'corporate-gifts', 'description' => 'Professional gifts for business'],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}