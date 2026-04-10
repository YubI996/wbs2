# WBS v2 - Docker Setup Guide

Quick reference untuk Docker files dan struktur project ini.

## File Structure

```
wbs-v2/
├── Dockerfile                          # Multi-stage build untuk production
├── docker-compose.yml                  # Docker Compose configuration
├── docker/
│   ├── nginx/
│   │   ├── conf.d/
│   │   │   └── default.conf           # Nginx internal config
│   │   └── nginx.conf                 # Legacy (deprecated)
│   ├── php/
│   │   ├── php.ini                    # Main PHP config
│   │   ├── local.ini                  # Local overrides (upload, memory)
│   │   └── www.conf                   # PHP-FPM pool config
│   └── entrypoint.sh                  # Container startup script
├── installer.sh                        # Production server installer
├── .dockerignore                       # Files to exclude from Docker build
├── DEPLOYMENT.md                       # Full deployment guide
└── DOCKER.md                          # This file
```

## Docker Architecture

### Services dalam docker-compose.yml

| Service | Image | Port | Role |
|---------|-------|------|------|
| **app** | Custom (Dockerfile) | 9000 | PHP-FPM application |
| **nginx** | nginx:alpine | 8001→80 | Web server & reverse proxy |
| **db** | mysql:8.0 | 3306 (internal) | Database |
| **redis** | redis:7-alpine | 6379 (internal) | Cache & session |
| **queue** | Custom (Dockerfile) | N/A | Background job worker |

### Network

- **Name**: `wbs-network` (Docker will name it `wbs_wbs-network`)
- **Type**: Bridge
- **Hosts communicate via**: Service names (e.g., `http://app:9000`, `mysql://db:3306`)

## Development Setup (Local/Laragon)

```bash
# Build images
docker compose build

# Start all services
docker compose up -d

# Install dependencies
docker compose exec app composer install
docker compose exec app npm install

# Setup database
docker compose exec app php artisan migrate

# View logs
docker compose logs -f app
```

Access: `http://localhost:8001`

## Production Deployment (Linux Server)

See [DEPLOYMENT.md](DEPLOYMENT.md) for complete production deployment guide.

Quick summary:
```bash
# 1. Upload/clone to /var/www/wbs
cd /var/www/wbs

# 2. Configure .env
cp .env.example .env
nano .env  # Set DB passwords, reCAPTCHA keys, etc

# 3. Run installer
chmod +x installer.sh
sudo ./installer.sh
```

The installer handles:
- Docker build & start
- Database migrations
- SSL certificate setup
- nginx-proxy integration

## Dockerfile Explanation

Multi-stage build optimizes for production:

```dockerfile
# Stage 1: composer-builder
# - Installs PHP dependencies via Composer
# - Creates vendor/ directory

# Stage 2: npm-builder
# - Copies dependencies from stage 1
# - Installs npm dependencies
# - Builds Vite assets (public/build/)

# Stage 3: production (final image)
# - Lightweight alpine-based PHP-FPM
# - Copies app from stages 1 & 2
# - Minimal image size
# - Ready for production
```

Result: Single production image ~500MB instead of 1GB+ with all build tools.

## Configuration Files

### docker-compose.yml

```yaml
services:
  app:           # PHP-FPM app container
    build: .     # Uses Dockerfile
    volumes:
      - .:/var/www  # Mount source code
    networks:
      - wbs-network # Custom network
    depends_on:
      db:          # Wait for MySQL to be healthy
        condition: service_healthy

  nginx:         # Web server
    image: nginx:alpine
    ports:
      - "8001:80"   # Host:Container port mapping
    volumes:
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/:ro
    depends_on:
      - app        # Wait for app to start

  db:            # MySQL database
    image: mysql:8.0
    healthcheck:  # Health check for depends_on
      test: mysqladmin ping
      interval: 5s
      retries: 10
    volumes:
      - db_data:/var/lib/mysql  # Persistent storage

  redis:         # Cache & session store
    image: redis:7-alpine
    volumes:
      - redis_data:/data

  queue:         # Background job worker
    build: .     # Uses same Dockerfile
    command: php artisan queue:work  # Different command
```

### docker/nginx/conf.d/default.conf

- Root directory: `/var/www/public`
- Forwards PHP requests to `app:9000` (FastCGI)
- Gzip compression enabled
- Security headers configured
- Static file caching (1 year for JS/CSS)

### docker/php/php.ini

Production PHP configuration:
- OPcache enabled (caching compiled PHP)
- Memory limit: 256M
- Max execution: 300s
- Upload limit: 50M
- Error logging (no display)

### docker/php/local.ini

Override for specific settings:
- Upload limit: **40M** (Filament upload limit)
- Memory: **512M** (for Filament operations)
- Execution: **600s** (longer timeout)

### docker/entrypoint.sh

Runs when container starts:
1. Waits for MySQL to be ready
2. Generates APP_KEY if needed
3. Runs migrations
4. Caches config for production
5. Fixes file permissions
6. Starts PHP-FPM

## Common Tasks

### View Logs

```bash
# Follow app logs in real-time
docker logs -f wbs-app

# View Nginx logs
docker logs -f wbs-nginx

# View database logs
docker logs -f wbs-db

# Get last N lines
docker logs --tail 50 wbs-app
```

### Execute Commands in Container

```bash
# Run artisan command
docker exec wbs-app php artisan migrate

# Interactive shell
docker exec -it wbs-app bash

# Database shell
docker exec -it wbs-db mysql -u root -p

# View PHP info
docker exec wbs-app php -i
```

### Rebuild & Restart

```bash
# Rebuild specific service
docker compose build app

# Restart without rebuild
docker compose restart app

# Full rebuild & restart
docker compose up -d --build
```

### Database Management

```bash
# Backup
docker exec wbs-db mysqldump -u root -p$DB_ROOT_PASSWORD wbs_v2 > backup.sql

# List tables
docker exec -it wbs-db mysql -u wbs_user -p wbs_v2 -e "SHOW TABLES;"

# Clear migrations
docker exec wbs-app php artisan migrate:reset

# Seed database
docker exec wbs-app php artisan db:seed
```

### Cache & Optimization

```bash
# Clear all caches
docker exec wbs-app php artisan cache:clear
docker exec wbs-app php artisan config:clear
docker exec wbs-app php artisan view:clear

# Reoptimize
docker exec wbs-app php artisan optimize

# View cache contents
docker exec wbs-app php artisan cache:forget <key>
```

## Environment Variables

Pass via `.env` → docker-compose.yml → container

Example flow:
```
.env (APP_DEBUG=false)
  ↓
docker-compose.yml (environment: section)
  ↓
Container /var/www/.env
  ↓
Available in PHP via env('APP_DEBUG')
```

## Networking

### Service Communication (internal)

- `http://app:9000` - PHP-FPM
- `mysql://db:3306` - MySQL
- `redis://redis:6379` - Redis

### External Access (from host)

- `http://localhost:8001` - Nginx (web server)
- `http://127.0.0.1:8001` - Same as above

### Production (via nginx-proxy)

- `https://wbs.atletik.biz.id` - nginx-proxy forwards to `http://wbs-nginx:80`
- `wbs-nginx:80` - Internal Docker network

## Volume Mounts

```yaml
# Source code mount (read-write)
- .:/var/www

# Nginx config mount (read-only)
- ./docker/nginx/conf.d/:/etc/nginx/conf.d/:ro

# Database persistent storage (named volume)
volumes:
  db_data:    # Survives container recreation

# Redis persistent storage
  redis_data:  # Session/cache survives restart
```

## Troubleshooting

### Container fails to start

```bash
# Check container logs
docker logs wbs-app

# Check docker-compose syntax
docker compose config

# Detailed error
docker compose up --build  # Don't use -d, see errors
```

### PHP errors not showing

Check `APP_DEBUG` in `.env` and Docker logs:
```bash
docker logs wbs-app | grep -i error
```

### Database connection error

```bash
# Test connection from app
docker exec wbs-app php artisan db:show

# Check DB_HOST in .env - should be "db" (not localhost)
grep "DB_HOST" .env
```

### Port already in use

```bash
# Change in docker-compose.yml
ports:
  - "8080:80"  # Use different port

# Or kill process on port 8001
lsof -i :8001
kill -9 <PID>
```

## Security Checklist

- [ ] `.env` not in version control (use `.env.example`)
- [ ] `APP_DEBUG=false` in production
- [ ] Strong DB passwords
- [ ] `SESSION_SECURE_COOKIE=true` for HTTPS
- [ ] reCAPTCHA keys set
- [ ] SSL certificates installed
- [ ] nginx security headers enabled
- [ ] `allow_url_include=Off` in PHP config
- [ ] Dangerous functions disabled in php.ini

## Performance Tips

1. **OPcache enabled**: PHP bytecode caching
2. **Gzip compression**: Nginx compresses responses
3. **Static file caching**: 1-year expiry for assets
4. **Redis cache**: Use for sessions & cache
5. **Queue worker**: Process jobs asynchronously

## References

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [Nginx Documentation](https://nginx.org/en/)
- [PHP-FPM Documentation](https://www.php.net/manual/en/install.fpm.php)
- [MySQL Docker Image](https://hub.docker.com/_/mysql)
- [Laravel Docker Setup](https://laravel.com/docs/sail)

---

For production deployment, see [DEPLOYMENT.md](DEPLOYMENT.md)
