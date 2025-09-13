<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): void;

    public function findBy(string $column, mixed $value): Model;

    public function findManyBy(string $column, mixed $value): Collection;

    public function exists(int $id): bool;

    public function count(): int;

    public function getPaginated(array $params): LengthAwarePaginator;
}
