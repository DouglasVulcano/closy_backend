<?php

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Pagination\LengthAwarePaginator;

class LeadRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return  Lead::class;
    }

    public function getPaginated(array $params): LengthAwarePaginator
    {
        $query = $this->model::query();
        if (isset($params['name']))
            $query->where('name', 'like', "%{$params['name']}%");
        if (isset($params['campaign_id']))
            $query->where('campaign_id', $params['campaign_id']);
        if (isset($params['status']))
            $query->where('status', $params['status']);
        return $query->paginate($params['per_page'] ?? 10);
    }
}
