# Docker Deployment Guide - WBS v2

## Overview

This guide will help you deploy the WBS v2 (Whistleblowing System) Laravel application using Docker on port 8001.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git
- At least 2GB RAM available
- 10GB disk space

## Architecture

The application runs in multiple containers:

```
Port 8001 → Nginx → PHP-FPM App ──┐
                    Queue Worker ──┼→ MySQL Database
                                   └→ Redis (optional)
```

### Services

1. **wbs-app** - PHP 8.2-FPM running Laravel 12
2. **wbs-webserver** - Nginx web server (port 8001)
3. **wbs-queue** - Queue worker for email notifications
4. **wbs-mysql** - MySQL 8.0 database
5. **wbs-redis** - Redis cache/session store (optional)

## Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/YourRepo/wbs-v2.git /opt/wbs-v2
cd /opt/wbs-v2
```

### 2. Configure Environment

```bash
# Copy environment template
cp .env.docker .env

# Edit configuration
nano .env
```

**Required configurations:**

```env
# Database credentials
DB_PASSWORD=YOUR_SECURE_PASSWORD_HERE
DB_ROOT_PASSWORD=YOUR_ROOT_PASSWORD_HERE

# API Key for SuperApps integration
WBS_API_KEY=YOUR_API_KEY_HERE

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=wbs@bontangkota.go.id

# reCAPTCHA (get keys from https://www.google.com/recaptcha)
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key

# Application URL
APP_URL=http://your-domain.com
```

**Generate secure passwords:**

```bash
# For database password
openssl rand -base64 32

# For API key
openssl rand -base64 32
```

### 3. Build and Start

```bash
# Build Docker images
docker-compose build --no-cache

# Start all services
docker-compose up -d

# Monitor startup logs
docker-compose logs -f wbs-app
```

Wait for the initialization to complete. You should see:
```
✓ MySQL is ready!
✓ Application key generated!
✓ Migrations completed!
✓ Storage symlink created!
✓ Production optimization completed!
Starting PHP-FPM...
```

### 4. Verify Deployment

```bash
# Check all containers are running
docker-compose ps

# Expected output:
# wbs-app         running
# wbs-webserver   running (0.0.0.0:8001->80/tcp)
# wbs-queue       running
# wbs-mysql       running
# wbs-redis       running

# Test application
curl http://localhost:8001

# Check queue worker
docker-compose logs wbs-queue
```

### 5. Access Application

Open your browser and navigate to:

- **Public Form**: `http://your-server:8001/buat-laporan`
- **Admin Panel**: `http://your-server:8001/admin`
- **Verifikator Panel**: `http://your-server:8001/verifikator`
- **Inspektur Panel**: `http://your-server:8001/inspektur`

## Post-Deployment Tasks

### Create Admin Users

```bash
# Access application shell
docker-compose exec wbs-app bash

# Create admin user using tinker
php artisan tinker

# In tinker console:
$user = new App\Models\User();
$user->name = 'Administrator';
$user->email = 'admin@bontangkota.go.id';
$user->password = Hash::make('secure_password');
$user->save();
```

### Seed Initial Data (Optional)

```bash
docker-compose exec wbs-app php artisan db:seed
```

## Management Commands

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f wbs-app
docker-compose logs -f wbs-queue
docker-compose logs -f wbs-mysql
```

### Restart Services

```bash
# Restart all
docker-compose restart

# Restart specific service
docker-compose restart wbs-app
docker-compose restart wbs-queue
```

### Run Artisan Commands

```bash
# General format
docker-compose exec wbs-app php artisan [command]

# Examples
docker-compose exec wbs-app php artisan migrate:status
docker-compose exec wbs-app php artisan queue:work
docker-compose exec wbs-app php artisan cache:clear
```

### Maintenance Mode

```bash
# Enable maintenance mode
docker-compose exec wbs-app php artisan down --secret="bypass-token"

# Disable maintenance mode
docker-compose exec wbs-app php artisan up
```

## Updating Application

### Pull Latest Code

```bash
cd /opt/wbs-v2
git pull origin main
```

### Rebuild and Deploy

```bash
# Rebuild images (only if dependencies changed)
docker-compose build wbs-app wbs-queue

# Restart services
docker-compose up -d --no-deps wbs-app wbs-queue

# Run new migrations
docker-compose exec wbs-app php artisan migrate --force

# Clear and rebuild caches
docker-compose exec wbs-app php artisan optimize:clear
docker-compose exec wbs-app php artisan optimize
```

## Backup and Restore

### Database Backup

```bash
# Manual backup
docker-compose exec wbs-mysql mysqldump -u wbs_user -p wbs_v2 > backup-$(date +%Y%m%d).sql

# Automated backup script (add to crontab)
#!/bin/bash
BACKUP_DIR="/backups/mysql"
DATE=$(date +%Y%m%d-%H%M%S)
docker-compose exec -T wbs-mysql mysqldump -u wbs_user -p${DB_PASSWORD} wbs_v2 | gzip > ${BACKUP_DIR}/wbs-${DATE}.sql.gz

# Keep last 30 days
find ${BACKUP_DIR} -name "wbs-*.sql.gz" -mtime +30 -delete
```

### Database Restore

```bash
# Restore from backup
docker-compose exec -T wbs-mysql mysql -u wbs_user -p${DB_PASSWORD} wbs_v2 < backup.sql
```

### Storage Backup

```bash
# Backup uploaded files
tar -czf storage-backup-$(date +%Y%m%d).tar.gz storage/app/public/

# Restore
tar -xzf storage-backup-YYYYMMDD.tar.gz
```

## Troubleshooting

### Permission Issues

```bash
docker-compose exec wbs-app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec wbs-app chmod -R 775 storage bootstrap/cache
```

### APP_KEY Not Set

```bash
docker-compose exec wbs-app php artisan key:generate --force
```

### Queue Not Processing

```bash
# Check queue worker logs
docker-compose logs wbs-queue

# Restart queue worker
docker-compose restart wbs-queue

# Check failed jobs
docker-compose exec wbs-app php artisan queue:failed
```

### MySQL Connection Issues

```bash
# Check MySQL is running
docker-compose ps wbs-mysql

# Test connection
docker-compose exec wbs-mysql mysql -u wbs_user -p${DB_PASSWORD} -e "SELECT 1"

# Check MySQL logs
docker-compose logs wbs-mysql
```

### Nginx 502 Bad Gateway

```bash
# Check PHP-FPM is running
docker-compose ps wbs-app

# Check PHP-FPM logs
docker-compose logs wbs-app

# Restart services
docker-compose restart wbs-app wbs-webserver
```

### Out of Memory

```bash
# Check container resources
docker stats wbs-app wbs-queue wbs-mysql

# Adjust PHP memory limit in docker/php/php.ini
memory_limit = 512M

# Rebuild and restart
docker-compose build wbs-app
docker-compose restart wbs-app
```

## Performance Optimization

### Enable Redis Cache

1. Edit `.env`:
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=wbs-redis
```

2. Restart application:
```bash
docker-compose restart wbs-app wbs-queue
docker-compose exec wbs-app php artisan config:cache
```

### Monitor Performance

```bash
# View resource usage
docker stats

# Check slow queries
docker-compose exec wbs-mysql mysql -u root -p -e "SHOW PROCESSLIST"

# Monitor queue
docker-compose exec wbs-app php artisan queue:monitor
```

## Security Recommendations

1. **Change Default Passwords**: Update all passwords in `.env`
2. **Enable HTTPS**: Use reverse proxy (Nginx Proxy Manager, Traefik) or configure SSL in Nginx
3. **Firewall**: Only allow port 8001 from trusted IPs
4. **Regular Updates**: Keep Docker images updated
5. **Backup Schedule**: Set up automated backups
6. **Log Monitoring**: Implement log aggregation (ELK, Graylog)

## Advanced Configuration

### Using Different Database Port

Edit `docker-compose.yml`:
```yaml
wbs-mysql:
  ports:
    - "3307:3306"  # External:Internal
```

### Custom Nginx Configuration

Edit `docker/nginx/nginx.conf` and restart:
```bash
docker-compose restart wbs-webserver
```

### Multiple Queue Workers

Add to `docker-compose.yml`:
```yaml
wbs-queue-2:
  extends:
    service: wbs-queue
  container_name: wbs-queue-2
```

## Monitoring and Health Checks

### Health Check Endpoints

- **Nginx**: `http://localhost:8001/health`
- **PHP-FPM Status**: `http://localhost:8001/fpm-status` (requires Nginx config)
- **Database**: Check with `docker-compose ps`

### Log Files

- **Application**: `storage/logs/laravel.log`
- **Nginx Access**: `docker-compose logs wbs-webserver`
- **PHP-FPM**: `docker-compose logs wbs-app`
- **Queue**: `docker-compose logs wbs-queue`

## Production Checklist

- [ ] `.env` configured with secure passwords
- [ ] APP_URL set to production domain
- [ ] APP_DEBUG=false
- [ ] Mail configuration tested
- [ ] reCAPTCHA keys configured
- [ ] WBS_API_KEY generated and stored securely
- [ ] Database credentials changed from defaults
- [ ] Firewall configured (port 8001 only)
- [ ] HTTPS/SSL configured
- [ ] Backup schedule configured
- [ ] Admin users created
- [ ] Storage permissions verified
- [ ] Queue worker running and processing jobs
- [ ] Email delivery tested
- [ ] All admin panels accessible
- [ ] Public form tested

## Support

For issues and questions:

- Check logs: `docker-compose logs -f`
- Review Laravel logs: `storage/logs/laravel.log`
- Verify configuration: Check `.env` file
- Test database: Connect to MySQL and verify tables

## Useful Commands Reference

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f [service]

# Execute commands
docker-compose exec wbs-app [command]

# Access shell
docker-compose exec wbs-app bash

# Clear caches
docker-compose exec wbs-app php artisan optimize:clear

# Run migrations
docker-compose exec wbs-app php artisan migrate

# Check status
docker-compose ps

# View resource usage
docker stats
```

---

**Last Updated**: 2026-01-21
**Application Version**: WBS v2
**Laravel Version**: 12.0
**Filament Version**: 3.3
