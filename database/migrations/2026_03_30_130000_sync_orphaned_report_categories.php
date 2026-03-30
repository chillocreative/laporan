<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get current active category names
        $activeCategories = DB::table('categories')->pluck('name')->toArray();

        if (!empty($activeCategories)) {
            // Update any reports whose category no longer exists to 'Lain-lain'
            DB::table('reports')
                ->whereNotIn('category', $activeCategories)
                ->update(['category' => 'Lain-lain']);
        }
    }

    public function down(): void
    {
        // Not reversible - old category names are unknown
    }
};
