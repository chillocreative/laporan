<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    protected int $maxFileSize = 5 * 1024 * 1024; // 5MB

    protected int $maxImageSize = 2 * 1024 * 1024; // 2MB

    public function uploadMultiple(Report $report, array $files): array
    {
        $attachments = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $attachments[] = $this->uploadSingle($report, $file);
            }
        }

        return $attachments;
    }

    public function uploadSingle(Report $report, UploadedFile $file): ReportAttachment
    {
        $this->validateFile($file);

        $directory = "report-attachments/{$report->id}";
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $filename, 'private');

        return $report->attachments()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    public function deleteAttachment(ReportAttachment $attachment): bool
    {
        Storage::disk('private')->delete($attachment->file_path);

        return $attachment->delete();
    }

    public function getFileUrl(ReportAttachment $attachment): string
    {
        return Storage::disk('private')->temporaryUrl(
            $attachment->file_path,
            now()->addMinutes(30)
        );
    }

    protected function validateFile(UploadedFile $file): void
    {
        // Validate real MIME type (not just extension)
        $realMimeType = $file->getMimeType();

        if (! in_array($realMimeType, $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                "File type '{$realMimeType}' is not allowed."
            );
        }

        $maxSize = str_starts_with($realMimeType, 'image/')
            ? $this->maxImageSize
            : $this->maxFileSize;

        if ($file->getSize() > $maxSize) {
            $maxMb = $maxSize / 1024 / 1024;
            throw new \InvalidArgumentException(
                "File size exceeds the maximum allowed size of {$maxMb}MB."
            );
        }
    }
}
