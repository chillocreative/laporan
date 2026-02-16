<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

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
            default => abort(404, 'Settings group not found.'),
        };

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $this->settingsService->getAllSettings(),
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
