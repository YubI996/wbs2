#!/bin/bash
set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}"
echo "========================================"
echo "  WBS v2 - Docker Installer"
echo "========================================"
echo -e "${NC}"

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root or with sudo${NC}"
    exit 1
fi

# Check Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker is not installed. Please install Docker first.${NC}"
    exit 1
fi

# Check Docker Compose
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
elif docker compose version &> /dev/null; then
    COMPOSE_CMD="docker compose"
else
    echo -e "${RED}Docker Compose is not installed. Please install Docker Compose first.${NC}"
    exit 1
fi

echo -e "${GREEN}Using: $COMPOSE_CMD${NC}"

# Get current directory
INSTALL_DIR=$(pwd)
echo -e "${YELLOW}Install directory: $INSTALL_DIR${NC}"

# Step 1: Create .env if not exists
echo ""
echo -e "${YELLOW}Step 1: Setting up environment...${NC}"

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "Created .env from .env.example"
    else
        echo -e "${RED}.env.example not found!${NC}"
        exit 1
    fi
fi

# Generate passwords if placeholders exist
generate_password() {
    openssl rand -base64 16 | tr -d '/+=' | head -c 20
}

# Check and set DB passwords
if grep -q "DB_PASSWORD=wbs_password_change_me\|DB_PASSWORD=$" .env; then
    NEW_PASS=$(generate_password)
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$NEW_PASS/" .env
    echo -e "${GREEN}Generated DB_PASSWORD${NC}"
fi

if grep -q "DB_ROOT_PASSWORD=.*change.*\|DB_ROOT_PASSWORD=$" .env; then
    NEW_ROOT_PASS=$(generate_password)
    sed -i "s/DB_ROOT_PASSWORD=.*/DB_ROOT_PASSWORD=$NEW_ROOT_PASS/" .env
    echo -e "${GREEN}Generated DB_ROOT_PASSWORD${NC}"
fi

# Generate APP_KEY
if grep -q "APP_KEY=$" .env; then
    APP_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s/APP_KEY=.*/APP_KEY=$APP_KEY/" .env
    echo -e "${GREEN}Generated APP_KEY${NC}"
fi

# Step 2: Create directories
echo ""
echo -e "${YELLOW}Step 2: Creating directories...${NC}"
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}Directories created${NC}"

# Step 3: Stop existing containers
echo ""
echo -e "${YELLOW}Step 3: Stopping existing containers...${NC}"
$COMPOSE_CMD down 2>/dev/null || true
echo -e "${GREEN}Containers stopped${NC}"

# Step 4: Build
echo ""
echo -e "${YELLOW}Step 4: Building Docker images...${NC}"
$COMPOSE_CMD build --no-cache
echo -e "${GREEN}Build complete${NC}"

# Step 5: Start database first
echo ""
echo -e "${YELLOW}Step 5: Starting database...${NC}"
$COMPOSE_CMD up -d db redis
echo "Waiting for database to be ready..."
sleep 15

# Check database health
MAX_TRIES=30
TRIES=0
while [ $TRIES -lt $MAX_TRIES ]; do
    if $COMPOSE_CMD exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; then
        echo -e "${GREEN}Database is ready!${NC}"
        break
    fi
    TRIES=$((TRIES + 1))
    echo "Waiting for database... ($TRIES/$MAX_TRIES)"
    sleep 2
done

if [ $TRIES -eq $MAX_TRIES ]; then
    echo -e "${YELLOW}Database might still be initializing, continuing anyway...${NC}"
fi

# Step 6: Start application
echo ""
echo -e "${YELLOW}Step 6: Starting application...${NC}"
$COMPOSE_CMD up -d app
sleep 10

# Step 7: Start web and queue
echo ""
echo -e "${YELLOW}Step 7: Starting web server and queue worker...${NC}"
$COMPOSE_CMD up -d web queue

# Step 8: Create storage link
echo ""
echo -e "${YELLOW}Step 8: Creating storage link...${NC}"
$COMPOSE_CMD exec -T app php artisan storage:link 2>/dev/null || echo "Storage link may already exist"

# Step 9: Show status
echo ""
echo -e "${YELLOW}Step 9: Checking status...${NC}"
sleep 5
$COMPOSE_CMD ps

# Get APP_PORT
APP_PORT=$(grep -E "^APP_PORT=" .env | cut -d'=' -f2)
APP_PORT=${APP_PORT:-8080}

echo ""
echo -e "${GREEN}========================================"
echo "  Installation Complete!"
echo "========================================"
echo -e "${NC}"
echo -e "Application URL: ${GREEN}http://YOUR_SERVER_IP:$APP_PORT${NC}"
echo ""
echo "Useful commands:"
echo "  $COMPOSE_CMD ps          - Check status"
echo "  $COMPOSE_CMD logs -f     - View logs"
echo "  $COMPOSE_CMD down        - Stop all"
echo "  $COMPOSE_CMD up -d       - Start all"
echo ""
echo -e "${YELLOW}Note: First startup may take a minute for migrations.${NC}"
echo -e "${YELLOW}Check logs with: $COMPOSE_CMD logs -f app${NC}"
