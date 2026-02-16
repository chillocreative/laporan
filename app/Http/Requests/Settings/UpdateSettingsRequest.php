<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $group = $this->route('group');

        return match ($group) {
            'general' => $this->generalRules(),
            'openai' => $this->openaiRules(),
            'captcha' => $this->captchaRules(),
            'smtp' => $this->smtpRules(),
            'branding' => $this->brandingRules(),
            default => [],
        };
    }

    protected function generalRules(): array
    {
        return [
            'system_name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function openaiRules(): array
    {
        return [
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'openai_model' => ['required', 'string', 'in:gpt-4o-mini,gpt-4o,gpt-4-turbo,gpt-3.5-turbo'],
            'openai_temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'openai_max_tokens' => ['required', 'integer', 'min:100', 'max:4000'],
            'openai_enabled' => ['required', 'boolean'],
            'openai_queue_enabled' => ['required', 'boolean'],
            'openai_daily_limit' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    protected function captchaRules(): array
    {
        return [
            'recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function smtpRules(): array
    {
        return [
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['required', 'string', 'in:tls,ssl,none'],
            'smtp_from_address' => ['required', 'email', 'max:255'],
            'smtp_from_name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function brandingRules(): array
    {
        return [
            'logo' => ['required', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
        ];
    }
}
