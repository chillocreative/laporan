<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MinitMesyuarat\StoreMinitMesyuaratRequest;
use App\Http\Requests\MinitMesyuarat\UpdateMinitMesyuaratRequest;
use App\Models\MinitMesyuarat;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MinitMesyuaratController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index(Request $request): JsonResponse
    {
        $query = MinitMesyuarat::query()->orderByDesc('bulan');

        if ($request->user()->hasAnyRole(['super-admin', 'admin'])) {
            $query->with('user:id,name');
        } else {
            $query->where('user_id', $request->user()->id);
        }

        $data = $query->get()->map(function ($record) {
            return array_merge($record->toArray(), [
                'download_url' => URL::temporarySignedRoute('minit-mesyuarat.download', now()->addMinutes(30), ['minitMesyuarat' => $record->id]),
                'view_url' => URL::temporarySignedRoute('minit-mesyuarat.view', now()->addMinutes(30), ['minitMesyuarat' => $record->id]),
            ]);
        });

        return response()->json(['data' => $data]);
    }

    public function store(StoreMinitMesyuaratRequest $request): JsonResponse
    {
        $userId = $request->targetUserId();
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $directory = "minit-mesyuarat/{$userId}";
        $filename = Str::uuid().'.'.$extension;

        $file->storeAs($directory, $filename, 'private');

        $record = MinitMesyuarat::create([
            'user_id' => $userId,
            'bulan' => $request->bulan,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $directory.'/'.$filename,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        $this->activityLogService->log('minit_mesyuarat_created', $record, 'Minit mesyuarat berjaya dimuat naik.');

        return response()->json([
            'message' => 'Minit mesyuarat berjaya dimuat naik.',
            'data' => $record,
        ], 201);
    }

    public function update(UpdateMinitMesyuaratRequest $request, MinitMesyuarat $minitMesyuarat): JsonResponse
    {
        $isOwnerOrAdmin = $minitMesyuarat->user_id === $request->user()->id
            || $request->user()->hasAnyRole(['super-admin', 'admin']);
        abort_if(! $isOwnerOrAdmin, 403, 'Tidak dibenarkan.');

        if ($request->hasFile('file')) {
            Storage::disk('private')->delete($minitMesyuarat->file_path);

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $directory = "minit-mesyuarat/{$request->user()->id}";
            $filename = Str::uuid().'.'.$extension;

            $file->storeAs($directory, $filename, 'private');

            $minitMesyuarat->update([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $directory.'/'.$filename,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        if ($request->filled('bulan')) {
            $minitMesyuarat->update(['bulan' => $request->bulan]);
        }

        $this->activityLogService->log('minit_mesyuarat_updated', $minitMesyuarat, 'Minit mesyuarat berjaya dikemas kini.');

        return response()->json([
            'message' => 'Minit mesyuarat berjaya dikemas kini.',
            'data' => $minitMesyuarat->fresh(),
        ]);
    }

    public function destroy(Request $request, MinitMesyuarat $minitMesyuarat): JsonResponse
    {
        $isOwnerOrAdmin = $minitMesyuarat->user_id === $request->user()->id
            || $request->user()->hasAnyRole(['super-admin', 'admin']);
        abort_if(! $isOwnerOrAdmin, 403, 'Tidak dibenarkan.');

        Storage::disk('private')->delete($minitMesyuarat->file_path);
        $minitMesyuarat->delete();

        $this->activityLogService->log('minit_mesyuarat_deleted', $minitMesyuarat, 'Minit mesyuarat berjaya dipadam.');

        return response()->json(['message' => 'Minit mesyuarat berjaya dipadam.']);
    }

    public function download(Request $request, MinitMesyuarat $minitMesyuarat): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Pautan muat turun tidak sah atau telah tamat tempoh.');
        }

        if ($request->user()?->id !== $minitMesyuarat->user_id && ! $request->user()?->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Anda tidak dibenarkan mengakses fail ini.');
        }

        return Storage::disk('private')->download($minitMesyuarat->file_path, $minitMesyuarat->original_name);
    }

    public function view(Request $request, MinitMesyuarat $minitMesyuarat): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Pautan tidak sah atau telah tamat tempoh.');
        }

        if ($request->user()?->id !== $minitMesyuarat->user_id && ! $request->user()?->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Anda tidak dibenarkan mengakses fail ini.');
        }

        return Storage::disk('private')->response(
            $minitMesyuarat->file_path,
            $minitMesyuarat->original_name,
            ['Content-Type' => $minitMesyuarat->mime_type]
        );
    }
}
