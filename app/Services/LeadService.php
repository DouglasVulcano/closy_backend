<?php

namespace App\Services;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class LeadService
{
    public function __construct(private LeadRepository $leadRepository) {}

    public function getPaginated(array $params): LengthAwarePaginator
    {
        return $this->leadRepository->getPaginated($params);
    }

    public function findById(int $id): Lead
    {
        return $this->leadRepository->findById($id);
    }

    public function create(array $data): Lead
    {
        return $this->leadRepository->create($data);
    }

    public function update(int $id, array $data): Lead
    {
        return $this->leadRepository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->leadRepository->delete($id);
    }

    public function getStatistics(int $user_id): array
    {
        return $this->leadRepository->getStatistics($user_id);
    }
}
