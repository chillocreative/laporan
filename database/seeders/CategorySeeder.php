<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Infrastructure',
            'Public Safety',
            'Environmental',
            'Public Health',
            'Traffic & Transport',
            'Utilities',
            'Community',
            'Government Services',
            'Education',
            'Other',
        ];

        foreach ($categories as $i => $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['sort_order' => $i + 1]
            );
        }
    }
}
