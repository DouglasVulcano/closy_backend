<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected string $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    abstract protected function getModelClass(): string;

    public function getAll(): Collection
    {
        return $this->model::all();
    }

    public function findById(int $id): Model
    {
        return $this->model::findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model::create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->findById($id);
        $model->update($data);
        $model->refresh();
        return $model;
    }

    public function delete(int $id): void
    {
        $this->findById($id)->delete();
    }

    public function findBy(string $column, mixed $value): Model
    {
        return $this->model::where($column, $value)->firstOrFail();
    }

    public function findManyBy(string $column, mixed $value): Collection
    {
        return $this->model::where($column, $value)->get();
    }

    public function exists(int $id): bool
    {
        return $this->model::where('id', $id)->exists();
    }

    public function count(): int
    {
        return $this->model::count();
    }

    public function getPaginated(array $params): LengthAwarePaginator
    {
        return $this->model::paginate($params['per_page'] ?? 10);
    }
}
