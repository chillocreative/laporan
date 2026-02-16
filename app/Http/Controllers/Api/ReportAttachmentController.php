<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReportAttachment;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportAttachmentController extends Controller
{
    public function __construct(protected FileUploadService $fileUploadService) {}

    public function download(Request $request, ReportAttachment $attachment): StreamedResponse
    {
        // Verify signed URL
        if (! $request->hasValidSignature()) {
            abort(403, 'Pautan muat turun tidak sah atau telah tamat tempoh.');
        }

        // Verify user can view the parent report
        $this->authorize('view', $attachment->report);

        return Storage::disk('private')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    public function view(Request $request, ReportAttachment $attachment): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Pautan tidak sah atau telah tamat tempoh.');
        }

        $this->authorize('view', $attachment->report);

        return Storage::disk('private')->response(
            $attachment->file_path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type]
        );
    }

    public function destroy(ReportAttachment $attachment)
    {
        $this->authorize('update', $attachment->report);

        $this->fileUploadService->deleteAttachment($attachment);

        return response()->json(['message' => 'Lampiran berjaya dipadam.']);
    }
}
