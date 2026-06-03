# Architecture

This document describes the high-level architecture of the iOS Apps Distribution Platform.

## 🎯 Goals

- **Clean separation** between business logic and infrastructure
- **SOLID principles** with strict dependency direction
- **Testable** through interface-driven services
- **Pluggable storage** (Local, S3, R2) without code changes
- **First-class i18n** and theming
- **Production-ready** with proper security, observability, and audit

## 🏛 Layered Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Presentation Layer                      │
│  ┌────────────────────┐        ┌────────────────────┐      │
│  │  Next.js 15 (RSC)  │  HTTPS │  Controllers (API) │      │
│  │  + Tailwind + i18n │ ─────▶ │  + Form Requests   │      │
│  └────────────────────┘        └────────────────────┘      │
└──────────────────────────┬──────────────────────────────────┘
                           │ JSON / Multipart
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                       Application Layer                      │
│  ┌─────────────────┐  ┌──────────────────┐ ┌─────────────┐  │
│  │ AuthService     │  │ ManifestService  │ │ AuditService│  │
│  │ IpaParserService│  │ StorageService   │ │ ...         │  │
│  └────────┬────────┘  └─────────┬────────┘ └──────┬──────┘  │
│           │                     │                  │         │
│  ─────────┴─────────────────────┴──────────────────┘         │
│  Business logic, orchestration, transactions                 │
└──────────────────────────┬──────────────────────────────────┘
                           │ depends on interfaces
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  ┌──────────────────────┐  ┌──────────────────────┐          │
│  │  Eloquent Models     │  │  Repository Contracts│          │
│  │  + Relationships     │  │  (Base, App, User,…) │          │
│  │  + Scopes            │  │                      │          │
│  └──────────────────────┘  └──────────────────────┘          │
└──────────────────────────┬──────────────────────────────────┘
                           │ Eloquent / SQL
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                     Infrastructure Layer                     │
│  MySQL · Redis · S3 / R2 / Local · Filesystem · Mail         │
└─────────────────────────────────────────────────────────────┘
```

## 🧩 Component Map

### Backend

| Concern              | Where it lives                                      |
| -------------------- | --------------------------------------------------- |
| HTTP entry           | `app/Http/Controllers/Api/...`                       |
| Request validation   | `app/Http/Requests/...`                              |
| API resources        | `app/Http/Resources/...`                             |
| Authorization        | `app/Policies/...` + `app/Http/Middleware/...`        |
| Business logic       | `app/Services/...` (one per bounded concept)          |
| Data access          | `app/Repositories/Eloquent/...` (impls of Contracts) |
| Domain entities      | `app/Models/...`                                     |
| Translations         | `lang/{en,ar}/...`                                   |
| Configuration        | `config/...`                                         |
| Migrations / seeds   | `database/migrations/...` + `database/seeders/...`    |

### Frontend

| Concern              | Where it lives                          |
| -------------------- | --------------------------------------- |
| Routes               | `src/app/[locale]/...`                  |
| Layouts              | `src/app/[locale]/layout.tsx`           |
| Server data          | Server components in `app/[locale]/...` |
| Client data          | `src/services/...` (axios + stores)     |
| Global state         | `src/stores/...` (Zustand)              |
| UI primitives        | `src/components/ui/...`                 |
| Feature components   | `src/components/{apps,admin,layout}/...`|
| i18n                 | `src/i18n/locales/{en,ar}.json`         |
| Providers            | `src/components/providers/...`          |

## 🔐 Security Model

1. **Authentication** — JWT (`tymon/jwt-auth`) with refresh tokens and blacklist.
2. **Authorization** — Role + permission based (Spatie's RBAC). Each controller checks via middleware or policies.
3. **Input validation** — Every request goes through a dedicated FormRequest class.
4. **CSRF** — Sanctum middleware on web routes.
5. **Rate limiting** — Laravel's `throttle` middleware applied to all API routes.
6. **File upload** — MIME + extension check, max size enforcement, stored in non-public paths.
7. **Audit log** — All admin writes captured via the `AuditActivity` middleware.
8. **CORS** — Whitelist via `config/cors.php`.

## 🌐 Internationalization

- **Backend** — Locale detected from `?lang=`, `X-Locale` header, `Accept-Language`, or user pref.
- **Frontend** — `next-intl` with `ar` + `en` locales; full RTL/LTR via `<html dir>`.
- **Translations** — `lang/{en,ar}/*.php` on the backend; `src/i18n/locales/{en,ar}.json` on the frontend.

## 🌓 Theming

CSS variables defined in `globals.css`. Light + dark mode with system preference detection via `next-themes`-style provider in `ThemeProvider.tsx`. Direction is set per-locale.

## 💾 Storage Abstraction

`StorageService` accepts any `Filesystem` driver. The active disk is driven by `FILESYSTEM_DISK` env. Files for IPA are stored on the configured disk (local/s3/r2); icons and screenshots are stored on the public disk and served via `/storage/...`.

## 🔄 IPA Install Flow

```
[Admin uploads IPA]                 [User taps Install]
        │                                    │
        ▼                                    ▼
  IpaParserService                   track endpoint
  (extract Info.plist)              (record + return URL)
        │                                    │
        ▼                                    ▼
  ManifestService                itms-services:// URL
  (build plist, write)                  │
        │                                ▼
        ▼                          iOS fetches plist
  /storage/manifests/*.plist           │
                                        ▼
                                   iOS downloads IPA
                                        │
                                        ▼
                                  iOS installs app
```

## 🧪 Testing Strategy

- **Unit tests** for services and repositories (in `tests/Unit/`)
- **Feature tests** for HTTP endpoints (in `tests/Feature/`)
- **Database** in-memory SQLite for fast tests
- **Factories** in `database/factories/`

## 📦 Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md).
