<?php

namespace App\Services;

use App\Models\Campaign;
use App\Repositories\CampaignRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignService
{
    public function __construct(private CampaignRepository $campaignRepository) {}

    public function getPaginated(array $params): LengthAwarePaginator
    {
        return $this->campaignRepository->getPaginated($params);
    }

    public function getAllByUserId(int $userId): Collection
    {
        return $this->campaignRepository->getAllByUserId($userId);
    }

    public function findById(int $id): Campaign
    {
        return $this->campaignRepository->findById($id);
    }

    public function findBySlug(string $slug): Campaign
    {
        return $this->campaignRepository->findBySlug($slug);
    }

    public function create(array $data): Campaign
    {
        return $this->campaignRepository->create($data);
    }

    public function update(int $id, array $data): Campaign
    {
        return $this->campaignRepository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->campaignRepository->delete($id);
    }

    public function getStatistics(?int $userId = null): array
    {
        return $this->campaignRepository->getStatistics($userId);
    }
}
