<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user instanceof User) {
                $token = $user->createToken('authToken')->plainTextToken;
                return response()->json(['token' => $token], JsonResponse::HTTP_OK);
            }
        }
        return response()->json(['message' => __('validation.custom.invalid_credentials')], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], JsonResponse::HTTP_CREATED);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user instanceof User)
            $user->tokens()->delete();
        return response()->json(['message' => __('validation.custom.invalid_credentials')], JsonResponse::HTTP_OK);
    }
}
