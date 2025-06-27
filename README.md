# 🐛 Bug Track API

A lightweight, RESTful API built with Laravel for managing and tracking software bugs, issues, and feature requests. This project is designed for teams and individuals who need a clean, backend-focused solution for bug tracking and reporting.

## 🚀 Features

- User authentication with Laravel Sanctum
- Create, update, and manage bug reports
- Assign bugs to users or teams
- Set priorities, statuses, and categories
- Comment system for bugs (discussions)
- Role-based access control (Admin, Developer, QA, etc.)
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

## 🔐 Authentication

The API uses **Laravel Sanctum** for token-based authentication. To access protected routes:

1. Register a user via `POST /api/register`
2. Login via `POST /api/login`
3. Use the returned token for subsequent requests:

```http
Authorization: Bearer YOUR_TOKEN_HERE
```

## 📦 API Endpoints (sample)

| Method | Endpoint              | Description            |
|--------|-----------------------|------------------------|
| POST   | /api/login            | Login user             |
| POST   | /api/register         | Register user          |
| GET    | /api/bugs             | List all bugs          |
| POST   | /api/bugs             | Create new bug         |
| GET    | /api/bugs/{id}        | Get bug detail         |
| PUT    | /api/bugs/{id}        | Update bug             |
| DELETE | /api/bugs/{id}        | Delete bug             |
| POST   | /api/logout           | Logout current session |

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