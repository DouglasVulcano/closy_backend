<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseAuthenticatedRequest;
use App\Http\Requests\Auth\{
    ForgotPasswordRequest,
    LoginRequest,
    RegisterRequest,
    ResetPasswordRequest
};
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{
    Auth,
    Hash,
    Password
};

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
        return response()->json(['user' => $user, 'token' => $token], JsonResponse::HTTP_CREATED);
    }

    public function logout(BaseAuthenticatedRequest $request): JsonResponse
    {
        $user = User::find($request->validated()['user_id']);
        if ($user instanceof User) {
            $user->tokens()->delete();
            return response()->json(['message' => __('validation.custom.logout_success')], JsonResponse::HTTP_OK);
        }
        return response()->json(['message' => __('validation.custom.logout_failure')], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __('passwords.sent')
            ], JsonResponse::HTTP_OK);
        }
        return response()->json(['message' => __($status)], JsonResponse::HTTP_BAD_REQUEST);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)]);
                $user->save();
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __('passwords.reset')], JsonResponse::HTTP_OK);
        }
        return response()->json(['message' => __($status)], JsonResponse::HTTP_BAD_REQUEST);
    }
}
