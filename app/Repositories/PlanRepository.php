<?php

namespace App\Repositories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Collection;

class PlanRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Plan::class;
    }

    /**
     * Get all active plans
     */
    public function getActivePlans(): Collection
    {
        return Plan::active()->get();
    }

    /**
     * Find plan by Stripe price ID
     */
    public function findByStripePriceId(string $stripePriceId): ?Plan
    {
        return Plan::findByStripePriceId($stripePriceId);
    }

    /**
     * Get plans by role
     */
    public function getByRole(string $role): Collection
    {
        return Plan::where('role', $role)->active()->get();
    }
}