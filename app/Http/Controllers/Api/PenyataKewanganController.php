<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenyataKewangan\StorePenyataKewanganRequest;
use App\Http\Requests\PenyataKewangan\UpdatePenyataKewanganRequest;
use App\Models\PenyataKewangan;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenyataKewanganController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index(Request $request): JsonResponse
    {
        $query = PenyataKewangan::query()->orderByDesc('bulan');

        if ($request->user()->canViewAll()) {
            $query->with('user:id,name');
        } else {
            $query->where('user_id', $request->user()->id);
        }

        $records = $query->get()->map(function ($record) {
            $data = $record->toArray();
            $data['download_url'] = URL::temporarySignedRoute(
                'penyata-kewangan.download',
                now()->addMinutes(30),
                ['penyataKewangan' => $record->id]
            );
            $data['view_url'] = URL::temporarySignedRoute(
                'penyata-kewangan.view',
                now()->addMinutes(30),
                ['penyataKewangan' => $record->id]
            );

            return $data;
        });

        return response()->json(['data' => $records]);
    }

    public function store(StorePenyataKewanganRequest $request): JsonResponse
    {
        $userId = $request->targetUserId();
        $file = $request->file('file');
        $directory = "penyata-kewangan/{$userId}";
        $filename = Str::uuid().'.'.$file->extension();

        $path = $file->storeAs($directory, $filename, 'private');

        $record = PenyataKewangan::create([
            'user_id' => $userId,
            'bulan' => $request->bulan,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        $this->activityLogService->log('penyata_kewangan_created', $record, 'Penyata kewangan berjaya dimuat naik.');

        return response()->json([
            'message' => 'Penyata kewangan berjaya dimuat naik.',
            'data' => $record,
        ], 201);
    }

    public function update(UpdatePenyataKewanganRequest $request, PenyataKewangan $penyataKewangan): JsonResponse
    {
        $isOwnerOrAdmin = $penyataKewangan->user_id === $request->user()->id
            || $request->user()->hasAnyRole(['super-admin', 'admin']);
        abort_if(! $isOwnerOrAdmin, 403, 'Tidak dibenarkan.');

        if ($request->hasFile('file')) {
            Storage::disk('private')->delete($penyataKewangan->file_path);

            $file = $request->file('file');
            $directory = dirname($penyataKewangan->file_path);
            $filename = Str::uuid().'.'.$file->extension();
            $path = $file->storeAs($directory, $filename, 'private');

            $penyataKewangan->update([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        if ($request->filled('bulan')) {
            $penyataKewangan->update(['bulan' => $request->bulan]);
        }

        $this->activityLogService->log('penyata_kewangan_updated', $penyataKewangan, 'Penyata kewangan berjaya dikemas kini.');

        return response()->json([
            'message' => 'Penyata kewangan berjaya dikemas kini.',
            'data' => $penyataKewangan->fresh(),
        ]);
    }

    public function destroy(Request $request, PenyataKewangan $penyataKewangan): JsonResponse
    {
        $isOwnerOrAdmin = $penyataKewangan->user_id === $request->user()->id
            || $request->user()->hasAnyRole(['super-admin', 'admin']);
        abort_if(! $isOwnerOrAdmin, 403, 'Tidak dibenarkan.');

        Storage::disk('private')->delete($penyataKewangan->file_path);
        $penyataKewangan->delete();

        $this->activityLogService->log('penyata_kewangan_deleted', $penyataKewangan, 'Penyata kewangan berjaya dipadam.');

        return response()->json(['message' => 'Penyata kewangan berjaya dipadam.']);
    }

    public function download(Request $request, PenyataKewangan $penyataKewangan): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Pautan muat turun tidak sah atau telah tamat tempoh.');
        }

        if ($request->user()?->id !== $penyataKewangan->user_id && ! $request->user()?->canViewAll()) {
            abort(403, 'Tidak dibenarkan.');
        }

        return Storage::disk('private')->download(
            $penyataKewangan->file_path,
            $penyataKewangan->original_name
        );
    }

    public function view(Request $request, PenyataKewangan $penyataKewangan): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Pautan tidak sah atau telah tamat tempoh.');
        }

        if ($request->user()?->id !== $penyataKewangan->user_id && ! $request->user()?->canViewAll()) {
            abort(403, 'Tidak dibenarkan.');
        }

        return Storage::disk('private')->response(
            $penyataKewangan->file_path,
            $penyataKewangan->original_name,
            ['Content-Type' => $penyataKewangan->mime_type]
        );
    }
}
