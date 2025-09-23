<?php

namespace App\Repositories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CampaignRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Campaign::class;
    }

    public function getAllByUserId(int $userId): Collection
    {
        return $this->model::where('user_id', $userId)->get();
    }

    /**
     * Aplica as queries comuns para contagem de leads e conversão
     * SUPER OTIMIZADA para grandes volumes (500k+ leads)
     * Usa índices compostos e evita subqueries desnecessárias
     */
    private function withLeadsData(Builder $query): Builder
    {
        return $query->selectRaw('
                campaigns.*,
                COALESCE(leads_stats.leads_count, 0) as leads_count,
                COALESCE(leads_stats.converted_leads_count, 0) as converted_leads_count,
                CASE
                    WHEN COALESCE(leads_stats.leads_count, 0) > 0
                    THEN ROUND((COALESCE(leads_stats.converted_leads_count, 0) / leads_stats.leads_count) * 100, 2)
                    ELSE 0
                END as conversion
            ')
            ->leftJoin(DB::raw('(
                SELECT
                    campaign_id,
                    COUNT(*) as leads_count,
                    SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted_leads_count
                FROM leads
                GROUP BY campaign_id
            ) as leads_stats'), 'campaigns.id', '=', 'leads_stats.campaign_id');
    }

    /**
     * Versão otimizada para paginação - calcula estatísticas apenas para as campanhas da página atual
     * CRÍTICO para performance com grandes volumes
     */
    private function withLeadsDataPaginated(Builder $query, int $perPage, int $page = 1): Builder
    {
        // Primeiro, obter apenas os IDs das campanhas da página atual
        $offset = ($page - 1) * $perPage;

        $campaignIds = $query->clone()
            ->select('campaigns.id')
            ->offset($offset)
            ->limit($perPage)
            ->pluck('id')
            ->toArray();

        if (empty($campaignIds)) {
            return $query->selectRaw('campaigns.*, 0 as leads_count, 0 as converted_leads_count, 0 as conversion');
        }

        // Agora calcular estatísticas apenas para essas campanhas
        $campaignIdsStr = implode(',', $campaignIds);

        return $query->selectRaw('
                campaigns.*,
                COALESCE(leads_stats.leads_count, 0) as leads_count,
                COALESCE(leads_stats.converted_leads_count, 0) as converted_leads_count,
                CASE
                    WHEN COALESCE(leads_stats.leads_count, 0) > 0
                    THEN ROUND((COALESCE(leads_stats.converted_leads_count, 0) / leads_stats.leads_count) * 100, 2)
                    ELSE 0
                END as conversion
            ')
            ->leftJoin(DB::raw("(
                SELECT
                    campaign_id,
                    COUNT(*) as leads_count,
                    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads_count
                FROM leads
                WHERE campaign_id IN ({$campaignIdsStr})
                GROUP BY campaign_id
            ) as leads_stats"), 'campaigns.id', '=', 'leads_stats.campaign_id');
    }

    public function findBySlug(string $slug): Campaign
    {
        return $this->withLeadsData($this->model::query())
            ->where('campaigns.slug', $slug)
            ->firstOrFail();
    }

    public function findById(int $id): Campaign
    {
        return $this->withLeadsData($this->model::query())
            ->where('campaigns.id', $id)
            ->firstOrFail();
    }

    public function getPaginated(array $params): LengthAwarePaginator
    {
        $query = $this->model::query();

        if (isset($params['title'])) {
            $query->where('campaigns.title', 'like', "%{$params['title']}%");
        }

        if (isset($params['status'])) {
            $query->where('campaigns.status', $params['status']);
        }

        // Para paginação, usar método otimizado que calcula estatísticas apenas para a página atual
        $perPage = $params['per_page'] ?? 10;
        $page = request()->get('page', 1);

        // Contar total de campanhas (query rápida sem joins)
        $total = $query->clone()->count();

        if ($total === 0) {
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }

        // Obter campanhas da página atual com estatísticas otimizadas
        $items = $this->withLeadsDataPaginated($query, $perPage, $page)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function getStatistics(?int $userId = null): array
    {
        // Filtro de usuário para multi-tenant (CRÍTICO para performance)
        $userFilter = $userId ? "WHERE user_id = {$userId}" : "";
        $userFilterLeads = $userId ? "WHERE l.user_id = {$userId}" : "";

        // Query 1: Estatísticas de campanhas (usa índice idx_campaigns_user_status)
        $campaignStats = DB::selectOne("
            SELECT
                COUNT(*) as total_campaigns,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as total_active_campaigns
            FROM campaigns
            {$userFilter}
        ");

        // Query 2: Estatísticas de leads (usa índices otimizados)
        $leadStats = DB::selectOne("
            SELECT
                COUNT(*) as total_leads,
                SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads
            FROM leads l
            {$userFilterLeads}
        ");

        // Query 3: Leads do mês atual (usa índice em created_at)
        $monthlyLeads = DB::selectOne(
            "
            SELECT COUNT(*) as total_leads_this_month
            FROM leads l
            WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            " . ($userId ? "AND l.user_id = {$userId}" : "")
        );

        // Cálculo de conversão otimizado
        $totalLeads = (int) $leadStats->total_leads;
        $convertedLeads = (int) $leadStats->converted_leads;
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        return [
            'total_campaigns' => (int) $campaignStats->total_campaigns,
            'total_active_campaigns' => (int) $campaignStats->total_active_campaigns,
            'total_leads' => $totalLeads,
            'total_leads_this_month' => (int) $monthlyLeads->total_leads_this_month,
            'total_leads_conversion' => $conversionRate . '%'
        ];
    }
}
