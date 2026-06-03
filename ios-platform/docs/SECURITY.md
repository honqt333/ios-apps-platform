# Security Notes

## ✅ Built-in Protections

| Protection                | Where it lives                                              |
| ------------------------- | ----------------------------------------------------------- |
| JWT authentication        | `tymon/jwt-auth`, `config/jwt.php`                          |
| Refresh tokens            | JWT blacklist + `/auth/refresh` endpoint                   |
| Role-based access control | `spatie/laravel-permission` + `config/permission.php`       |
| Permission checks         | `App\Http\Middleware\EnsureUserHasPermission`               |
| Input validation          | `App\Http\Requests\*`                                       |
| CSRF protection           | Laravel CSRF middleware (web routes)                        |
| Rate limiting             | `throttle` middleware                                       |
| File upload validation    | `Upload\*` form requests, MIME + size checks                |
| Audit logging             | `spatie/laravel-activitylog` + `AuditActivity` middleware   |
| CORS                      | `config/cors.php`                                           |
| Hashing                   | bcrypt for passwords, sha256 for file integrity             |

## 🔐 Best practices

1. **Always** rotate `APP_KEY` and `JWT_SECRET` per environment.
2. **Never** enable `APP_DEBUG=true` in production.
3. **Restrict** `CORS_ALLOWED_ORIGINS` to your actual domains.
4. **Use HTTPS** in production. Set `APP_URL` and `NEXT_PUBLIC_API_URL` to `https://...`.
5. **Use S3 / R2** for production file storage; local storage should only be used for development.
6. **Enable backups** for both MySQL and the storage bucket.
7. **Monitor** the activity log for suspicious actions.
8. **Restrict** the admin role assignment; only super-admins should be able to grant admin.
9. **Validate** every IPA file before making it public — never serve a file with `is_active=false` or `is_archived=true`.
10. **Set** strong passwords for the default `admin@platform.local` user; better, delete the demo account after first login.

## 🛡 IPA-specific security

- IPA files are stored on the configured storage disk; they are never exposed directly to the public.
- The plist manifest is regenerated on every IPA upload.
- The download URL is signed via Laravel's storage URL (when using S3/R2).
- Optionally enable a time-limited download token in `platform.downloads.token_ttl_hours`.

## 🚨 Reporting vulnerabilities

If you discover a security issue, please open a private issue or contact the maintainers.
