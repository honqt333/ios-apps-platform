# Deployment Guide

## 📋 Requirements

- **PHP** 8.3+ with extensions: `pdo_mysql`, `mbstring`, `zip`, `gd`, `intl`, `opcache`, `bcmath`, `exif`, `pcntl`
- **Node.js** 20+
- **Composer** 2.7+
- **MySQL** 8+ (or MariaDB 10.6+)
- **Redis** 7+ (recommended for cache & queue)
- **Nginx** 1.25+ or **Apache** 2.4+
- **SSL certificate** (Let's Encrypt recommended for production)

---

## 🐳 Option 1: Docker (Recommended)

The fastest and most reproducible way to deploy.

### Quick start

```bash
git clone <your-repo> ios-platform
cd ios-platform

# Copy env files
cp docker.env.example docker.env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Generate secrets
sed -i "s|APP_KEY=.*|APP_KEY=base64:$(openssl rand -base64 32)|" backend/.env
JWT_SECRET=$(openssl rand -base64 32 | tr -d '=' | head -c 32)
sed -i "s|JWT_SECRET=.*|JWT_SECRET=${JWT_SECRET}|" backend/.env

# Build & start
docker compose up -d --build

# Run migrations and seed
docker compose exec backend php artisan migrate --seed
```

### Production tweaks

In `docker.env`:

```dotenv
DB_PASSWORD=<strong-password>
DB_ROOT_PASSWORD=<strong-root-password>
NGINX_PORT=80
NGINX_SSL_PORT=443
```

In `backend/.env`:
```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
FILESYSTEM_DISK=s3   # or r2
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_BUCKET=your-bucket
AWS_DEFAULT_REGION=us-east-1
```

For HTTPS, add an SSL service to `docker-compose.yml` (e.g. `nginxproxy/nginx-proxy` + `acme-companion`) and set up certificates automatically.

---

## 🖥 Option 2: Manual / VM

### Backend (Laravel)

```bash
# Install PHP + extensions (Ubuntu 22.04 example)
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-zip php8.3-curl php8.3-gd php8.3-intl \
  php8.3-bcmath php8.3-redis php8.3-opcache php8.3-cli

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Clone
git clone <your-repo> /var/www/ios-platform
cd /var/www/ios-platform/backend

# Install
composer install --optimize-autoloader --no-dev

# Setup env
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Storage
php artisan storage:link

# Permissions
sudo chown -R www-data:www-data /var/www/ios-platform
sudo chmod -R 755 /var/www/ios-platform/storage
sudo chmod -R 755 /var/www/ios-platform/bootstrap/cache

# Migrate
php artisan migrate --seed --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend (Next.js)

```bash
cd /var/www/ios-platform/frontend
npm ci
npm run build

# Run with PM2 or systemd
pm2 start npm --name "ios-frontend" -- start
```

Or use the standalone build:

```bash
# Add output: "standalone" in next.config.ts (already set)
node server.js
```

### Nginx config

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    client_max_body_size 1024M;

    # Frontend
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Backend API
    location /api/ {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 300s;
    }

    # Public storage (manifests, screenshots, icons)
    location /storage/ {
        alias /var/www/ios-platform/backend/storage/app/public/;
        expires 7d;
        add_header Cache-Control "public";
    }
}
```

Backend PHP-FPM pool:

```ini
; /etc/php/8.3/fpm/pool.d/ios-platform.conf
[ios-platform]
user = www-data
group = www-data
listen = 127.0.0.1:9000
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
php_admin_value[upload_max_filesize] = 1024M
php_admin_value[post_max_size] = 1024M
```

---

## ☁️ Option 3: Cloud (AWS / DigitalOcean / etc.)

### Managed services

| Service       | Recommended                             |
| ------------- | --------------------------------------- |
| App servers   | ECS Fargate, App Runner, Droplets, K8s  |
| Database      | RDS MySQL, Aurora, DigitalOcean Managed |
| Cache / Queue | ElastiCache Redis, Memorystore          |
| Object store  | S3 (or Cloudflare R2 for cost)          |
| CDN           | CloudFront, Cloudflare                  |
| SSL           | ACM (AWS) or Let's Encrypt              |

### Environment variables

For all environments, the following env vars are required:

```dotenv
APP_NAME="iOS Apps Platform"
APP_ENV=production
APP_KEY=<generate-with-php-artisan-key:generate>
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=...
DB_DATABASE=ios_platform
DB_USERNAME=...
DB_PASSWORD=...

# Redis
REDIS_HOST=...
REDIS_PORT=6379
REDIS_PASSWORD=...

# JWT
JWT_SECRET=<generate-with-php-artisan-jwt:secret>
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=ios-platform-uploads

# Frontend
NEXT_PUBLIC_API_URL=https://api.yourdomain.com/api
NEXT_PUBLIC_SITE_URL=https://yourdomain.com

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

---

## 🔒 Security checklist

- [x] All secrets stored in env, never committed
- [x] APP_DEBUG=false in production
- [x] Strong database passwords
- [x] HTTPS enabled (Let's Encrypt / Cloudflare)
- [x] CORS restricted to your domain
- [x] Rate limiting enabled
- [x] File upload MIME validation
- [x] Audit logging on admin writes
- [x] Firewall: only 80, 443 open to public
- [x] SSH key-only auth
- [x] Regular backups of MySQL + storage bucket

## 📊 Post-deploy

- Run `php artisan config:cache route:cache view:cache`
- Run `npm run build` for frontend
- Run `php artisan migrate --force` for DB updates
- Monitor logs: `tail -f storage/logs/laravel.log`
- Monitor queue: `php artisan queue:listen`

## 🔄 Updating

```bash
cd /var/www/ios-platform
git pull origin main
cd backend && composer install --no-dev
php artisan migrate --force
php artisan config:cache route:cache view:cache
cd ../frontend && npm ci && npm run build
pm2 restart all
```
