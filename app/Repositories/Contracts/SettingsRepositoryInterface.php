<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface SettingsRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, bool $encrypted = false, string $group = 'general'): void;

    public function getByGroup(string $group): Collection;

    public function getAll(): Collection;
}
