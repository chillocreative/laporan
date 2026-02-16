<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface ActivityLogRepositoryInterface
{
    public function log(array $data): Model;

    public function getAll(int $perPage = 25): LengthAwarePaginator;

    public function getByUser(int $userId, int $perPage = 25): LengthAwarePaginator;

    public function getByAction(string $action, int $perPage = 25): LengthAwarePaginator;

    public function getFiltered(array $filters, int $perPage = 25): LengthAwarePaginator;
}
