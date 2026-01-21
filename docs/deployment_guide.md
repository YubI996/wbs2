# WBS v2 - Panduan Deployment & Konfigurasi

## 1. Requirements

- PHP 8.4+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ & NPM
- Redis (opsional, untuk queue)

## 2. Environment Setup

Copy file `.env.example` ke `.env` dan update nilai berikut:

```env
APP_NAME="WBS Kota Bontang"
APP_URL=https://wbs.bontangkota.go.id
APP_TIMEZONE=Asia/Makassar

DB_DATABASE=wbs_v2
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Email SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=wbs@bontangkota.go.id
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="wbs@bontangkota.go.id"
MAIL_FROM_NAME="WBS Kota Bontang"

# Queue (gunakan database atau redis)
QUEUE_CONNECTION=database

# reCAPTCHA v3
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key

# OneSignal (opsional)
ONESIGNAL_APP_ID=your_app_id
ONESIGNAL_REST_API_KEY=your_rest_api_key
```

## 3. Installation

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Generate key
php artisan key:generate

# Run migrations & seeders
php artisan migrate --force
php artisan db:seed --force

# Storage link
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
```

## 4. Queue Worker

Untuk menjalankan queue worker (email notifications):

### Development:
```bash
php artisan queue:work --queue=default --sleep=3 --tries=3
```

### Production (Supervisor):

Buat file `/etc/supervisor/conf.d/wbs-worker.conf`:

```ini
[program:wbs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wbs/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/wbs/storage/logs/worker.log
stopwaitsecs=3600
```

Kemudian:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start wbs-worker:*
```

### Windows (Task Scheduler):

Buat scheduled task untuk menjalankan:
```
php D:\apps\laragon\www\wbs-v2\artisan queue:work --stop-when-empty
```
Dengan trigger setiap 5 menit.

## 5. Test Accounts

| Email | Password | Role | Panel URL |
|-------|----------|------|-----------|
| admin@wbs.bontangkota.go.id | password | Admin | /admin |
| verifikator@wbs.bontangkota.go.id | password | Verifikator | /verifikator |
| inspektur@wbs.bontangkota.go.id | password | Inspektur | /inspektur |

## 6. URLs

| Path | Description |
|------|-------------|
| / | Landing Page |
| /buat-laporan | Form Pelaporan |
| /cek-status | Cek Status Laporan |
| /admin | Panel Admin |
| /verifikator | Panel Verifikator |
| /inspektur | Panel Inspektur |

## 7. Troubleshooting

### Email tidak terkirim:
```bash
php artisan queue:work --once
# atau cek failed jobs:
php artisan queue:failed
php artisan queue:retry all
```

### Cache issues:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Storage permission:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```
