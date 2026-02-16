<?php

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Repositories\Contracts\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsRepository implements SettingsRepositoryInterface
{
    public function __construct(protected Setting $model) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "settings.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = $this->model->where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public function set(string $key, mixed $value, bool $encrypted = false, string $group = 'general'): void
    {
        $setting = $this->model->firstOrNew(['key' => $key]);
        $setting->is_encrypted = $encrypted;
        $setting->group_name = $group;
        $setting->value = $value;
        $setting->save();

        Cache::forget("settings.{$key}");
        Cache::forget("settings.group.{$group}");
    }

    public function getByGroup(string $group): Collection
    {
        $cacheKey = "settings.group.{$group}";

        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return $this->model->where('group_name', $group)->get();
        });
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }
}
