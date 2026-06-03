# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - Initial Release

### Added
- **Backend (Laravel 12)**
  - REST API with versioned routes (`/api/v1`)
  - JWT authentication with refresh tokens
  - Role-Based Access Control (super-admin, admin, moderator, editor)
  - Full CRUD for apps, categories, users
  - IPA upload with metadata extraction (Info.plist)
  - Automatic manifest (plist) generation
  - Pluggable storage (Local, S3, Cloudflare R2)
  - Audit logging for all admin actions
  - i18n with English + Arabic translations
  - Rate limiting, CORS, CSRF, file validation
  - Clean architecture: Repositories + Services + Models

- **Frontend (Next.js 15)**
  - App Router with i18n routing
  - Public pages: Home, Apps list, App detail, Categories, Search
  - Admin pages: Dashboard, Apps, Categories, Users, Activity Log
  - Drag-and-drop friendly forms for IPA/Icon/Screenshots
  - Dark / Light theme with system preference
  - Full Arabic + English with RTL/LTR
  - JWT auth with auto-refresh and persistent session
  - Responsive design (mobile / tablet / desktop)
  - Skeleton loaders, toast notifications, modern UI

- **Infrastructure**
  - Docker Compose for full stack (MySQL, Redis, Backend, Frontend, Nginx)
  - Production-ready Dockerfiles with multi-stage builds
  - Nginx reverse proxy config
  - Helper scripts (install, reset, generate secrets)

- **Documentation**
  - README with quickstart
  - API Reference
  - Architecture overview
  - Deployment guide (Docker / Manual / Cloud)
  - Security notes
