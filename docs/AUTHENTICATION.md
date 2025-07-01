# Authentication & Authorization Guide

## Overview

This Laravel 12 API uses **Laravel's standard authentication system** with:
1. **API Key Authentication** (JWT-based) - For service-to-service communication
2. **User Authentication** (Laravel Sanctum) - For user-specific operations using Laravel's Auth facade

## Architecture

### 1. API Key Authentication (JWT)
- **Purpose**: Service-to-service authentication
- **Implementation**: Custom middleware using Firebase JWT
- **Header**: `X-BugTrackApi` (configurable)
- **Scope**: Validates API access and URL-specific permissions

### 2. User Authentication (Laravel Sanctum + Auth Facade)
- **Purpose**: User-specific operations
- **Implementation**: Laravel Sanctum with personal access tokens
- **Authentication**: Laravel's Auth facade for login validation
- **Header**: `Authorization: Bearer {token}`
- **Scope**: User identity and permissions

## Configuration

### Environment Variables
```env
# API Key Configuration
API_AUTH_KEY=X-BugTrackApi
API_AUTH_SECRET_KEY=your-secret-key-here

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SANCTUM_TOKEN_PREFIX=
API_TOKEN_NAME=BugTrackApi
```

### Auth Guards
```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

## API Endpoints

### Public Endpoints (No Authentication)
```
POST /api/login
POST /api/register
```

### Protected Endpoints (Require API Key + User Token)
```
GET  /api/me
POST /api/logout
POST /api/logout-all
POST /api/refresh
```

## Usage Examples

### 1. User Registration
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "X-BugTrackApi: your-jwt-token" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "iPhone 15"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

### 2. User Login (Laravel Auth Facade)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "X-BugTrackApi: your-jwt-token" \
  -d '{
    "email": "john@example.com",
    "password": "password123",
    "device_name": "iPhone 15"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

### 3. Access Protected Endpoints
```bash
curl -X GET http://localhost:8000/api/me \
  -H "X-BugTrackApi: your-jwt-token" \
  -H "Authorization: Bearer 1|abc123..."
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### 4. Logout
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "X-BugTrackApi: your-jwt-token" \
  -H "Authorization: Bearer 1|abc123..."
```

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

### 5. Logout All Devices
```bash
curl -X POST http://localhost:8000/api/logout-all \
  -H "X-BugTrackApi: your-jwt-token" \
  -H "Authorization: Bearer 1|abc123..."
```

**Response:**
```json
{
  "message": "Successfully logged out from all devices"
}
```

### 6. Refresh Token
```bash
curl -X POST http://localhost:8000/api/refresh \
  -H "X-BugTrackApi: your-jwt-token" \
  -H "Authorization: Bearer 1|abc123..."
```

**Response:**
```json
{
  "access_token": "2|def456...",
  "token_type": "Bearer"
}
```

## Implementation Details

### AuthController Methods

#### 1. Register Method
```php
public function register(Request $request): JsonResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'device_name' => 'nullable|string',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken($request->device_name ?? 'api-token');

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
        'access_token' => $token->plainTextToken,
        'token_type' => 'Bearer',
    ], 201);
}
```

#### 2. Login Method (Laravel Auth Facade)
```php
public function login(Request $request): JsonResponse
{
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

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    throw ValidationException::withMessages([
        'email' => ['The provided credentials are incorrect.'],
    ]);
}
```

#### 3. Me Method (Get User Info)
```php
public function me(Request $request): JsonResponse
{
    $user = Auth::user();
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ],
    ]);
}
```

#### 4. Logout Method
```php
public function logout(Request $request): JsonResponse
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Successfully logged out',
    ]);
}
```

#### 5. Logout All Method
```php
public function logoutAll(Request $request): JsonResponse
{
    $request->user()->tokens()->delete();

    return response()->json([
        'message' => 'Successfully logged out from all devices',
    ]);
}
```

#### 6. Refresh Method
```php
public function refresh(Request $request): JsonResponse
{
    $user = Auth::user();
    
    // Revoke current token
    $request->user()->currentAccessToken()->delete();
    
    // Create new token
    $token = $user->createToken('api-token');

    return response()->json([
        'access_token' => $token->plainTextToken,
        'token_type' => 'Bearer',
    ]);
}
```

## Security Features

### 1. API Key Security
- **JWT-based**: Tamper-proof tokens
- **URL-specific**: Tokens can be bound to specific endpoints
- **Expiration**: Configurable token expiration
- **Logging**: Failed authentication attempts are logged

### 2. User Token Security
- **Database-stored**: Tokens stored in `personal_access_tokens` table
- **Revocable**: Tokens can be revoked individually or globally
- **Device tracking**: Tokens can be associated with specific devices
- **Laravel Auth**: Uses Laravel's built-in authentication system

### 3. Rate Limiting
```php
// Add to routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // Rate-limited routes
});
```

## Best Practices

### 1. Token Management
- **Short-lived tokens**: Set expiration for sensitive operations
- **Device-specific tokens**: Use device names for better tracking
- **Regular cleanup**: Implement token cleanup for expired tokens

### 2. Error Handling
- **Consistent responses**: Use standardized error formats
- **Security logging**: Log authentication failures
- **Rate limiting**: Prevent brute force attacks

### 3. Authorization
```php
// Role-based authorization
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin-only routes
});

// Ability-based authorization
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/users', function (Request $request) {
        if (!$request->user()->currentAccessToken()->can('view-users')) {
            abort(403, 'Insufficient permissions');
        }
        // Admin logic
    });
});
```

## Advanced Features

### 1. Token Abilities
```php
// Create token with specific abilities
$token = $user->createToken('api-token', [
    'read:bugs',
    'write:bugs',
    'delete:bugs'
]);

// Check abilities
if ($request->user()->currentAccessToken()->can('delete:bugs')) {
    // Allow deletion
}
```

### 2. Custom Authorization
```php
// Create custom authorization middleware
class CheckBugPermission
{
    public function handle(Request $request, Closure $next, string $action)
    {
        $bug = Bug::findOrFail($request->route('bug'));
        
        if (!$request->user()->can($action, $bug)) {
            abort(403, 'Insufficient permissions');
        }
        
        return $next($request);
    }
}
```

## Monitoring & Logging

### 1. Authentication Logs
```php
// Log successful logins
Log::info('User logged in', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent()
]);

// Log failed attempts
Log::warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent()
]);
```

### 2. Token Analytics
```php
// Track token usage
$token = $request->user()->currentAccessToken();
$token->update(['last_used_at' => now()]);
```

## Troubleshooting

### Common Issues

1. **"API key is required"**
   - Ensure `X-BugTrackApi` header is present
   - Check JWT token validity

2. **"Invalid API key signature"**
   - Verify `API_AUTH_SECRET_KEY` matches
   - Check JWT token format

3. **"Unauthenticated"**
   - Ensure `Authorization: Bearer {token}` header is present
   - Verify Sanctum token is valid and not expired

4. **"Insufficient permissions"**
   - Check user's token abilities
   - Verify role-based permissions

### Debug Mode
```php
// Enable detailed error messages in development
if (config('app.debug')) {
    Log::debug('Authentication details', [
        'headers' => $request->headers->all(),
        'user' => $request->user(),
        'token' => $request->bearerToken()
    ]);
}
```

## Migration & Deployment

### 1. Database Setup
```bash
# Run migrations
php artisan migrate

# Create personal access tokens table
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 2. Environment Setup
```bash
# Generate application key
php artisan key:generate

# Set secure API keys
echo "API_AUTH_SECRET_KEY=$(openssl rand -base64 32)" >> .env
```

### 3. Production Considerations
- Use HTTPS in production
- Set secure session configuration
- Implement proper CORS policies
- Use environment-specific API keys
- Enable rate limiting
- Set up monitoring and alerting

## Token Scope & URL-Specific Tokens

### Overview

The JWT API tokens can be generated with different scopes, allowing you to control which endpoints each token can access. This provides fine-grained security control for your API.

### Token Generation Options

#### 1. Global Token (Default)
```bash
# Generates a token valid for ALL /api/* endpoints
php artisan api:generate-token
```

**Scope**: `http://localhost:8000/api/*`
- ✅ Works for `/api/register`
- ✅ Works for `/api/login`
- ✅ Works for `/api/me`, `/api/logout`, etc.
- ✅ Works for any future `/api/*` endpoints

#### 2. URL-Specific Tokens
```bash
# Token for only registration endpoint
php artisan api:generate-token "http://localhost:8000/api/register"

# Token for only login endpoint
php artisan api:generate-token "http://localhost:8000/api/login"

# Token for user management endpoints
php artisan api:generate-token "http://localhost:8000/api/user/*"

# Token for admin endpoints
php artisan api:generate-token "http://localhost:8000/api/admin/*"
```

### Security Benefits

#### Principle of Least Privilege
Using URL-specific tokens follows the security principle of least privilege:

- **Registration Service**: Only needs access to `/api/register`
- **Authentication Service**: Only needs access to `/api/login`
- **User Management**: Only needs access to `/api/user/*`
- **Admin Panel**: Only needs access to `/api/admin/*`

#### Example: Register-Specific Token
```bash
# Generate token for registration only
php artisan api:generate-token "http://localhost:8000/api/register"

# This token will:
# ✅ Work for /api/register
# ❌ Fail for /api/login (401 Unauthorized)
# ❌ Fail for /api/me (401 Unauthorized)
```

### Token Validation Logic

The middleware validates tokens using regex pattern matching:

```php
// Convert wildcard pattern to regex
$pattern = str_replace(['*', '/'], ['.*', '\/'], $tokenUrl);
$pattern = '/^' . $pattern . '$/';

if (!preg_match($pattern, $requestUrl)) {
    throw new UnauthorizedHttpException('Bearer', 'Invalid API key for this endpoint');
}
```

### Production Recommendations

#### 1. Service-Specific Tokens
```bash
# For microservices architecture
php artisan api:generate-token "http://api.example.com/auth/*"     # Auth service
php artisan api:generate-token "http://api.example.com/users/*"    # User service
php artisan api:generate-token "http://api.example.com/bugs/*"     # Bug tracking service
```

#### 2. Environment-Specific Tokens
```bash
# Development
php artisan api:generate-token "http://localhost:8000/api/*"

# Staging
php artisan api:generate-token "http://staging.example.com/api/*"

# Production
php artisan api:generate-token "http://api.example.com/api/*"
```

#### 3. Role-Based Token Scopes
```bash
# Public endpoints (registration, login)
php artisan api:generate-token "http://api.example.com/api/public/*"

# User endpoints (profile, settings)
php artisan api:generate-token "http://api.example.com/api/user/*"

# Admin endpoints (user management, system settings)
php artisan api:generate-token "http://api.example.com/api/admin/*"
```

### Token Management Best Practices

#### 1. Token Naming Convention
```bash
# Use descriptive names for different services
php artisan api:generate-token "http://localhost:8000/api/register" --name="registration-service"
php artisan api:generate-token "http://localhost:8000/api/login" --name="auth-service"
```

#### 2. Token Expiration
```bash
# Short-lived tokens for sensitive operations
php artisan api:generate-token "http://localhost:8000/api/admin/*" --expires=1800  # 30 minutes

# Longer-lived tokens for public endpoints
php artisan api:generate-token "http://localhost:8000/api/public/*" --expires=86400  # 24 hours
```

#### 3. Token Rotation
- Regularly rotate tokens (e.g., monthly)
- Use different tokens for different environments
- Monitor token usage and revoke unused tokens

### Testing Token Scopes

#### Test Global Token
```bash
# Generate global token
php artisan api:generate-token

# Test multiple endpoints
curl -H "X-BugTrackApi: <global-token>" http://localhost:8000/api/register
curl -H "X-BugTrackApi: <global-token>" http://localhost:8000/api/login
curl -H "X-BugTrackApi: <global-token>" http://localhost:8000/api/me
```

#### Test Specific Token
```bash
# Generate register-specific token
php artisan api:generate-token "http://localhost:8000/api/register"

# Test register endpoint (should work)
curl -H "X-BugTrackApi: <register-token>" http://localhost:8000/api/register

# Test login endpoint (should fail)
curl -H "X-BugTrackApi: <register-token>" http://localhost:8000/api/login
```

### Troubleshooting Token Scope Issues

#### Common Issues

1. **"Invalid API key for this endpoint"**
   - Check if the token URL matches the requested endpoint
   - Verify wildcard patterns are correct
   - Ensure the token hasn't expired

2. **Token works for some endpoints but not others**
   - Verify the token was generated with the correct URL scope
   - Check if the endpoint URL matches the token's URL pattern

3. **Wildcard patterns not working**
   - Ensure the wildcard (*) is in the correct position
   - Test the regex pattern manually if needed

#### Debug Token Contents
```php
// Add this to your middleware for debugging
Log::debug('Token URL validation', [
    'token_url' => $decoded->url,
    'request_url' => $request->url(),
    'pattern' => $pattern,
    'matches' => preg_match($pattern, $request->url())
]);
```

## Testing the Authentication System

### 1. Test Registration
```bash
# Generate API token
php artisan api:generate-token --expires=3600

# Register a new user
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "X-BugTrackApi: <your-jwt-token>" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 2. Test Login
```bash
# Login with the registered user
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "X-BugTrackApi: <your-jwt-token>" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 3. Test Protected Endpoint
```bash
# Access user profile (use token from login response)
curl -X GET http://localhost:8000/api/me \
  -H "X-BugTrackApi: <your-jwt-token>" \
  -H "Authorization: Bearer <sanctum-token>"
```

### 4. Test Unauthorized Access
```bash
# Try to access protected endpoint without authentication
curl -X GET http://localhost:8000/api/me \
  -H "X-BugTrackApi: <your-jwt-token>"
# Should return 401 Unauthorized
```

### 5. Test Logout
```bash
# Logout (revokes the current token)
curl -X POST http://localhost:8000/api/logout \
  -H "X-BugTrackApi: <your-jwt-token>" \
  -H "Authorization: Bearer <sanctum-token>"
```

## Conclusion

This authentication system provides:
- **Security**: API key + user token dual-layer protection
- **Simplicity**: Uses Laravel's standard Auth facade
- **Flexibility**: Support for both service and user authentication
- **Scalability**: Stateless design for horizontal scaling
- **Maintainability**: Laravel-native implementation

The system is production-ready and follows Laravel best practices for API authentication and authorization. 