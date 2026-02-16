<?php

namespace App\Services;

use App\Repositories\Contracts\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    public function __construct(
        protected SettingsRepositoryInterface $settingsRepository,
        protected SecurityLogService $securityLogService,
    ) {}

    public function verify(string $token): bool
    {
        $secretKey = $this->settingsRepository->get('recaptcha_secret_key');

        if (empty($secretKey)) {
            Log::warning('reCAPTCHA secret key not configured, skipping verification');

            return true;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            if (empty($result['success'])) {
                $this->securityLogService->logCaptchaFailure();

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function getSiteKey(): ?string
    {
        return $this->settingsRepository->get('recaptcha_site_key');
    }
}
