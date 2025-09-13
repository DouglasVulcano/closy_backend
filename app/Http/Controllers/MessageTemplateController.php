<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageTemplate\{
    CreateMessageTemplateRequest,
    MessageTemplatePaginatedRequest,
    MessageTemplateRequest,
    UpdateMessageTemplateRequest
};
use App\Services\MessageTemplateService;
use Illuminate\Http\JsonResponse;

class MessageTemplateController extends Controller
{
    public function __construct(private MessageTemplateService $messageTemplateService) {}

    public function index(MessageTemplatePaginatedRequest $request): JsonResponse
    {
        $messageTemplates = $this->messageTemplateService->getPaginated($request->validated());
        return response()->json($messageTemplates, JsonResponse::HTTP_OK);
    }

    public function store(CreateMessageTemplateRequest $request): JsonResponse
    {
        $messageTemplate = $this->messageTemplateService->create($request->validated());
        return response()->json($messageTemplate, JsonResponse::HTTP_OK);
    }

    public function show(MessageTemplateRequest $request): JsonResponse
    {
        $messageTemplate = $this->messageTemplateService->findById($request->validated()['id']);
        return response()->json($messageTemplate, JsonResponse::HTTP_OK);
    }

    public function update(UpdateMessageTemplateRequest $request): JsonResponse
    {
        $messageTemplate = $this->messageTemplateService->update($request->validated()['id'], $request->validated());
        return response()->json($messageTemplate, JsonResponse::HTTP_OK);
    }

    public function destroy(MessageTemplateRequest $request): JsonResponse
    {
        $this->messageTemplateService->delete($request->validated()['id']);
        return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
