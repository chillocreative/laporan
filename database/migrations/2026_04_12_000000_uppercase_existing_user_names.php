<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // mb_strtoupper handles UTF-8 names that SQL UPPER may butcher.
        DB::table('users')->lazyById()->each(function ($user): void {
            $upper = mb_strtoupper(trim((string) $user->name), 'UTF-8');

            if ($upper !== $user->name) {
                DB::table('users')->where('id', $user->id)->update(['name' => $upper]);
            }
        });
    }

    public function down(): void
    {
        // Original casing is not recoverable.
    }
};
