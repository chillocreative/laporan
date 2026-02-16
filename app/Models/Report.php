<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'location',
        'description',
        'incident_date',
        'status',
        'ai_summary',
        'ai_risk_level',
        'ai_urgency_score',
        'ai_recommended_action',
        'ai_related_issue',
        'ai_spam_probability',
        'ai_confidence_score',
        'ai_raw_response',
        'ai_analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'ai_risk_level' => RiskLevel::class,
            'ai_urgency_score' => 'integer',
            'ai_spam_probability' => 'decimal:4',
            'ai_confidence_score' => 'decimal:4',
            'ai_raw_response' => 'array',
            'ai_analyzed_at' => 'datetime',
            'incident_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }

    public function openaiLogs(): HasMany
    {
        return $this->hasMany(OpenAILog::class);
    }

    public function isAnalyzed(): bool
    {
        return $this->ai_analyzed_at !== null;
    }
}
