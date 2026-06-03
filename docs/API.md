# API Reference

Base URL: `http://localhost:8000/api/v1`

All responses follow this shape:

```json
{
  "success": true,
  "data": { ... },
  "message": "optional human-readable string"
}
```

Errors:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": { "field": ["error message"] }
}
```

---

## 🔓 Public Endpoints

### Apps

| Method | Endpoint                              | Description                |
| ------ | ------------------------------------- | -------------------------- |
| GET    | `/apps`                               | List apps (paginated)      |
| GET    | `/apps/featured`                      | Featured apps              |
| GET    | `/apps/most-downloaded`               | Top by downloads           |
| GET    | `/apps/recent`                        | Most recently added        |
| GET    | `/apps/{id-or-slug}`                  | App details                |
| POST   | `/apps/{id-or-slug}/track`            | Track install (count + URL)|
| GET    | `/apps/{id-or-slug}/download`         | Stream IPA file            |

**Query parameters** for `/apps`:
- `q` — search by name/developer/description/bundle id
- `category` — id or slug
- `developer` — partial name
- `sort` — `newest` | `downloads` | `name` | `oldest`
- `per_page` — 1..100
- `page` — page number

**Example**
```bash
curl http://localhost:8000/api/v1/apps?sort=downloads&category=1
```

### Categories

| Method | Endpoint              | Description              |
| ------ | --------------------- | ------------------------ |
| GET    | `/categories`         | List active categories   |
| GET    | `/categories/tree`    | Tree (with children)     |
| GET    | `/categories/{slug}`  | Single category          |

### Search

| Method | Endpoint     | Description                |
| ------ | ------------ | -------------------------- |
| GET    | `/search`    | Unified search endpoint    |

Same query params as `/apps`.

### Auth

| Method | Endpoint            | Description                |
| ------ | ------------------- | -------------------------- |
| POST   | `/auth/register`    | Register a new user        |
| POST   | `/auth/login`       | Login, returns JWT         |

**Login body**:
```json
{ "email": "user@example.com", "password": "secret123" }
```

**Response**:
```json
{
  "success": true,
  "data": {
    "user": { "id": 1, "name": "…", "email": "…", "roles": ["editor"] },
    "access_token": "eyJ…",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

## 🔐 Authenticated Endpoints

> All require `Authorization: Bearer <token>` header.

| Method | Endpoint                  | Description            |
| ------ | ------------------------- | ---------------------- |
| POST   | `/auth/logout`            | Invalidate token       |
| POST   | `/auth/refresh`           | Refresh token          |
| GET    | `/auth/me`                | Current user           |
| PATCH  | `/auth/me`                | Update profile         |
| POST   | `/auth/change-password`   | Change password        |

---

## 🛡 Admin Endpoints

> All require `Authorization: Bearer <token>` and admin role.

### Dashboard

| Method | Endpoint             | Description                  |
| ------ | -------------------- | ---------------------------- |
| GET    | `/admin/dashboard`   | Stats, recent apps, downloads|

### Apps

| Method | Endpoint                              | Description             |
| ------ | ------------------------------------- | ----------------------- |
| GET    | `/admin/apps`                         | List (incl. archived)   |
| POST   | `/admin/apps`                         | Create (multipart)      |
| GET    | `/admin/apps/{id}`                    | Get one                 |
| POST   | `/admin/apps/{id}`                    | Update (multipart, with `_method=PUT`) |
| DELETE | `/admin/apps/{id}`                    | Soft delete             |
| POST   | `/admin/apps/{id}/archive`            | Toggle archive          |
| POST   | `/admin/apps/{id}/toggle-active`      | Toggle active           |

**Create/Update body** (multipart/form-data):
- `name` (required), `developer` (required), `bundle_id` (required, unique)
- `version` (required), `minimum_ios_version` (required)
- `description`, `long_description`, `changelog` (optional)
- `category_id` (optional, int)
- `icon` (file, image)
- `ipa` (file, .ipa)
- `screenshots[]` (multiple image files)
- `is_active` (bool), `is_featured` (bool), `is_archived` (bool)

### Uploads

| Method | Endpoint                       | Description               |
| ------ | ------------------------------ | ------------------------- |
| POST   | `/admin/upload/ipa`            | Upload IPA for an app     |
| POST   | `/admin/upload/icon`           | Replace app icon          |
| POST   | `/admin/upload/screenshots`    | Append screenshots        |

### Categories

| Method | Endpoint                 | Description        |
| ------ | ------------------------ | ------------------ |
| GET    | `/admin/categories`      | List (paginated)   |
| POST   | `/admin/categories`      | Create             |
| GET    | `/admin/categories/{id}` | Get one            |
| PUT    | `/admin/categories/{id}` | Update             |
| DELETE | `/admin/categories/{id}` | Delete             |

### Users

| Method | Endpoint             | Description        |
| ------ | -------------------- | ------------------ |
| GET    | `/admin/users`       | List (paginated)   |
| POST   | `/admin/users`       | Create             |
| GET    | `/admin/users/{id}`  | Get one            |
| PUT    | `/admin/users/{id}`  | Update             |
| DELETE | `/admin/users/{id}`  | Delete             |

### Activity Log

| Method | Endpoint                  | Description            |
| ------ | ------------------------- | ---------------------- |
| GET    | `/admin/activity-logs`    | Audit log (paginated)  |

---

## 📱 IPA Install Flow

1. User taps **Install** on the app detail page.
2. Frontend calls `POST /apps/{slug}/track` to record the install + get the manifest URL.
3. Frontend redirects to: `itms-services://?action=download-manifest&url=<manifest-url>`
4. iOS fetches the manifest, which contains the IPA URL.
5. iOS downloads and installs the IPA.

**Manifest example** (`/storage/manifests/{slug}_{version}.plist`):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>https://yourdomain.com/storage/apps/1/ipa/app.ipa</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>com.example.app</string>
                <key>bundle-version</key>
                <string>1.0.0</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>My App</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>
```

---

## 🔒 Permissions Reference

| Permission           | Description                  |
| -------------------- | ---------------------------- |
| `app.view`           | View apps                    |
| `app.create`         | Create apps                  |
| `app.update`         | Update apps                  |
| `app.delete`         | Delete apps                  |
| `app.archive`        | Archive/unarchive            |
| `app.publish`        | Toggle active                |
| `app.upload`         | Upload IPA/screenshots       |
| `category.manage`    | CRUD categories              |
| `user.view`          | View users                   |
| `user.create`        | Create users                 |
| `user.update`        | Update users                 |
| `user.delete`        | Delete users                 |
| `user.assign-role`   | Assign roles                 |
| `audit.view`         | View activity logs           |
| `settings.manage`    | Manage platform settings     |

### Default roles

| Role          | Permissions                                                  |
| ------------- | ------------------------------------------------------------ |
| `super-admin` | All                                                           |
| `admin`       | All                                                           |
| `moderator`   | app.* (limited), category.*, user.view, audit.view            |
| `editor`      | app.view/create/update/upload, category.view                  |
