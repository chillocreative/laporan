<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'system_name', 'value' => 'Sistem Pelaporan', 'is_encrypted' => false, 'group_name' => 'general'],
            ['key' => 'system_logo', 'value' => null, 'is_encrypted' => false, 'group_name' => 'general'],

            // OpenAI
            ['key' => 'openai_api_key', 'value' => null, 'is_encrypted' => true, 'group_name' => 'openai'],
            ['key' => 'openai_model', 'value' => 'gpt-4o-mini', 'is_encrypted' => false, 'group_name' => 'openai'],
            ['key' => 'openai_temperature', 'value' => '0.3', 'is_encrypted' => false, 'group_name' => 'openai'],
            ['key' => 'openai_max_tokens', 'value' => '1000', 'is_encrypted' => false, 'group_name' => 'openai'],
            ['key' => 'openai_enabled', 'value' => '0', 'is_encrypted' => false, 'group_name' => 'openai'],
            ['key' => 'openai_queue_enabled', 'value' => '1', 'is_encrypted' => false, 'group_name' => 'openai'],
            ['key' => 'openai_daily_limit', 'value' => '100', 'is_encrypted' => false, 'group_name' => 'openai'],

            // Captcha
            ['key' => 'recaptcha_site_key', 'value' => null, 'is_encrypted' => false, 'group_name' => 'captcha'],
            ['key' => 'recaptcha_secret_key', 'value' => null, 'is_encrypted' => true, 'group_name' => 'captcha'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
