<?php

namespace App\Services;

use App\Repositories\Contracts\SettingsRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    public function __construct(
        protected SettingsRepositoryInterface $settingsRepository,
        protected ActivityLogService $activityLogService,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settingsRepository->get($key, $default);
    }

    public function getByGroup(string $group): array
    {
        $settings = $this->settingsRepository->getByGroup($group);

        return $settings->pluck('value', 'key')->toArray();
    }

    public function updateGeneral(array $data): void
    {
        if (isset($data['system_name'])) {
            $this->settingsRepository->set('system_name', $data['system_name'], false, 'general');
        }

        $this->activityLogService->log('settings_updated', null, 'General settings updated');
    }

    public function updateOpenAI(array $data): void
    {
        $fields = [
            'openai_api_key' => true,   // encrypted
            'openai_model' => false,
            'openai_temperature' => false,
            'openai_max_tokens' => false,
            'openai_enabled' => false,
            'openai_queue_enabled' => false,
            'openai_daily_limit' => false,
        ];

        foreach ($fields as $key => $encrypted) {
            if (isset($data[$key])) {
                $this->settingsRepository->set($key, $data[$key], $encrypted, 'openai');
            }
        }

        $this->activityLogService->log('settings_updated', null, 'OpenAI settings updated');
    }

    public function updateSmtp(array $data): void
    {
        $fields = [
            'smtp_host' => false,
            'smtp_port' => false,
            'smtp_username' => false,
            'smtp_password' => true, // encrypted
            'smtp_encryption' => false,
            'smtp_from_address' => false,
            'smtp_from_name' => false,
        ];

        foreach ($fields as $key => $encrypted) {
            if (isset($data[$key])) {
                $this->settingsRepository->set($key, $data[$key], $encrypted, 'smtp');
            }
        }

        $this->activityLogService->log('settings_updated', null, 'SMTP settings updated');
    }

    public function updateCaptcha(array $data): void
    {
        if (isset($data['recaptcha_site_key'])) {
            $this->settingsRepository->set('recaptcha_site_key', $data['recaptcha_site_key'], false, 'captcha');
        }

        if (isset($data['recaptcha_secret_key'])) {
            $this->settingsRepository->set('recaptcha_secret_key', $data['recaptcha_secret_key'], true, 'captcha');
        }

        $this->activityLogService->log('settings_updated', null, 'Captcha settings updated');
    }

    public function updateLogo(UploadedFile $file): string
    {
        $oldLogo = $this->settingsRepository->get('system_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        $path = $file->store('branding', 'public');
        $this->settingsRepository->set('system_logo', $path, false, 'general');
        $this->activityLogService->log('settings_updated', null, 'System logo updated');

        return $path;
    }

    public function getAllSettings(): array
    {
        return [
            'general' => $this->getByGroup('general'),
            'openai' => $this->getSafeOpenAISettings(),
            'captcha' => $this->getSafeCaptchaSettings(),
            'smtp' => $this->getSafeSmtpSettings(),
        ];
    }

    protected function getSafeOpenAISettings(): array
    {
        $settings = $this->getByGroup('openai');

        // Mask API key for display
        if (isset($settings['openai_api_key']) && $settings['openai_api_key']) {
            $key = $settings['openai_api_key'];
            $settings['openai_api_key'] = substr($key, 0, 7).'...'.substr($key, -4);
            $settings['openai_api_key_set'] = true;
        } else {
            $settings['openai_api_key_set'] = false;
        }

        return $settings;
    }

    protected function getSafeSmtpSettings(): array
    {
        $settings = $this->getByGroup('smtp');

        if (isset($settings['smtp_password']) && $settings['smtp_password']) {
            $settings['smtp_password'] = '••••••••';
            $settings['smtp_password_set'] = true;
        } else {
            $settings['smtp_password_set'] = false;
        }

        return $settings;
    }

    public function applySmtpConfig(): void
    {
        $smtp = $this->getByGroup('smtp');

        if (! empty($smtp['smtp_host'])) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['smtp_host'],
                'mail.mailers.smtp.port' => (int) ($smtp['smtp_port'] ?? 587),
                'mail.mailers.smtp.username' => $smtp['smtp_username'] ?? null,
                'mail.mailers.smtp.password' => $smtp['smtp_password'] ?? null,
                'mail.mailers.smtp.encryption' => $smtp['smtp_encryption'] ?? 'tls',
                'mail.from.address' => $smtp['smtp_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $smtp['smtp_from_name'] ?? config('mail.from.name'),
            ]);
        }
    }

    protected function getSafeCaptchaSettings(): array
    {
        $settings = $this->getByGroup('captcha');

        // Mask secret key
        if (isset($settings['recaptcha_secret_key']) && $settings['recaptcha_secret_key']) {
            $settings['recaptcha_secret_key'] = '••••••••';
            $settings['recaptcha_secret_key_set'] = true;
        } else {
            $settings['recaptcha_secret_key_set'] = false;
        }

        return $settings;
    }
}
