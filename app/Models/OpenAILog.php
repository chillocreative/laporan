<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenAILog extends Model
{
    public $timestamps = false;

    protected $table = 'openai_logs';

    protected $fillable = [
        'user_id',
        'report_id',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_estimate',
        'response_time_ms',
        'status',
        'error_message',
        'raw_response',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'cost_estimate' => 'decimal:6',
            'response_time_ms' => 'integer',
            'raw_response' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
