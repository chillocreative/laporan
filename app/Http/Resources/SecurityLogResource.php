<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecurityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'event_type' => [
                'value' => $this->event_type->value,
                'label' => $this->event_type->label(),
            ],
            'severity' => [
                'value' => $this->severity->value,
                'label' => $this->severity->label(),
            ],
            'description' => $this->description,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
