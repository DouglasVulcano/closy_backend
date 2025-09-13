<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lead\{LeadPaginatedRequest, LeadRequest, PublicCreateLeadRequest, UpdateLeadStatusRequest};
use Illuminate\Http\{JsonResponse};
use App\Services\LeadService;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(LeadPaginatedRequest $request)
    {
        return response()->json($this->leadService->getPaginated($request->validated()), JsonResponse::HTTP_OK);
    }

    public function storePublic(PublicCreateLeadRequest $request)
    {
        $lead = $this->leadService->create($request->validated());
        return response()->json($lead, JsonResponse::HTTP_CREATED);
    }

    public function show(LeadRequest $request)
    {
        return response()->json($this->leadService->findById($request->validated()['id']), JsonResponse::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(UpdateLeadStatusRequest $request)
    {
        $lead = $this->leadService->update(
            $request->validated()['id'],
            ['status' => $request->validated()['status']]
        );
        return response()->json($lead, JsonResponse::HTTP_OK);
    }

    public function destroy(LeadRequest $request)
    {
        $this->leadService->delete($request->validated()['id']);
        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
