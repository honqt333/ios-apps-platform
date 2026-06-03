# iOS Apps Distribution Platform

A production-grade iOS application distribution platform with a modern web interface, REST API, and full admin dashboard.

> Inspired by modern app stores — built for self-hosted iOS app distribution with IPA installation support, role-based access control, multi-language support (Arabic / English with full RTL/LTR), and dark/light themes.

---

## ✨ Features

### Public Site
- **App Browsing** — Browse apps in beautiful, responsive cards
- **App Details** — Screenshots carousel, full description, version history, bundle ID, size, system requirements
- **Search & Filter** — Search by name, developer, filter by category, sort by newest / most downloaded / alphabetical
- **Direct Install** — `itms-services://` links for one-tap IPA installation
- **i18n** — Full Arabic and English support with automatic RTL/LTR switching
- **Theme** — Dark and Light mode with system preference detection

### Admin Dashboard
- **Statistics Overview** — Total apps, downloads, users, recent activity
- **App Management** — Full CRUD: create, edit, delete, archive, activate/deactivate
- **Category Management** — CRUD operations
- **User Management** — Admins, moderators, editors with granular RBAC
- **Audit Logs** — Every action tracked
- **IPA Upload** — Drag-and-drop, auto-extract metadata, generate manifest, secure download links

### Technical Highlights
- **Backend** — Laravel 12 (PHP 8.3), Clean Architecture (Repository + Service pattern), SOLID
- **Frontend** — Next.js 15 (App Router), TypeScript, Tailwind CSS 4
- **Auth** — JWT with refresh tokens, role-based access control (RBAC)
- **Storage** — Pluggable: Local / Amazon S3 / Cloudflare R2
- **Security** — CSRF, rate limiting, file upload validation, audit logs
- **Database** — MySQL 8
- **i18n** — Full Arabic + English with RTL/LTR
- **Themes** — Dark / Light with system detection
- **Docker** — Full containerized stack
- **API** — Versioned REST API with auto-generated docs

---

## 📁 Project Structure

```
ios-platform/
├── backend/              # Laravel 12 REST API
├── frontend/             # Next.js 15 web app
├── nginx/                # Reverse proxy configuration
├── docker/               # Docker build contexts
├── docs/                 # API, Architecture, Deployment docs
└── scripts/              # Helper scripts
```

---

## 🚀 Quick Start (Docker)

The fastest way to run the full stack:

```bash
# 1. Clone / navigate to the project
cd ios-platform

# 2. Copy environment files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# 3. Generate application key
openssl rand -base64 32   # paste this into backend/.env as APP_KEY

# 4. Build and start
docker-compose up -d --build

# 5. Run migrations and seeders
docker-compose exec backend php artisan migrate --seed

# 6. Visit the site
open http://localhost:3000       # Frontend
open http://localhost:8000/api    # Backend API
```

**Default admin login** (after seeding): `admin@platform.local` / `password`

---

## 🛠 Manual Installation

See [`docs/DEPLOYMENT.md`](./docs/DEPLOYMENT.md) for full setup instructions.

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed
php artisan serve
```

### Frontend
```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

---

## 📚 Documentation

- 📖 [API Reference](./docs/API.md)
- 🏗 [Architecture](./docs/ARCHITECTURE.md)
- 🚢 [Deployment Guide](./docs/DEPLOYMENT.md)
- 🔒 [Security Notes](./docs/SECURITY.md)

---

## 📋 Tech Stack

| Layer       | Technology                              |
| ----------- | --------------------------------------- |
| Frontend    | Next.js 15, TypeScript, Tailwind CSS 4  |
| Backend     | Laravel 12, PHP 8.3                     |
| Database    | MySQL 8                                 |
| Auth        | JWT (tymon/jwt-auth)                    |
| Cache       | Redis                                   |
| Storage     | Local / S3 / Cloudflare R2              |
| Web Server  | Nginx                                   |
| Container   | Docker, Docker Compose                  |

---

## 📝 License

MIT — see [LICENSE](./LICENSE).
