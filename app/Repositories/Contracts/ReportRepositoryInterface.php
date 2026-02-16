<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    public function getByRiskLevel(string $riskLevel, int $perPage = 15): LengthAwarePaginator;

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function countByStatus(): array;

    public function countByRiskLevel(): array;

    public function getTotalCount(): int;

    public function getCountByUser(int $userId): int;

    public function getTopReporters(int $limit = 5): array;
}
