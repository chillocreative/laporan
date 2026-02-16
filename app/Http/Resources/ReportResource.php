<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'title' => $this->title,
            'category' => $this->category,
            'description' => $this->description,
            'incident_date' => $this->incident_date?->format('Y-m-d'),
            'attachments' => ReportAttachmentResource::collection($this->whenLoaded('attachments')),
            'ai_analysis' => $this->when($this->ai_analyzed_at !== null, fn () => [
                'summary' => $this->ai_summary,
                'risk_level' => $this->ai_risk_level ? [
                    'value' => $this->ai_risk_level->value,
                    'label' => $this->ai_risk_level->label(),
                    'color' => $this->ai_risk_level->color(),
                ] : null,
                'urgency_score' => $this->ai_urgency_score,
                'recommended_action' => $this->ai_recommended_action,
                'related_issue' => $this->ai_related_issue,
                'spam_probability' => $this->ai_spam_probability,
                'confidence_score' => $this->ai_confidence_score,
                'analyzed_at' => $this->ai_analyzed_at?->toISOString(),
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
