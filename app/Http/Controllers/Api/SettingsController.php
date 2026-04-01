<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function __construct(protected SettingsService $settingsService) {}

    public function index(): JsonResponse
    {
        $this->authorize('view', \App\Models\Setting::class);

        return response()->json([
            'data' => $this->settingsService->getAllSettings(),
        ]);
    }

    public function update(UpdateSettingsRequest $request, string $group): JsonResponse
    {
        match ($group) {
            'general' => $this->settingsService->updateGeneral($request->validated()),
            'openai' => $this->settingsService->updateOpenAI($request->validated()),
            'captcha' => $this->settingsService->updateCaptcha($request->validated()),
            'smtp' => $this->settingsService->updateSmtp($request->validated()),
            'branding' => $this->settingsService->updateLogo($request->file('logo')),
            default => abort(404, 'Kumpulan tetapan tidak ditemui.'),
        };

        return response()->json([
            'message' => 'Tetapan berjaya dikemas kini.',
            'data' => $this->settingsService->getAllSettings(),
        ]);
    }

    public function sendTestEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Re-apply SMTP config to pick up latest saved settings
        $this->settingsService->applySmtpConfig();

        $recipientEmail = $request->input('email');
        $fromName = config('mail.from.name', 'Sistem Pelaporan');

        Mail::raw(
            "Ini adalah e-mel ujian daripada {$fromName}.\n\nTetapan SMTP anda berfungsi dengan betul.\n\nTarikh & Masa: ".now()->format('d/m/Y H:i:s'),
            function ($message) use ($recipientEmail, $fromName) {
                $message->to($recipientEmail)
                    ->subject("[{$fromName}] E-mel Ujian SMTP");
            }
        );

        return response()->json([
            'message' => 'E-mel ujian berjaya dihantar ke '.$recipientEmail,
        ]);
    }

    public function publicSettings(): JsonResponse
    {
        return response()->json([
            'system_name' => $this->settingsService->get('system_name', 'Sistem Pelaporan'),
            'system_logo' => $this->settingsService->get('system_logo'),
            'recaptcha_site_key' => $this->settingsService->get('recaptcha_site_key'),
        ]);
    }
}
