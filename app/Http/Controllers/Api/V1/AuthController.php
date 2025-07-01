<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'device_name' => 'nullable|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken($request->device_name ?? 'api-token');

            return $this->createdResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
            ], 'User registered successfully');
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to register user');
        }
    }

    /**
     * Login user using Laravel's Auth facade
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'device_name' => 'nullable|string',
            ]);

            // Use Laravel's Auth facade for authentication
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = Auth::user();
                
                // Revoke existing tokens for this device if device_name is provided
                if ($request->device_name) {
                    $user->tokens()->where('name', $request->device_name)->delete();
                }

                $token = $user->createToken($request->device_name ?? 'api-token');

                return $this->successResponse([
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'access_token' => $token->plainTextToken,
                    'token_type' => 'Bearer',
                ], 'User logged in successfully');
            }

            return $this->unauthorizedResponse('Invalid credentials');
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to login user');
        }
    }

    /**
     * Get authenticated user information
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 'User information retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve user information');
        }
    }

    /**
     * Logout user and revoke current token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke the current token
            $request->user()->currentAccessToken()->delete();

            return $this->successMessage('Successfully logged out');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to logout user');
        }
    }

    /**
     * Logout user and revoke all tokens
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            // Revoke all tokens for the user
            $request->user()->tokens()->delete();

            return $this->successMessage('Successfully logged out from all devices');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to logout from all devices');
        }
    }

    /**
     * Refresh user token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('api-token');

            return $this->successResponse([
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to refresh token');
        }
    }
} 