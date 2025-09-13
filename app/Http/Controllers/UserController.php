<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseAuthenticatedRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function profile(BaseAuthenticatedRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);
        return response()->json($user, JsonResponse::HTTP_OK);
    }

    public function updateProfile(UpdateUserRequest $request): JsonResponse
    {
        $user = $this->userService->update($request->validated()['user_id'], $request->validated());
        return response()->json(['message' => __('user.updated'), 'user' => $user], JsonResponse::HTTP_OK);
    }

    public function deleteAccount(BaseAuthenticatedRequest $request): JsonResponse
    {
        $user = $this->userService->findById($request->validated()['user_id']);
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => __('user.deleted')], JsonResponse::HTTP_OK);
    }
}
