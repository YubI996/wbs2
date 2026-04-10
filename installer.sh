#!/bin/bash
set -e

# ============================================================
# WBS v2 - Server Deployment Installer
# Untuk: wbs.atletik.biz.id
# ============================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/wbs"
PROXY_DIR="/home/prasasti/proxy"
DOMAIN="wbs.atletik.biz.id"
SSL_SRC="/home/prasasti/SSLBARU/wbs.ssl"
DOCKER_NETWORK="wbs_wbs-network"
PROXY_CONTAINER="nginx-proxy"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}WBS v2 - Server Deployment Setup${NC}"
echo -e "${BLUE}Domain: $DOMAIN${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# ============================================================
# [1/7] Check .env file
# ============================================================
echo -e "${YELLOW}[1/7]${NC} Checking .env configuration..."
if [ ! -f "$PROJECT_DIR/.env" ]; then
    if [ ! -f "$PROJECT_DIR/.env.example" ]; then
        echo -e "${RED}ERROR: .env.example not found in $PROJECT_DIR${NC}"
        exit 1
    fi

    echo -e "${YELLOW}>>> Copying .env.example to .env${NC}"
    cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"

    echo -e "${YELLOW}>>> Please edit $PROJECT_DIR/.env with your production values:${NC}"
    echo "  - DB_PASSWORD (strong password)"
    echo "  - DB_ROOT_PASSWORD (strong password)"
    echo "  - RECAPTCHA_SITE_KEY"
    echo "  - RECAPTCHA_SECRET_KEY"
    echo "  - MAIL_* settings if needed"
    echo ""
    echo -e "${RED}ERROR: Please configure .env and run this script again${NC}"
    exit 1
fi
echo -e "${GREEN}✓ .env file exists${NC}"
echo ""

# ============================================================
# [2/7] Build and start Docker containers
# ============================================================
echo -e "${YELLOW}[2/7]${NC} Building and starting Docker containers..."
cd "$PROJECT_DIR"
docker compose up -d --build
echo -e "${GREEN}✓ Containers started${NC}"
echo ""

# Wait for app to be ready
echo -e "${YELLOW}    Waiting for application to be ready...${NC}"
sleep 10
echo ""

# ============================================================
# [3/7] Install dependencies and setup application
# ============================================================
echo -e "${YELLOW}[3/7]${NC} Installing dependencies and running migrations..."

echo -e "${YELLOW}    Installing Composer dependencies...${NC}"
docker exec wbs-app composer install --no-dev --optimize-autoloader

echo -e "${YELLOW}    Generating APP_KEY...${NC}"
docker exec wbs-app php artisan key:generate --force

echo -e "${YELLOW}    Running database migrations...${NC}"
docker exec wbs-app php artisan migrate --force

echo -e "${YELLOW}    Caching configuration...${NC}"
docker exec wbs-app php artisan config:cache
docker exec wbs-app php artisan route:cache
docker exec wbs-app php artisan view:cache

echo -e "${GREEN}✓ Application setup complete${NC}"
echo ""

# ============================================================
# [4/7] Copy SSL certificates
# ============================================================
echo -e "${YELLOW}[4/7]${NC} Installing SSL certificates..."

SSL_DIR="$PROXY_DIR/ssl/$DOMAIN"
mkdir -p "$SSL_DIR"

if [ ! -f "$SSL_SRC/certificate.crt" ] || [ ! -f "$SSL_SRC/private.crt" ]; then
    echo -e "${RED}ERROR: SSL certificates not found in $SSL_SRC${NC}"
    echo "Expected files:"
    echo "  - $SSL_SRC/certificate.crt"
    echo "  - $SSL_SRC/private.crt"
    exit 1
fi

cp "$SSL_SRC/certificate.crt" "$SSL_DIR/fullchain.pem"
cp "$SSL_SRC/private.crt"     "$SSL_DIR/privkey.pem"
chmod 644 "$SSL_DIR/fullchain.pem"
chmod 600 "$SSL_DIR/privkey.pem"

echo -e "${GREEN}✓ SSL certificates installed to $SSL_DIR${NC}"
echo ""

# ============================================================
# [5/7] Create nginx proxy configuration
# ============================================================
echo -e "${YELLOW}[5/7]${NC} Creating nginx proxy configuration..."

PROXY_CONFIG="$PROXY_DIR/nginx/conf.d/$DOMAIN.conf"
mkdir -p "$PROXY_DIR/nginx/conf.d"

cat > "$PROXY_CONFIG" << 'EOF'
# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name wbs.atletik.biz.id;
    return 301 https://$host$request_uri;
}

# HTTPS server
server {
    listen 443 ssl;
    server_name wbs.atletik.biz.id;

    # SSL certificates
    ssl_certificate     /etc/nginx/ssl/wbs.atletik.biz.id/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/wbs.atletik.biz.id/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Proxy to local container
    location / {
        proxy_pass         http://wbs-nginx:80;
        proxy_http_version 1.1;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto https;
        proxy_set_header   X-Forwarded-Host  $host;
        proxy_set_header   X-Forwarded-Port  443;
        proxy_set_header   Upgrade           $http_upgrade;
        proxy_set_header   Connection        "upgrade";
        proxy_read_timeout 90;
        proxy_send_timeout 90;
    }
}
EOF

echo -e "${GREEN}✓ Proxy configuration created at $PROXY_CONFIG${NC}"
echo ""

# ============================================================
# [6/7] Connect Docker network to proxy
# ============================================================
echo -e "${YELLOW}[6/7]${NC} Connecting Docker network to proxy..."

# Check if proxy container is running
if ! docker ps | grep -q "$PROXY_CONTAINER"; then
    echo -e "${RED}WARNING: Proxy container '$PROXY_CONTAINER' is not running${NC}"
    echo "Please ensure nginx-proxy container is running in $PROXY_DIR"
else
    # Connect network if not already connected
    docker network connect "$DOCKER_NETWORK" "$PROXY_CONTAINER" 2>/dev/null || true
    echo -e "${GREEN}✓ Docker network connected to proxy${NC}"
fi

echo ""

# ============================================================
# [7/7] Reload proxy nginx
# ============================================================
echo -e "${YELLOW}[7/7]${NC} Reloading proxy nginx configuration..."

if docker exec "$PROXY_CONTAINER" nginx -t 2>/dev/null; then
    docker exec "$PROXY_CONTAINER" nginx -s reload
    echo -e "${GREEN}✓ Proxy nginx reloaded${NC}"
else
    echo -e "${YELLOW}WARNING: Could not reload proxy nginx${NC}"
    echo "Please manually reload: docker exec $PROXY_CONTAINER nginx -s reload"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}[OK] WBS v2 deployment complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Test the application:"
echo -e "   ${BLUE}curl -I https://$DOMAIN${NC}"
echo ""
echo "2. Check logs:"
echo -e "   ${BLUE}docker logs -f wbs-app${NC}"
echo -e "   ${BLUE}docker logs -f wbs-nginx${NC}"
echo ""
echo "3. Access the application:"
echo -e "   ${BLUE}https://$DOMAIN${NC}"
echo ""
echo "Container information:"
echo "  - PHP App:   wbs-app"
echo "  - Nginx:     wbs-nginx (port 8001 on host)"
echo "  - Database:  wbs-db"
echo "  - Redis:     wbs-redis"
echo "  - Queue:     wbs-queue"
echo ""
echo "Docker network: $DOCKER_NETWORK"
echo "Proxy domain:   $DOMAIN"
echo ""
