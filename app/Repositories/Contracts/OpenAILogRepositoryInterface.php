<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface OpenAILogRepositoryInterface
{
    public function log(array $data): Model;

    public function getAll(int $perPage = 25): LengthAwarePaginator;

    public function getTodayUsage(): array;

    public function getByReport(int $reportId): \Illuminate\Database\Eloquent\Collection;

    public function getFiltered(array $filters, int $perPage = 25): LengthAwarePaginator;
}
