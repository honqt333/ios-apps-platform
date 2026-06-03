# Vercel + Railway Deployment Guide

This guide walks you through deploying the iOS Apps Distribution Platform to:

- **Vercel** for the Next.js frontend (free)
- **Railway** for the Laravel backend + MySQL + Redis (free trial, then ~$5/month)

**Total time: ~20 minutes**

---

## 🏗 Architecture

```
┌──────────────────┐       ┌──────────────────────┐
│   Vercel CDN     │       │   Railway            │
│   (Next.js)      │       │   ┌──────────────┐   │
│                  │ HTTPS │   │ Laravel API  │   │
│   your-app.      │ ◀──▶  │   │   (PHP-FPM)  │   │
│   vercel.app     │       │   └──────┬───────┘   │
│                  │       │          │           │
└──────────────────┘       │   ┌──────▼───────┐   │
                           │   │   MySQL      │   │
                           │   │   Redis      │   │
                           │   └──────────────┘   │
                           └──────────────────────┘
                                      │
                                      ▼
                            ┌────────────────────┐
                            │  Cloudflare R2     │
                            │  (App IPA files)   │
                            │  free egress       │
                            └────────────────────┘
```

---

## 📋 Prerequisites

- ✅ A GitHub account (you already have `ios-apps-platform` repo)
- ✅ A Vercel account — https://vercel.com (sign up with GitHub)
- ✅ A Railway account — https://railway.app (sign up with GitHub)
- ✅ A Cloudflare account — for R2 bucket (free tier, no credit card)

---

## Part 1 — Backend on Railway (10 minutes)

### 1.1 Create a new Railway project

1. Go to https://railway.app/dashboard
2. Click **+ New Project** → **Deploy from GitHub repo**
3. Select `honqt333/ios-apps-platform`
4. **Important**: Click on the service → **Settings** → set **Root Directory** to `backend`
5. Railway auto-detects Laravel via Nixpacks and starts building

### 1.2 Add MySQL database

1. In the same Railway project, click **+ New** → **Database** → **MySQL**
2. Wait for it to provision (~30 seconds)
3. Right-click MySQL service → **Variables** — note the `MYSQL*` variables

### 1.3 Add Redis (optional but recommended)

1. Click **+ New** → **Database** → **Redis**
2. Note the `REDIS*` variables

### 1.4 Link the database to your backend service

1. Click your **backend service** → **Variables**
2. Click **+ New Variable** → **Add Reference** → select `MYSQLHOST`, `MYSQLPORT`, etc.
3. Repeat for Redis variables (`REDISHOST`, `REDISPORT`, `REDISPASSWORD`)

### 1.5 Add Laravel-specific environment variables

In the same Variables tab, add these manually:

| Variable | Value |
|---|---|
| `APP_NAME` | `iOS Apps Platform` |
| `APP_ENV` | `production` |
| `APP_KEY` | *(generate below)* |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://<your-railway-domain>.up.railway.app` |
| `JWT_SECRET` | *(generate below)* |
| `JWT_TTL` | `60` |
| `JWT_REFRESH_TTL` | `20160` |
| `CACHE_STORE` | `redis` |
| `QUEUE_CONNECTION` | `redis` |
| `SESSION_DRIVER` | `redis` |
| `FILESYSTEM_DISK` | `r2` |
| `CORS_ALLOWED_ORIGINS` | `https://your-app.vercel.app` |
| `MANIFEST_BASE_URL` | `https://<your-railway-domain>.up.railway.app` |

**To generate `APP_KEY` and `JWT_SECRET`:**

Open Railway's shell (right-click backend service → **Shell**) and run:
```bash
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
php -r "echo 'JWT_SECRET=' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

Copy each line into Variables.

### 1.6 Set up Cloudflare R2 for file storage

Files uploaded via Railway are **ephemeral** (lost on restart). You need a persistent store.

1. Go to https://dash.cloudflare.com → **R2** → **Create bucket** (e.g. `ios-platform-apps`)
2. **Settings** tab → enable **Public Development URL** → copy the URL
3. **R2** → **Manage R2 API Tokens** → **Create API token** with **Object Read & Write** permission
4. Note the **Access Key ID**, **Secret Access Key**, and **Endpoint** (looks like `https://<account_id>.r2.cloudflarestorage.com`)

Add these to Railway Variables:

| Variable | Value |
|---|---|
| `AWS_ACCESS_KEY_ID` | R2 access key |
| `AWS_SECRET_ACCESS_KEY` | R2 secret key |
| `AWS_DEFAULT_REGION` | `auto` |
| `AWS_BUCKET` | `ios-platform-apps` |
| `AWS_ENDPOINT` | `https://<account_id>.r2.cloudflarestorage.com` |
| `AWS_URL` | `<your R2 public URL>` |

### 1.7 Deploy!

1. Click **Deploy** (or push a commit to trigger auto-deploy)
2. Watch the build logs — should complete in 2-3 minutes
3. Once running, click **Settings** → **Networking** → **Generate Domain**
4. Copy the URL — this is your API URL: `https://<something>.up.railway.app`

### 1.8 Verify backend

Open in browser:
- `https://<your-railway-domain>.up.railway.app/up` — should return `{"status":"ok"}`
- `https://<your-railway-domain>.up.railway.app/api/v1/categories` — should return categories

Migrations and seeders run **automatically** on first deploy (configured in `nixpacks.toml`).

---

## Part 2 — Frontend on Vercel (5 minutes)

### 2.1 Create a new Vercel project

1. Go to https://vercel.com/dashboard
2. Click **Add New…** → **Project**
3. **Import** `honqt333/ios-apps-platform`
4. Configure:
   - **Framework Preset**: Next.js (auto-detected)
   - **Root Directory**: `frontend`
   - **Build Command**: `npm run build` (default)
   - **Output Directory**: `.next` (default)
5. **Environment Variables** — add these (use the Railway URL from step 1.7):

| Variable | Value |
|---|---|
| `NEXT_PUBLIC_SITE_URL` | `https://<your-vercel-domain>.vercel.app` |
| `NEXT_PUBLIC_API_URL` | `https://<your-railway-domain>.up.railway.app/api` |
| `NEXT_PUBLIC_APP_NAME` | `iOS Apps Platform` |
| `NEXT_PUBLIC_DEFAULT_LOCALE` | `en` |
| `NEXT_PUBLIC_SUPPORTED_LOCALES` | `en,ar` |
| `NEXT_PUBLIC_DEFAULT_THEME` | `system` |

6. Click **Deploy** — build takes 1-2 minutes
7. Once live, you get a URL: `https://<project-name>.vercel.app`

### 2.2 Update backend CORS

Go back to Railway backend → Variables:
- Set `CORS_ALLOWED_ORIGINS` to your actual Vercel domain
- Railway will auto-redeploy

### 2.3 Test!

Open `https://<your-vercel-domain>.vercel.app`:
- ✅ Homepage loads with apps
- ✅ Arabic/English switch works
- ✅ Dark/Light mode works
- ✅ Login works
- ✅ Admin dashboard accessible after login

---

## Part 3 — Custom Domain (Optional)

### Vercel

1. Project → **Settings** → **Domains**
2. Add your domain (e.g. `apps.yourdomain.com`)
3. Add the DNS records Vercel shows you at your registrar

### Railway

1. Service → **Settings** → **Networking** → **Custom Domain**
2. Add `api.yourdomain.com`
3. Add the CNAME record at your registrar

After setting up the custom domain, update the env vars:
- In Vercel: `NEXT_PUBLIC_API_URL` → `https://api.yourdomain.com/api`
- In Railway: `APP_URL` and `MANIFEST_BASE_URL` → `https://api.yourdomain.com`

---

## 🔐 Post-Deployment Checklist

- [ ] **Change default admin password** — Login with `admin@platform.local` / `password` and update immediately
- [ ] **Disable demo accounts** — Delete the seeded `editor@` and `manager@` users
- [ ] **Set up backups** — Railway auto-backs-up MySQL, but consider exporting R2 data periodically
- [ ] **Add a real mail provider** — Set `MAIL_MAILER=smtp` and use Resend/SendGrid/Mailgun
- [ ] **Set up monitoring** — Sentry.io has a free tier, integrates with both Vercel and Laravel
- [ ] **Custom 404 page** — Already handled by the Next.js not-found page
- [ ] **Verify IPA install** — Upload a test app, get the `itms-services://` link, test on a real iOS device

---

## 🐛 Troubleshooting

### "CORS error" in browser console
- Make sure `CORS_ALLOWED_ORIGINS` in Railway includes your exact Vercel URL (no trailing slash)
- Wait 30 seconds after saving — Railway redeploys automatically

### "API unreachable" 
- Check `NEXT_PUBLIC_API_URL` in Vercel — must end with `/api`
- Check Railway service is running (green status)
- Try `curl https://<railway-url>/up` — should return OK

### "Storage error" when uploading
- Verify R2 credentials in Railway Variables
- Check R2 bucket exists and is public-readable
- Make sure `AWS_URL` is the **public** URL, not the endpoint

### Migrations didn't run
- Open Railway shell on backend service
- Run: `php artisan migrate --seed --force`

### Need to clear cache
- Railway shell: `php artisan optimize:clear && php artisan config:cache && php artisan route:cache`

---

## 💰 Cost Estimate

| Service | Free Tier | Production Cost |
|---|---|---|
| **Vercel** | 100GB bandwidth/mo | Free for hobby, $20/mo Pro |
| **Railway** | $5 trial credit | ~$5-10/mo (Laravel + MySQL + Redis) |
| **Cloudflare R2** | 10GB storage, 1M reads/mo | ~$0.015/GB after free tier |
| **Total** | **$0 to start** | **~$5-30/mo** |

---

## 🚀 Next Steps

1. **Custom branding** — Update colors in `frontend/tailwind.config.ts` and `src/app/globals.css`
2. **Add custom domain** — See Part 3 above
3. **Upload your first app** — Admin → Apps → Add App
4. **Test IPA install** — On a real iOS device, tap the install button

You're live! 🎉
