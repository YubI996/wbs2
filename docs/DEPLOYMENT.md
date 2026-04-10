# WBS v2 - Panduan Deployment ke Server Linux

Dokumentasi lengkap untuk deploy WBS v2 ke server Linux dengan Docker dan nginx-proxy.

## Prasyarat Server

- **OS**: Linux (Ubuntu 20.04+)
- **Docker**: 20.10+
- **Docker Compose**: 2.0+
- **nginx-proxy**: Sudah berjalan di `/home/prasasti/proxy/`
- **Domain**: `wbs.atletik.biz.id` (sudah terkonfigurasi di nginx-proxy)
- **SSL**: Sertifikat tersedia di `/home/prasasti/SSLBARU/wbs.ssl/`

## Arsitektur

```
┌─────────────────────────────────────────────────────┐
│            Public Internet (HTTPS)                  │
└──────────────────────┬──────────────────────────────┘
                       │ Port 443
┌──────────────────────┴──────────────────────────────┐
│    nginx-proxy (wbs.atletik.biz.id)                │
│  - SSL termination                                  │
│  - Port 80/443                                      │
└──────────────────────┬──────────────────────────────┘
                       │
    ┌──────────────────┴────────────────────────┐
    │ wbs_wbs-network (Docker bridge)           │
    │                                           │
    ├─ wbs-nginx (port 8001 on host)           │
    │   └─ wbs-app (PHP-FPM)                   │
    │       ├─ wbs-db (MySQL)                  │
    │       ├─ wbs-redis (Cache)               │
    │       └─ wbs-queue (Queue Worker)        │
    │                                           │
    └───────────────────────────────────────────┘
```

## Langkah-langkah Deployment

### 1. Clone/Upload Project ke Server

```bash
# Login ke server
ssh user@server

# Clone project ke /var/www/wbs
git clone <repo-url> /var/www/wbs
cd /var/www/wbs
```

### 2. Setup Environment (.env)

```bash
# Copy .env.example ke .env
cp .env.example .env

# Edit .env dengan nilai production
nano .env
```

**Penting**: Pastikan isi .env:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wbs.atletik.biz.id

# Database - HARUS sesuai dengan docker-compose.yml
DB_CONNECTION=mysql
DB_HOST=db          # Nama service di docker-compose
DB_PORT=3306
DB_DATABASE=wbs_v2
DB_USERNAME=wbs_user
DB_PASSWORD=<GENERATE_STRONG_PASSWORD>
DB_ROOT_PASSWORD=<GENERATE_STRONG_PASSWORD>

# Session
SESSION_SECURE_COOKIE=true     # HTTPS only di production
SESSION_ENCRYPT=true

# reCAPTCHA (sesuaikan dengan domain Anda)
RECAPTCHA_SITE_KEY=<your-key>
RECAPTCHA_SECRET_KEY=<your-key>

# Mail (jika diperlukan)
MAIL_MAILER=smtp
MAIL_HOST=<your-smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<your-email>
MAIL_PASSWORD=<your-password>
MAIL_FROM_ADDRESS=noreply@atletik.biz.id
```

### 3. Jalankan Installer Script

```bash
cd /var/www/wbs
chmod +x installer.sh
sudo ./installer.sh
```

Script ini akan:
1. ✓ Validasi .env
2. ✓ Build dan start Docker containers
3. ✓ Install composer dependencies
4. ✓ Run database migrations
5. ✓ Copy SSL certificates
6. ✓ Setup nginx proxy config
7. ✓ Connect Docker network ke proxy
8. ✓ Reload proxy nginx

### 4. Verifikasi Deployment

```bash
# Check status containers
docker ps | grep wbs

# Check logs
docker logs -f wbs-app
docker logs -f wbs-nginx
docker logs -f wbs-db

# Test HTTPS
curl -I https://wbs.atletik.biz.id

# Test aplikasi (harusnya return 200)
curl -L https://wbs.atletik.biz.id
```

## File-file Docker

### docker-compose.yml

Definisi semua services:
- **wbs-app**: PHP-FPM container (dari Dockerfile)
- **wbs-nginx**: Nginx web server (port 8001)
- **wbs-db**: MySQL database
- **wbs-redis**: Redis cache & session store
- **wbs-queue**: Background job queue worker

**Network**: `wbs-network` - untuk komunikasi antar container

### Dockerfile

Multi-stage build untuk production:
1. **Stage 1** (composer-builder): Install PHP dependencies
2. **Stage 2** (npm-builder): Build frontend assets (Vite)
3. **Stage 3** (production): Final image dengan PHP-FPM

Base image: `php:8.2-fpm-alpine` (lightweight)

### docker/nginx/conf.d/default.conf

Nginx config untuk internal routing:
- Root directory: `/var/www/public`
- Proxy FastCGI ke `app:9000`
- Gzip compression
- Static file caching
- Security headers

### docker/php/local.ini

PHP configuration overrides:
- Upload limit: 40M
- Memory: 512M
- Max execution: 600s

### docker/php/php.ini

Production PHP configuration:
- OPcache enabled
- Error logging
- Session config
- Security settings

## Troubleshooting

### Container tidak start

```bash
# Check error
docker logs wbs-app

# Rebuild container
docker compose rebuild --no-cache
docker compose up -d
```

### Database connection error

```bash
# Check database status
docker exec wbs-db mysqladmin -u root -p$DB_ROOT_PASSWORD ping

# Check app connectivity
docker exec wbs-app ping db
```

### Aplikasi belum accessible via domain

```bash
# Verify proxy network connection
docker network inspect wbs_wbs-network

# Check proxy config
docker exec nginx-proxy cat /etc/nginx/conf.d/wbs.atletik.biz.id.conf

# Test proxy
curl -I http://localhost:8001
```

### Memory atau disk penuh

```bash
# Check disk usage
df -h

# Clean Docker cache
docker system prune -a --volumes

# Check container size
docker ps --size
```

## Maintenance

### Database Backup

```bash
# Backup database
docker exec wbs-db mysqldump -u root -p$DB_ROOT_PASSWORD wbs_v2 > backup.sql

# Restore dari backup
docker exec -i wbs-db mysql -u root -p$DB_ROOT_PASSWORD wbs_v2 < backup.sql
```

### Update Aplikasi

```bash
cd /var/www/wbs

# Pull latest code
git pull origin main

# Restart containers (app akan rebuild otomatis)
docker compose up -d --build

# Run migrations jika ada
docker exec wbs-app php artisan migrate --force
```

### View Logs

```bash
# Real-time logs dari app
docker logs -f wbs-app

# Logs dari nginx
docker logs -f wbs-nginx

# Logs dari database
docker logs -f wbs-db

# Last 100 lines
docker logs --tail 100 wbs-app
```

### Clear Cache

```bash
# Clear Laravel cache
docker exec wbs-app php artisan cache:clear
docker exec wbs-app php artisan config:clear

# Clear view cache
docker exec wbs-app php artisan view:clear

# Reoptimize
docker exec wbs-app php artisan optimize
```

## Container Names & Network

**PENTING**: Jangan ubah nama-nama ini:

| Component | Container Name | Port (Host) |
|-----------|---|---|
| PHP App | `wbs-app` | Internal (9000) |
| Web Server | `wbs-nginx` | 8001 |
| Database | `wbs-db` | Internal (3306) |
| Cache | `wbs-redis` | Internal (6379) |
| Queue | `wbs-queue` | Internal |

**Docker Network**: `wbs-network`
- Proxy akan connect ke network ini
- Container berkomunikasi via names (e.g., `http://app:9000`)

## SSL Certificate Management

Sertifikat tersimpan di:
```
/home/prasasti/proxy/ssl/wbs.atletik.biz.id/
  ├── fullchain.pem     (public cert)
  └── privkey.pem       (private key)
```

Installer script otomatis copy dari `/home/prasasti/SSLBARU/wbs.ssl/`.

### Renew Certificate

Jika cert di-renew:
```bash
# Update cert files
cp /home/prasasti/SSLBARU/wbs.ssl/certificate.crt /home/prasasti/proxy/ssl/wbs.atletik.biz.id/fullchain.pem
cp /home/prasasti/SSLBARU/wbs.ssl/private.crt /home/prasasti/proxy/ssl/wbs.atletik.biz.id/privkey.pem

# Reload proxy nginx
docker exec nginx-proxy nginx -s reload
```

## Environment Variables

Semua env variables dari `.env` tersedia di container via docker-compose.yml environment section.

Key variables untuk production:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `DB_PASSWORD` (strong)
- `SESSION_SECURE_COOKIE=true` (HTTPS)
- `RECAPTCHA_*` keys

## Support & Info

- **Project Root**: `/var/www/wbs`
- **Docker Compose Command**: `docker compose` (bukan `docker-compose`)
- **View Logs**: `docker logs -f <container-name>`
- **Stop All**: `docker compose down`
- **Start All**: `docker compose up -d`
- **Restart Service**: `docker compose restart <service>`

## Quick Commands Reference

```bash
# Status
docker ps
docker compose ps

# Logs
docker logs -f wbs-app
docker exec wbs-app tail -f storage/logs/*.log

# Shell access
docker exec -it wbs-app bash
docker exec -it wbs-db bash

# Laravel commands
docker exec wbs-app php artisan tinker
docker exec wbs-app php artisan migrate:status
docker exec wbs-app php artisan db:show

# Restart services
docker compose restart wbs-app
docker compose restart wbs-queue

# Full rebuild
docker compose down
docker compose up -d --build
```

---

**Last Updated**: 2024
**Maintained By**: Development Team
