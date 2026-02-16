<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface SecurityLogRepositoryInterface
{
    public function log(array $data): Model;

    public function getAll(int $perPage = 25): LengthAwarePaginator;

    public function getBySeverity(string $severity, int $perPage = 25): LengthAwarePaginator;

    public function getRecentSuspicious(int $hours = 24): int;

    public function getFiltered(array $filters, int $perPage = 25): LengthAwarePaginator;
}
