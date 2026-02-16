<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ReportAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'file_size' => $this->file_size,
            'file_size_human' => $this->humanFileSize(),
            'mime_type' => $this->mime_type,
            'download_url' => URL::signedRoute('attachments.download', ['attachment' => $this->id], now()->addMinutes(30)),
            'view_url' => $this->isViewable() ? URL::signedRoute('attachments.view', ['attachment' => $this->id], now()->addMinutes(30)) : null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    protected function isViewable(): bool
    {
        $viewable = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

        return in_array($this->mime_type, $viewable);
    }

    protected function humanFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
