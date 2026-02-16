<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenAILogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'report' => $this->whenLoaded('report', fn () => [
                'id' => $this->report->id,
                'title' => $this->report->title,
            ]),
            'model' => $this->model,
            'prompt_tokens' => $this->prompt_tokens,
            'completion_tokens' => $this->completion_tokens,
            'total_tokens' => $this->total_tokens,
            'cost_estimate' => $this->cost_estimate,
            'response_time_ms' => $this->response_time_ms,
            'status' => $this->status,
            'error_message' => $this->when($this->status !== 'success', $this->error_message),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
