<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user(), JsonResponse::HTTP_OK);
    }

    /**
     * Update the authenticated user's profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Delete the authenticated user's account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Delete all user's tokens
        $user->tokens()->delete();
        
        // Delete the user
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ], JsonResponse::HTTP_OK);
    }
}
