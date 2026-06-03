# دليل النشر على Vercel + Railway (بالعربي)

دليل خطوة بخطوة لنشر **iOS Apps Distribution Platform** على:

- **Vercel** للواجهة (Next.js) — مجاناً
- **Railway** للـ backend (Laravel) + MySQL + Redis — تجربة مجانية، بعدها ~$5/شهر

**الوقت الكلي: ~20 دقيقة**

---

## 🏗 المعمارية

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
                            │  (ملفات IPA)        │
                            │  خروج مجاني         │
                            └────────────────────┘
```

---

## 📋 المتطلبات الأساسية

- ✅ حساب GitHub (عندك `ios-apps-platform` بالفعل)
- ✅ حساب Vercel — https://vercel.com (سجّل بـ GitHub)
- ✅ حساب Railway — https://railway.app (سجّل بـ GitHub)
- ✅ حساب Cloudflare — للـ R2 bucket (مجاني، بدون بطاقة)

---

## الجزء 1 — الـ Backend على Railway (10 دقايق)

### 1.1 إنشاء مشروع Railway جديد

1. روح https://railway.app/dashboard
2. اضغط **+ New Project** → **Deploy from GitHub repo**
3. اختار `honqt333/ios-apps-platform`
4. **مهم**: اضغط على الـ service → **Settings** → حط **Root Directory** = `backend`
5. Railway هيكتشف Laravel تلقائياً ويبدأ البناء

### 1.2 إضافة قاعدة بيانات MySQL

1. في نفس المشروع، اضغط **+ New** → **Database** → **MySQL**
2. استنى 30 ثانية لحد ما يجهز
3. كليك يمين على MySQL → **Variables** — انسخ المتغيرات `MYSQL*`

### 1.3 إضافة Redis (اختياري بس مستحسن)

1. اضغط **+ New** → **Database** → **Redis**
2. انسخ المتغيرات `REDIS*`

### 1.4 ربط الـ database بالـ backend

1. اضغط على **backend service** → **Variables**
2. اضغط **+ New Variable** → **Add Reference** → اختار `MYSQLHOST`, `MYSQLPORT`, إلخ
3. كرر ل Redis (`REDISHOST`, `REDISPORT`, `REDISPASSWORD`)

### 1.5 إضافة متغيرات Laravel

في نفس Variables tab، ضيف دول يدوياً:

| المتغير | القيمة |
|---|---|
| `APP_NAME` | `iOS Apps Platform` |
| `APP_ENV` | `production` |
| `APP_KEY` | *(هنولّدها تحت)* |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://<your-railway-domain>.up.railway.app` |
| `JWT_SECRET` | *(هنولّدها تحت)* |
| `JWT_TTL` | `60` |
| `JWT_REFRESH_TTL` | `20160` |
| `CACHE_STORE` | `redis` |
| `QUEUE_CONNECTION` | `redis` |
| `SESSION_DRIVER` | `redis` |
| `FILESYSTEM_DISK` | `r2` |
| `CORS_ALLOWED_ORIGINS` | `https://your-app.vercel.app` |
| `MANIFEST_BASE_URL` | `https://<your-railway-domain>.up.railway.app` |

**لتوليد `APP_KEY` و `JWT_SECRET`:**

افتح Railway Shell (كليك يمين على backend → **Shell**) ونفّذ:
```bash
php -r "echo 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
php -r "echo 'JWT_SECRET=' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

انسخ كل سطر في Variables.

### 1.6 إعداد Cloudflare R2 للتخزين

الملفات على Railway **مؤقتة** (تتمسح مع كل restart). محتاج تخزين دائم.

1. روح https://dash.cloudflare.com → **R2** → **Create bucket** (مثلاً `ios-platform-apps`)
2. تبويب **Settings** → فعّل **Public Development URL** → انسخ الـ URL
3. **R2** → **Manage R2 API Tokens** → **Create API token** بـ **Object Read & Write**
4. انسخ: **Access Key ID**, **Secret Access Key**, و **Endpoint** (شكله `https://<account_id>.r2.cloudflarestorage.com`)

ضيف دول في Railway Variables:

| المتغير | القيمة |
|---|---|
| `AWS_ACCESS_KEY_ID` | R2 access key |
| `AWS_SECRET_ACCESS_KEY` | R2 secret key |
| `AWS_DEFAULT_REGION` | `auto` |
| `AWS_BUCKET` | `ios-platform-apps` |
| `AWS_ENDPOINT` | `https://<account_id>.r2.cloudflarestorage.com` |
| `AWS_URL` | `<R2 public URL>` |

### 1.7 Deploy!

1. اضغط **Deploy** (أو ادفع commit عشان يعمل auto-deploy)
2. بص على build logs — هياخد 2-3 دقايق
3. بعد ما يشتغل، روح **Settings** → **Networking** → **Generate Domain**
4. انسخ الـ URL — ده الـ API URL بتاعك: `https://<something>.up.railway.app`

### 1.8 تأكد إن الـ backend شغّال

افتح في المتصفح:
- `https://<your-railway-domain>.up.railway.app/up` — لازم يرد `{"status":"ok"}`
- `https://<your-railway-domain>.up.railway.app/api/v1/categories` — لازم يرد بالفئات

الـ migrations والـ seeders بتتنفذ **تلقائياً** في أول deploy (متضبطين في `nixpacks.toml`).

---

## الجزء 2 — الواجهة على Vercel (5 دقايق)

### 2.1 إنشاء مشروع Vercel جديد

1. روح https://vercel.com/dashboard
2. اضغط **Add New…** → **Project**
3. **Import** `honqt333/ios-apps-platform`
4. الإعدادات:
   - **Framework Preset**: Next.js (متعرف تلقائي)
   - **Root Directory**: `frontend`
   - **Build Command**: `npm run build` (افتراضي)
   - **Output Directory**: `.next` (افتراضي)
5. **Environment Variables** — ضيف دول (استخدم Railway URL من 1.7):

| المتغير | القيمة |
|---|---|
| `NEXT_PUBLIC_SITE_URL` | `https://<your-vercel-domain>.vercel.app` |
| `NEXT_PUBLIC_API_URL` | `https://<your-railway-domain>.up.railway.app/api` |
| `NEXT_PUBLIC_APP_NAME` | `iOS Apps Platform` |
| `NEXT_PUBLIC_DEFAULT_LOCALE` | `en` |
| `NEXT_PUBLIC_SUPPORTED_LOCALES` | `en,ar` |
| `NEXT_PUBLIC_DEFAULT_THEME` | `system` |

6. اضغط **Deploy** — build هياخد 1-2 دقيقة
7. بعد ما يخلص، هيظهر URL: `https://<project-name>.vercel.app`

### 2.2 حدّث CORS في الـ backend

ارجع لـ Railway backend → Variables:
- حط `CORS_ALLOWED_ORIGINS` = الـ Vercel domain بتاعك بالظبط
- Railway هيـ auto-redeploy

### 2.3 جرّب!

افتح `https://<your-vercel-domain>.vercel.app`:
- ✅ الصفحة الرئيسية تفتح وفيها apps
- ✅ التبديل بين العربي والإنجليزي شغّال
- ✅ الـ Dark/Light mode شغّال
- ✅ الـ Login شغّال
- ✅ الـ Admin dashboard يفتح بعد الـ login

---

## الجزء 3 — دومين مخصص (اختياري)

### على Vercel

1. Project → **Settings** → **Domains**
2. ضيف دومينك (مثلاً `apps.yourdomain.com`)
3. ضيف DNS records اللي Vercel يقولك عليها عند الـ registrar بتاعك

### على Railway

1. Service → **Settings** → **Networking** → **Custom Domain**
2. ضيف `api.yourdomain.com`
3. ضيف CNAME record عند الـ registrar

بعد ما تعمل الدومين المخصص، حدّث الـ env vars:
- في Vercel: `NEXT_PUBLIC_API_URL` → `https://api.yourdomain.com/api`
- في Railway: `APP_URL` و `MANIFEST_BASE_URL` → `https://api.yourdomain.com`

---

## 🔐 Checklist بعد النشر

- [ ] **غيّر باسورد الأدمن الافتراضي** — ادخل بـ `admin@platform.local` / `password` وغيّره فوراً
- [ ] **عطّل الحسابات التجريبية** — امسح الـ seeded `editor@` و `manager@` users
- [ ] **فعّل الـ backups** — Railway بياخد backup تلقائي للـ MySQL، بس خد export للـ R2 data بين فترة والتانية
- [ ] **حط mail provider حقيقي** — `MAIL_MAILER=smtp` واستخدم Resend/SendGrid/Mailgun
- [ ] **فعّل الـ monitoring** — Sentry.io فيه free tier، يتكامل مع Vercel و Laravel
- [ ] **جرّب install لـ IPA** — ارفع تطبيق تجريبي، خد الـ `itms-services://` link، وجرّبه على iOS device حقيقي

---

## 🐛 حل المشاكل

### "CORS error" في الـ console
- تأكد إن `CORS_ALLOWED_ORIGINS` في Railway فيه Vercel URL بالظبط (بدون `/` في الآخر)
- استنى 30 ثانية بعد الحفظ — Railway بيعمل redeploy

### "API unreachable"
- تأكد من `NEXT_PUBLIC_API_URL` في Vercel — لازم يخلص بـ `/api`
- تأكد إن Railway service شغّال (status أخضر)
- جرب `curl https://<railway-url>/up` — لازم يرد OK

### "Storage error" عند رفع ملف
- تأكد من R2 credentials في Railway Variables
- تأكد إن الـ R2 bucket موجود وإنه public-readable
- تأكد إن `AWS_URL` هو الـ **public** URL مش الـ endpoint

### الـ migrations ما اتنفذتش
- افتح Railway shell على backend service
- نفّذ: `php artisan migrate --seed --force`

### عايز تفضي الـ cache
- Railway shell: `php artisan optimize:clear && php artisan config:cache && php artisan route:cache`

---

## 💰 التكلفة المتوقعة

| الخدمة | Free Tier | تكلفة الإنتاج |
|---|---|---|
| **Vercel** | 100GB bandwidth/شهر | مجاني للـ hobby، $20/شهر Pro |
| **Railway** | $5 رصيد تجريبي | ~$5-10/شهر (Laravel + MySQL + Redis) |
| **Cloudflare R2** | 10GB تخزين، 1M قراءة/شهر | ~$0.015/GB بعد الـ free tier |
| **المجموع** | **$0 للبداية** | **~$5-30/شهر** |

---

## 🚀 الخطوات الجاية

1. **براندنج مخصص** — غيّر الألوان في `frontend/tailwind.config.ts` و `src/app/globals.css`
2. **دومين مخصص** — شوف الجزء 3 فوق
3. **ارفع أول تطبيق** — Admin → Apps → Add App
4. **جرّب install لـ IPA** — على iOS device حقيقي، اضغط install

أنت live دلوقتي! 🎉
