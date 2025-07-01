# API Response Format Documentation

## Overview

This document describes the standardized API response format used throughout the BugTrack API. All API responses follow a consistent structure to ensure uniformity and ease of integration for frontend applications and third-party consumers.

## Response Structure

### Success Response Format

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data here
  },
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 200
}
```

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "data": null,
  "errors": {
    // Validation errors or additional error details
  },
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 400
}
```

## Response Methods

### Success Responses

#### 1. `successResponse($data, $message, $code)`
Returns a successful response with data.

```php
return $this->successResponse($userData, 'User retrieved successfully');
```

**Response:**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 200
}
```

#### 2. `successMessage($message, $code)`
Returns a success response without data (for operations like delete, logout).

```php
return $this->successMessage('User deleted successfully');
```

**Response:**
```json
{
  "success": true,
  "message": "User deleted successfully",
  "data": null,
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 200
}
```

#### 3. `createdResponse($data, $message)`
Returns a 201 Created response.

```php
return $this->createdResponse($newUser, 'User created successfully');
```

**Response:**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 201
}
```

#### 4. `noContentResponse()`
Returns a 204 No Content response.

```php
return $this->noContentResponse();
```

**Response:** `204 No Content` (no body)

### Error Responses

#### 1. `errorResponse($message, $code, $errors)`
Returns a generic error response.

```php
return $this->errorResponse('Something went wrong', 500);
```

**Response:**
```json
{
  "success": false,
  "message": "Something went wrong",
  "data": null,
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 500
}
```

#### 2. `validationErrorResponse($errors, $message)`
Returns a 422 validation error response.

```php
return $this->validationErrorResponse($validator->errors(), 'Validation failed');
```

**Response:**
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 422
}
```

#### 3. `notFoundResponse($message)`
Returns a 404 Not Found response.

```php
return $this->notFoundResponse('User not found');
```

#### 4. `unauthorizedResponse($message)`
Returns a 401 Unauthorized response.

```php
return $this->unauthorizedResponse('Invalid credentials');
```

#### 5. `forbiddenResponse($message)`
Returns a 403 Forbidden response.

```php
return $this->forbiddenResponse('Insufficient permissions');
```

#### 6. `conflictResponse($message)`
Returns a 409 Conflict response.

```php
return $this->conflictResponse('Email already exists');
```

#### 7. `tooManyRequestsResponse($message)`
Returns a 429 Too Many Requests response.

```php
return $this->tooManyRequestsResponse('Rate limit exceeded');
```

#### 8. `serverErrorResponse($message)`
Returns a 500 Internal Server Error response.

```php
return $this->serverErrorResponse('Internal server error');
```

### Special Response Types

#### 1. `paginatedResponse($data, $message)`
Returns paginated data with pagination metadata.

```php
$users = User::paginate(10);
return $this->paginatedResponse($users, 'Users retrieved successfully');
```

**Response:**
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "last_page": 3,
    "from": 1,
    "to": 10,
    "has_more_pages": true
  },
  "timestamp": "2024-01-01T12:00:00.000000Z",
  "status_code": 200
}
```

#### 2. `collectionResponse($data, $message)`
Returns a collection of items.

```php
$users = User::all();
return $this->collectionResponse($users, 'All users retrieved');
```

#### 3. `resourceResponse($data, $message)`
Returns a single resource.

```php
$user = User::find(1);
return $this->resourceResponse($user, 'User retrieved');
```

### Exception Handling

#### 1. `handleValidationException($exception)`
Handles Laravel validation exceptions.

```php
try {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:8'
    ]);
} catch (ValidationException $e) {
    return $this->handleValidationException($e);
}
```

#### 2. `handleException($exception, $message)`
Handles general exceptions with logging.

```php
try {
    // Some operation
} catch (\Exception $e) {
    return $this->handleException($e, 'Failed to process request');
}
```

## Usage in Controllers

### Extending BaseApiController

All API controllers should extend `BaseApiController` to automatically include the response methods:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function index(): JsonResponse
    {
        try {
            $users = User::paginate(10);
            return $this->paginatedResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to retrieve users');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users'
            ]);

            $user = User::create($request->validated());
            return $this->createdResponse($user, 'User created successfully');
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to create user');
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            return $this->resourceResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('User not found');
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return $this->successMessage('User deleted successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to delete user');
        }
    }
}
```

### Using the Trait Directly

If you prefer to use the trait directly:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->successResponse(User::all(), 'Users retrieved');
    }
}
```

## HTTP Status Codes

The API uses standard HTTP status codes:

- **200 OK**: Successful GET, PUT, PATCH requests
- **201 Created**: Successful POST requests
- **204 No Content**: Successful DELETE requests
- **400 Bad Request**: Invalid request syntax
- **401 Unauthorized**: Authentication required
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Resource not found
- **409 Conflict**: Resource conflict (e.g., duplicate email)
- **422 Unprocessable Entity**: Validation errors
- **429 Too Many Requests**: Rate limit exceeded
- **500 Internal Server Error**: Server error
- **503 Service Unavailable**: Service temporarily unavailable

## Best Practices

### 1. Consistent Error Messages
Use clear, user-friendly error messages:

```php
// Good
return $this->errorResponse('Email address is already registered');

// Bad
return $this->errorResponse('SQLSTATE[23000]: Integrity constraint violation');
```

### 2. Proper Exception Handling
Always wrap controller methods in try-catch blocks:

```php
public function store(Request $request): JsonResponse
{
    try {
        // Business logic
        return $this->createdResponse($data, 'Resource created');
    } catch (ValidationException $e) {
        return $this->handleValidationException($e);
    } catch (\Exception $e) {
        return $this->handleException($e, 'Failed to create resource');
    }
}
```

### 3. Meaningful Success Messages
Provide descriptive success messages:

```php
// Good
return $this->successResponse($user, 'User profile updated successfully');

// Bad
return $this->successResponse($user, 'OK');
```

### 4. Consistent Data Structure
Maintain consistent data structure across endpoints:

```php
// User data structure
[
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => '2024-01-01T12:00:00.000000Z',
    'updated_at' => '2024-01-01T12:00:00.000000Z'
]
```

## Frontend Integration

### JavaScript/TypeScript Example

```typescript
interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T | null;
  errors?: Record<string, string[]>;
  timestamp: string;
  status_code: number;
}

interface PaginatedResponse<T> extends ApiResponse<T[]> {
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
    has_more_pages: boolean;
  };
}

// API client function
async function apiRequest<T>(url: string, options?: RequestInit): Promise<ApiResponse<T>> {
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'X-BugTrackApi': 'your-jwt-token',
      'Authorization': 'Bearer your-sanctum-token',
    },
    ...options,
  });

  const data = await response.json();
  
  if (!data.success) {
    throw new Error(data.message);
  }
  
  return data;
}

// Usage
try {
  const response = await apiRequest<User[]>('/api/users');
  console.log(response.data); // Array of users
  console.log(response.message); // "Users retrieved successfully"
} catch (error) {
  console.error('API Error:', error.message);
}
```

## Testing

### PHPUnit Test Example

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    public function test_can_retrieve_users()
    {
        $user = User::factory()->create();
        
        $response = $this->getJson('/api/users');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data',
                    'timestamp',
                    'status_code'
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Users retrieved successfully'
                ]);
    }

    public function test_returns_404_for_nonexistent_user()
    {
        $response = $this->getJson('/api/users/999');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'User not found'
                ]);
    }
}
```

This standardized response format ensures consistency across all API endpoints and makes it easier for frontend developers to handle responses uniformly. 