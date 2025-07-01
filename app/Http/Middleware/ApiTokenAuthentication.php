<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            Log::warning('API authentication failed: Missing API key', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl()
            ]);
            throw new UnauthorizedHttpException('Bearer', 'API key is required');
        }

        try {
            $secretKey = config('sanctum.api_auth_secret_key');
            JWT::$leeway = 60; // 60 seconds leeway for clock skew
            
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            
            // Validate URL if present in token
            if (isset($decoded->url) && $decoded->url !== $request->url()) {
                // Check if the token URL contains a wildcard pattern
                $tokenUrl = $decoded->url;
                $requestUrl = $request->url();
                
                // Convert wildcard pattern to regex
                $pattern = str_replace(['*', '/'], ['.*', '\/'], $tokenUrl);
                $pattern = '/^' . $pattern . '$/';
                
                if (!preg_match($pattern, $requestUrl)) {
                    Log::warning('API authentication failed: URL mismatch', [
                        'expected_url' => $decoded->url,
                        'actual_url' => $request->url(),
                        'ip' => $request->ip()
                    ]);
                    throw new UnauthorizedHttpException('Bearer', 'Invalid API key for this endpoint');
                }
            }
            
            // Add decoded token data to request for later use
            $request->attributes->set('api_token_data', $decoded);
            
            return $next($request);
            
        } catch (ExpiredException $e) {
            Log::warning('API authentication failed: Token expired', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            throw new UnauthorizedHttpException('Bearer', 'API key has expired');
        } catch (SignatureInvalidException $e) {
            Log::warning('API authentication failed: Invalid signature', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            throw new UnauthorizedHttpException('Bearer', 'Invalid API key signature');
        } catch (\Exception $e) {
            Log::error('API authentication failed: Unexpected error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            throw new UnauthorizedHttpException('Bearer', 'Invalid API key');
        }
    }
}
