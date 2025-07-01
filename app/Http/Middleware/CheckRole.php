<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if user has the required role
        // You can customize this logic based on your user model structure
        if (!$this->userHasRole($request->user(), $role)) {
            return response()->json(['message' => 'Insufficient permissions'], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has the specified role
     */
    private function userHasRole($user, string $role): bool
    {
        // This is a basic implementation. You can enhance it based on your needs:
        // 1. Add a 'role' column to users table
        // 2. Create a roles table with many-to-many relationship
        // 3. Use a package like Spatie Laravel Permission
        
        // For now, we'll check if the role is in the user's abilities
        // You can modify this based on your user model structure
        return $user->currentAccessToken()->can($role);
    }
} 