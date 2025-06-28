<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiTokenAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header(config('sanctum.api_auth_key'));
        if (!$token) {
            throw new UnauthorizedHttpException('Guard', 'The supplied API KEY is missing or an invalid authorization header was sent');
        }

        $secretKey = config('sanctum.api_auth_secret_key');
        JWT::$leeway = 60;
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

        if ($decoded->url !== $request->url()) {
            throw new UnauthorizedHttpException('Guard', 'The supplied API KEY is invalid');
        }
        return $next($request);
    }
}
