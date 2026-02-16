<?php

namespace App\Policies;

use App\Models\User;

class SettingsPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('settings.view');
    }

    public function editGeneral(User $user): bool
    {
        return $user->hasPermission('settings.edit-general');
    }

    public function editOpenAI(User $user): bool
    {
        return $user->hasPermission('settings.edit-openai');
    }

    public function editCaptcha(User $user): bool
    {
        return $user->hasPermission('settings.edit-captcha');
    }

    public function editBranding(User $user): bool
    {
        return $user->hasPermission('settings.edit-branding');
    }
}
