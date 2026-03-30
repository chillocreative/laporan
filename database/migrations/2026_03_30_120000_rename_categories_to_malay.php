<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $mapping = [
        'Infrastructure'     => 'Infrastruktur',
        'Public Safety'      => 'Keselamatan Awam',
        'Environmental'      => 'Alam Sekitar',
        'Public Health'      => 'Kesihatan Awam',
        'Traffic & Transport' => 'Trafik & Pengangkutan',
        'Utilities'          => 'Utiliti',
        'Community'          => 'Komuniti',
        'Government Services' => 'Perkhidmatan Kerajaan',
        'Education'          => 'Pendidikan',
        'Other'              => 'Lain-lain',
    ];

    public function up(): void
    {
        foreach ($this->mapping as $english => $malay) {
            DB::table('categories')->where('name', $english)->update(['name' => $malay]);
            DB::table('reports')->where('category', $english)->update(['category' => $malay]);
        }
    }

    public function down(): void
    {
        foreach ($this->mapping as $english => $malay) {
            DB::table('categories')->where('name', $malay)->update(['name' => $english]);
            DB::table('reports')->where('category', $malay)->update(['category' => $english]);
        }
    }
};
