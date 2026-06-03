# Backend (Laravel 12)

REST API for the iOS Apps Distribution Platform.

## 📋 Stack

- PHP 8.3
- Laravel 12
- MySQL 8
- Redis 7
- JWT (tymon/jwt-auth)
- RBAC (spatie/laravel-permission)
- Activity log (spatie/laravel-activitylog)
- File storage (local / S3 / R2)

## 🚀 Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

API: `http://localhost:8000/api/v1`

## 📂 Structure

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── Auth/
│   │       ├── Public/
│   │       └── Admin/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Providers/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
└── Services/
    ├── Auth/
    ├── Audit/
    ├── IpaParser/
    ├── Manifest/
    └── Storage/
config/
database/
├── migrations/
├── seeders/
└── factories/
lang/
├── en/
└── ar/
routes/
storage/
tests/
```

## 🔑 Default users (after seed)

| Email                   | Password | Role        |
| ----------------------- | -------- | ----------- |
| admin@platform.local    | password | super-admin |
| manager@platform.local  | password | admin       |
| editor@platform.local   | password | editor      |

## 🧪 Tests

```bash
php artisan test
```
