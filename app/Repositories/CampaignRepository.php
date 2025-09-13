<?php

namespace App\Repositories;

use App\Models\Campaign;
use Illuminate\Pagination\LengthAwarePaginator;

class CampaignRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Campaign::class;
    }

    public function findBySlug(string $slug): Campaign
    {
        return $this->findBy('slug', $slug);
    }

    public function getPaginated(array $params): LengthAwarePaginator
    {
        $query = $this->model::query();
        if (isset($params['title']))
            $query->where('title', 'like', "%{$params['title']}%");
        if (isset($params['status']))
            $query->where('status', $params['status']);
        return $query->paginate($params['per_page'] ?? 10);
    }
}
