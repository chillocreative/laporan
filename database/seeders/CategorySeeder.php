<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Infrastruktur',
            'Keselamatan Awam',
            'Alam Sekitar',
            'Kesihatan Awam',
            'Trafik & Pengangkutan',
            'Utiliti',
            'Komuniti',
            'Perkhidmatan Kerajaan',
            'Pendidikan',
            'Lain-lain',
        ];

        foreach ($categories as $i => $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['sort_order' => $i + 1]
            );
        }
    }
}
