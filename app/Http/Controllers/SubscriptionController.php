<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseAuthenticatedRequest;
use App\Services\PlanService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(private PlanService $planService,  private UserService $userService) {}

    /**
     * Get user's current subscription
     */
    public function current(BaseAuthenticatedRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);

        if (!$user->subscribed('default')) {
            return response()->json([
                'message' => 'Usuário não possui assinatura ativa'
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $subscription = $user->subscription('default');

        return response()->json([
            'data' => [
                'id' => $subscription->id,
                'stripe_id' => $subscription->stripe_id,
                'stripe_status' => $subscription->stripe_status,
                'stripe_price' => $subscription->stripe_price,
                'quantity' => $subscription->quantity,
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'created_at' => $subscription->created_at,
                'updated_at' => $subscription->updated_at,
            ]
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Cancel user's subscription
     */
    public function cancel(BaseAuthenticatedRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);

        if (!$user->subscribed('default')) {
            return response()->json([
                'message' => 'Usuário não possui assinatura ativa para cancelar'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->subscription('default')->cancel();

        return response()->json([
            'message' => 'Assinatura cancelada com sucesso'
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Resume user's subscription
     */
    public function resume(BaseAuthenticatedRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);

        if (!$user->subscription('default')->cancelled()) {
            return response()->json([
                'message' => 'Assinatura não está cancelada'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->subscription('default')->resume();

        return response()->json([
            'message' => 'Assinatura reativada com sucesso'
        ], JsonResponse::HTTP_OK);
    }
}
