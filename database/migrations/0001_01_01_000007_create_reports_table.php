<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 100);
            $table->string('location');
            $table->text('description');
            $table->date('incident_date');
            $table->enum('status', [
                'pending', 'under_review', 'in_progress', 'resolved', 'rejected',
            ])->default('pending');

            // AI Analysis Fields
            $table->text('ai_summary')->nullable();
            $table->enum('ai_risk_level', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->unsignedTinyInteger('ai_urgency_score')->nullable();
            $table->text('ai_recommended_action')->nullable();
            $table->text('ai_related_issue')->nullable();
            $table->decimal('ai_spam_probability', 5, 4)->nullable();
            $table->decimal('ai_confidence_score', 5, 4)->nullable();
            $table->json('ai_raw_response')->nullable();
            $table->timestamp('ai_analyzed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category');
            $table->index('ai_risk_level');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
