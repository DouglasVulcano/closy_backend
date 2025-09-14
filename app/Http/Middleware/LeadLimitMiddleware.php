<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class LeadLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $campaignSlug = $request->route('campaign_slug');
        
        if (!$campaignSlug) {
            return response()->json(['message' => 'Campaign slug is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Find campaign by slug
        $campaign = Campaign::where('slug', $campaignSlug)->first();
        
        if (!$campaign) {
            return response()->json(['message' => 'Campaign not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Get campaign owner
        $user = $campaign->user;
        
        if (!$user) {
            return response()->json(['message' => 'Campaign owner not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Get user's current plan
        $plan = $this->getUserCurrentPlan($user);
        
        if (!$plan) {
            return response()->json(['message' => 'No active plan found for user'], JsonResponse::HTTP_FORBIDDEN);
        }

        // Count leads created this month for this user
        $currentMonthLeadsCount = $this->getCurrentMonthLeadsCount($user->id);
        
        // Check if user has reached the monthly limit
        if ($currentMonthLeadsCount >= $plan->monthly_leads_limit) {
            return response()->json([
                'message' => 'Monthly leads limit reached',
                'current_count' => $currentMonthLeadsCount,
                'limit' => $plan->monthly_leads_limit
            ], JsonResponse::HTTP_TOO_MANY_REQUESTS);
        }

        return $next($request);
    }

    /**
     * Get user's current active plan
     */
    private function getUserCurrentPlan($user): ?Plan
    {
        // If user has an active subscription, get plan by stripe price
        if ($user->subscribed('default')) {
            $subscription = $user->subscription('default');
            return Plan::findByStripePriceId($subscription->stripe_price);
        }

        // If user is on trial, get plan by stripe price
        if ($user->onTrial('default')) {
            $subscription = $user->subscription('default');
            return Plan::findByStripePriceId($subscription->stripe_price);
        }

        // Default to basic plan or return null
        return Plan::where('role', 'USER')->where('active', true)->first();
    }

    /**
     * Count leads created this month for the user
     */
    private function getCurrentMonthLeadsCount(int $userId): int
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return Lead::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
    }
}
