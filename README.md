# 🐛 Bug Track API

A modern bug tracking API built with Laravel 12, featuring a robust dual-layer authentication system.

## 🔐 Authentication System

This API uses a **dual-layer authentication approach**:

1. **API Key Authentication** (JWT-based) - For service-to-service communication
2. **User Authentication** (Laravel Sanctum) - For user-specific operations

### Quick Start

1. **Generate API Token:**
```bash
php artisan api:generate-token
```

2. **Register a User:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "X-BugTrackApi: your-jwt-token" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

3. **Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "X-BugTrackApi: your-jwt-token" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

4. **Access Protected Endpoints:**
```bash
curl -X GET http://localhost:8000/api/me \
  -H "X-BugTrackApi: your-jwt-token" \
  -H "Authorization: Bearer user-access-token"
```

📖 **Full Authentication Guide**: See [docs/AUTHENTICATION.md](docs/AUTHENTICATION.md)

## 🚀 Features

- **Dual Authentication**: API keys + user tokens
- **JWT-based API Keys**: Secure service-to-service communication
- **Laravel Sanctum**: Modern token-based user authentication
- **Role-based Authorization**: Flexible permission system
- **Rate Limiting**: Built-in protection against abuse
- **Comprehensive Logging**: Security event tracking
- Create, update, and manage bug reports
- Assign bugs to users or teams
- Set priorities, statuses, and categories
- Comment system for bugs (discussions)
- API resource formatting and validation
- RESTful JSON API responses

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
routes/
├── api.php
```

## 🛠️ Installation

1. **Clone the repository**

```bash
git clone https://github.com/musafci/bug-track-api.git
cd bug-track-api
```

2. **Install dependencies**

```bash
composer install
```

3. **Set up environment**

```bash
cp .env.example .env
php artisan key:generate
```

Update the `.env` file with your database and mail credentials.

4. **Run migrations**

```bash
php artisan migrate
```

5. **Start the server**

```bash
php artisan serve
```

## 🔧 Configuration

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

## 📦 API Endpoints (sample)

| Method | Endpoint              | Description            | Auth Required |
|--------|-----------------------|------------------------|---------------|
| POST   | /api/register         | Register new user      | API Key       |
| POST   | /api/login            | User login            | API Key       |
| GET    | /api/me               | Get current user       | Both          |
| POST   | /api/logout           | Logout current session | Both          |
| POST   | /api/logout-all       | Logout all sessions    | Both          |
| POST   | /api/refresh          | Refresh access token   | Both          |
| GET    | /api/user             | Get user information   | Both          |
| GET    | /api/bugs             | List all bugs          | Both          |
| POST   | /api/bugs             | Create new bug         | Both          |
| GET    | /api/bugs/{id}        | Get bug detail         | Both          |
| PUT    | /api/bugs/{id}        | Update bug             | Both          |
| DELETE | /api/bugs/{id}        | Delete bug             | Both          |

📘 Full documentation coming soon...

## 📌 Tech Stack

- Laravel 12+
- PHP 8.3+
- MySQL
- Laravel Sanctum (API Auth)
- Laravel Resource Transformers

## 🧪 Testing

```bash
php artisan test
```

## 🤝 Contribution

PRs and Issues are welcome! Please submit a detailed pull request with a description of your changes.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/something`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature/something`)
5. Open a pull request

## 📄 License

This project is open-source and available under the [MIT license](LICENSE).

---

**Developed with ❤️ by [Musa](https://github.com/musafci)**