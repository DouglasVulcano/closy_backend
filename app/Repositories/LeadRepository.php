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
        if (isset($params['email']))
            $query->where('email', 'like', "%{$params['email']}%");
        if (isset($params['campaign_id']))
            $query->where('campaign_id', $params['campaign_id']);
        if (isset($params['status']))
            $query->where('status', $params['status']);
        return $query->paginate($params['per_page'] ?? 10);
    }

    public function getStatistics(int $user_id): array
    {
        $stats = $this->model::query()
            ->where('user_id', $user_id)
            ->selectRaw('
                count(*) as total,
                sum(case when status = "converted" then 1 else 0 end) as converted,
                sum(case when status = "lost" then 1 else 0 end) as lost,
                sum(case when status = "qualified" then 1 else 0 end) as qualified,
                sum(case when status = "contacted" then 1 else 0 end) as contacted,
                sum(case when status = "new" then 1 else 0 end) as new
            ')
            ->first();

        // Cálculo otimizado da conversão em PHP (mais eficiente)
        $total = (int) $stats->total;
        $converted = (int) $stats->converted;
        $conversionRate = $total > 0 ? round(($converted / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'converted' => $converted,
            'lost' => (int) $stats->lost,
            'qualified' => (int) $stats->qualified,
            'contacted' => (int) $stats->contacted,
            'new' => (int) $stats->new,
            'total_leads_conversion' => $conversionRate
        ];
    }
}
