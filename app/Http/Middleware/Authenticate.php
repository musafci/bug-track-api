<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API routes, return null to prevent redirect and let the middleware handle JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // Only call route() for web routes
        return route('login');
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API routes, throw an authentication exception that will be handled by the exception handler
        if ($request->expectsJson() || $request->is('api/*')) {
            throw new \Illuminate\Auth\AuthenticationException(
                'Unauthenticated.', $guards, null
            );
        }

        parent::unauthenticated($request, $guards);
    }
} 