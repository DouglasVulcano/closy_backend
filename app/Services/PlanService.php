<?php

namespace App\Services;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use Illuminate\Database\Eloquent\Collection;

class PlanService
{
    public function __construct(private PlanRepository $planRepository) {}

    /**
     * Get all active plans
     */
    public function getActivePlans(): Collection
    {
        return $this->planRepository->getActivePlans();
    }

    /**
     * Find plan by ID
     */
    public function findById(int $id): Plan
    {
        return $this->planRepository->findById($id);
    }

    /**
     * Find plan by Stripe price ID
     */
    public function findByStripePriceId(string $stripePriceId): ?Plan
    {
        return $this->planRepository->findByStripePriceId($stripePriceId);
    }

    /**
     * Get plans by role
     */
    public function getByRole(string $role): Collection
    {
        return $this->planRepository->getByRole($role);
    }

    /**
     * Create a new plan
     */
    public function create(array $data): Plan
    {
        return $this->planRepository->create($data);
    }

    /**
     * Update a plan
     */
    public function update(int $id, array $data): Plan
    {
        return $this->planRepository->update($id, $data);
    }

    /**
     * Delete a plan
     */
    public function delete(int $id): void
    {
        $this->planRepository->delete($id);
    }
}