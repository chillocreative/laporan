<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyata_kewangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('bulan');
            $table->string('original_name');
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 100);
            $table->timestamps();

            $table->unique(['user_id', 'bulan']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyata_kewangans');
    }
};
