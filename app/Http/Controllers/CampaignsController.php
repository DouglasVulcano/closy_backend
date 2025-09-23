<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseAuthenticatedRequest;
use App\Http\Requests\Campaign\{
    CampaignPaginatedRequest,
    CampaignRequest,
    CreateCampaignRequest,
    UpdateCampaignRequest,
};
use App\Services\CampaignService;
use Illuminate\Http\JsonResponse;

class CampaignsController extends Controller
{
    public function __construct(private CampaignService $campaignService) {}

    public function index(CampaignPaginatedRequest $request)
    {
        return response()->json($this->campaignService->getPaginated($request->validated()), JsonResponse::HTTP_OK);
    }

    public function getAll(BaseAuthenticatedRequest $request): JsonResponse
    {
        return response()->json($this->campaignService->getAllByUserId($request->validated()['user_id']), JsonResponse::HTTP_OK);
    }

    public function store(CreateCampaignRequest $request): JsonResponse
    {
        $campaign = $this->campaignService->create($request->validated());
        return response()->json($campaign, JsonResponse::HTTP_CREATED);
    }

    public function show(CampaignRequest $request): JsonResponse
    {
        return response()->json($this->campaignService->findById($request->validated()['id']), JsonResponse::HTTP_OK);
    }

    public function update(UpdateCampaignRequest $request)
    {
        $campaign = $this->campaignService->update($request->validated()['id'], $request->validated());
        return response()->json($campaign, JsonResponse::HTTP_OK);
    }

    public function destroy(CampaignRequest $request)
    {
        $this->campaignService->delete($request->validated()['id']);
        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    public function statistics(BaseAuthenticatedRequest $request): JsonResponse
    {
        $statistics = $this->campaignService->getStatistics($request->validated()['user_id']);
        return response()->json($statistics, JsonResponse::HTTP_OK);
    }
}
