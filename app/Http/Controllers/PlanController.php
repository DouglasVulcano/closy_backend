<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function __construct(private PlanService $planService) {}

    public function index(): JsonResponse
    {
        $plans = $this->planService->getActivePlans();
        return response()->json($plans, JsonResponse::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $plan = $this->planService->findById($id);
            return response()->json($plan, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Plano não encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
