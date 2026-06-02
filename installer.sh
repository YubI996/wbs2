#!/bin/bash
set -euo pipefail

# ============================================================
# WBS v2 - Server Deployment Installer
# ------------------------------------------------------------
# Pola server (Debian + KVM):
#   - Container HTTP aplikasi hanya bind ke 127.0.0.1:<APP_PORT>
#     (loopback VM, tidak terekspos ke jaringan luar).
#   - Apache2 di host = reverse proxy, satu VirtualHost per
#     subdomain, meneruskan ke 127.0.0.1:<APP_PORT>.
#   - Multi-app: tiap aplikasi pakai port + subdomain berbeda,
#     cukup satu pintu masuk (Apache) untuk semua container.
# ============================================================

# ---------- Konfigurasi (sesuaikan per aplikasi) ----------
DOMAIN="devapps-wbs.bontangkota.go.id"   # ServerName VirtualHost (& APP_URL)
APP_PORT="8003"                          # Port loopback container — HARUS UNIK antar app
APP_SCHEME="http"                        # http | https (skema publik; lihat blok HTTPS di bawah)

# Direktori proyek = lokasi script ini berada (hasil git clone)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APACHE_SITES_DIR="/etc/apache2/sites-available"
APACHE_CONF="${APACHE_SITES_DIR}/${DOMAIN}.conf"

# ---------- Tampilan ----------
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
log()  { echo -e "${BLUE}$*${NC}"; }
ok()   { echo -e "${GREEN}✓ $*${NC}"; }
warn() { echo -e "${YELLOW}! $*${NC}"; }
err()  { echo -e "${RED}ERROR: $*${NC}"; }

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE} WBS v2 - Deploy (Apache reverse proxy)${NC}"
echo -e "${BLUE} Domain   : ${DOMAIN}${NC}"
echo -e "${BLUE} Upstream : 127.0.0.1:${APP_PORT}${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# ---------- [0] Prasyarat ----------
command -v docker >/dev/null 2>&1 || { err "docker tidak ditemukan"; exit 1; }
docker compose version >/dev/null 2>&1 || { err "plugin 'docker compose' tidak tersedia"; exit 1; }
command -v apache2ctl >/dev/null 2>&1 || command -v apachectl >/dev/null 2>&1 \
    || { err "Apache2 tidak ditemukan (apache2ctl/apachectl)"; exit 1; }

SUDO=""
if [ "$(id -u)" -ne 0 ]; then
    SUDO="sudo"
    warn "Bukan root — perintah Apache/systemd akan memakai sudo"
fi

cd "$PROJECT_DIR"

# ============================================================
# [1/6] Cek file .env
# ============================================================
log "[1/6] Cek konfigurasi .env ..."
if [ ! -f "$PROJECT_DIR/.env" ]; then
    [ -f "$PROJECT_DIR/.env.example" ] || { err ".env.example tidak ada di $PROJECT_DIR"; exit 1; }
    cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"
    warn ".env dibuat dari .env.example. Lengkapi nilai berikut, lalu jalankan ulang script ini:"
    echo "   - APP_PORT=${APP_PORT}   (samakan dengan port di atas)"
    echo "   - APP_URL=${APP_SCHEME}://${DOMAIN}"
    echo "   - DB_PASSWORD, DB_ROOT_PASSWORD  (password kuat)"
    echo "   - RECAPTCHA_SITE_KEY, RECAPTCHA_SECRET_KEY"
    echo "   - WBS_API_KEY, MAIL_*  (bila perlu)"
    err "Edit $PROJECT_DIR/.env lalu jalankan lagi: ./installer.sh"
    exit 1
fi
ok ".env tersedia"
echo ""

# ============================================================
# [2/6] Build image + dependency + aset frontend
#       (tanpa perlu PHP/Composer/Node terpasang di host)
# ============================================================
log "[2/6] Build image, dependency, dan aset ..."
docker compose build

# Composer (pakai image composer resmi; --ignore-platform-reqs karena ekstensi
# PHP runtime ada di image app, bukan di image composer). Tulis ke bind-mount host.
docker run --rm -v "$PROJECT_DIR":/var/www -w /var/www composer:2 \
    install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs
ok "Composer dependencies terpasang (vendor/)"

# Aset Vite (pakai container Node sekali pakai) -> public/build
docker run --rm -v "$PROJECT_DIR":/var/www -w /var/www node:20-alpine \
    sh -c "npm ci && npm run build"
ok "Aset frontend ter-build (public/build/)"
echo ""

# ============================================================
# [3/6] Jalankan containers
# ============================================================
log "[3/6] Menjalankan containers ..."
docker compose up -d
ok "Containers berjalan"
log "    Menunggu database & aplikasi siap ..."
sleep 10
echo ""

# ============================================================
# [4/6] Setup aplikasi Laravel
# ============================================================
log "[4/6] Setup aplikasi (key, migrate, cache) ..."

# Generate APP_KEY hanya bila belum ada (idempoten — aman dijalankan ulang)
if ! grep -qE '^APP_KEY=base64:' "$PROJECT_DIR/.env"; then
    docker exec wbs-app php artisan key:generate --force
fi

docker exec wbs-app php artisan storage:link        || true
docker exec wbs-app php artisan package:discover     --ansi || true
docker exec wbs-app php artisan migrate --force
docker exec wbs-app php artisan filament:assets      || true
docker exec wbs-app php artisan config:cache
docker exec wbs-app php artisan route:cache
docker exec wbs-app php artisan view:cache
docker exec wbs-app php artisan filament:upgrade     || true

# Restart agar php-fpm memuat config/opcache terbaru
docker compose restart app queue
ok "Aplikasi siap"
echo ""

# ============================================================
# [5/6] Buat VirtualHost Apache
# ============================================================
log "[5/6] Membuat VirtualHost Apache ..."
$SUDO a2enmod proxy proxy_http headers rewrite >/dev/null 2>&1 || true

$SUDO tee "$APACHE_CONF" >/dev/null << EOF
# ${DOMAIN}  ->  http://127.0.0.1:${APP_PORT}  (container WBS v2 / wbs-nginx)
# Dibuat otomatis oleh installer.sh.
# Pola multi-app: satu VirtualHost per subdomain, satu Apache untuk banyak container.
<VirtualHost *:80>
    ServerName ${DOMAIN}

    ProxyPreserveHost On
    ProxyRequests Off

    # Teruskan skema ke Laravel (dibaca oleh TrustProxies di bootstrap/app.php).
    # Bila TLS diterminasi di layer luar (gateway HTTPS), ubah "http" -> "https".
    RequestHeader set X-Forwarded-Proto "${APP_SCHEME}"

    ProxyPass        / http://127.0.0.1:${APP_PORT}/ retry=0
    ProxyPassReverse / http://127.0.0.1:${APP_PORT}/

    ErrorLog  \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>

# ------------------------------------------------------------------
# CONTOH HTTPS (aktifkan setelah punya sertifikat):
#   1) sudo a2enmod ssl
#   2) Let's Encrypt:  sudo certbot --apache -d ${DOMAIN}
#      (certbot membuat blok :443 + redirect :80 -> :443 otomatis)
#   3) Pada blok :443, set  RequestHeader set X-Forwarded-Proto "https"
#      dan ubah APP_URL=https://${DOMAIN} di .env, lalu:
#      docker exec wbs-app php artisan config:cache
# ------------------------------------------------------------------
EOF

$SUDO a2ensite "${DOMAIN}.conf" >/dev/null 2>&1 || true
ok "VirtualHost dibuat: $APACHE_CONF"
echo ""

# ============================================================
# [6/6] Test konfigurasi & reload Apache
# ============================================================
log "[6/6] Test konfigurasi & reload Apache ..."
if $SUDO apache2ctl configtest; then
    $SUDO systemctl reload apache2 2>/dev/null || $SUDO apache2ctl -k graceful
    ok "Apache di-reload"
else
    err "Konfigurasi Apache tidak valid — perbaiki lalu jalankan: $SUDO systemctl reload apache2"
    exit 1
fi
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}[OK] Deploy WBS v2 selesai!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Akses     : ${APP_SCHEME}://${DOMAIN}"
echo "Upstream  : 127.0.0.1:${APP_PORT}  (container wbs-nginx)"
echo ""
echo "Cek cepat :"
echo -e "   ${BLUE}curl -I http://127.0.0.1:${APP_PORT}${NC}                       # langsung ke container"
echo -e "   ${BLUE}curl -I -H 'Host: ${DOMAIN}' http://127.0.0.1${NC}   # via Apache"
echo ""
echo "Containers: wbs-app (php-fpm), wbs-nginx, wbs-db, wbs-redis, wbs-queue"
echo "Logs      : docker logs -f wbs-app"
echo -e "            ${SUDO:+$SUDO }tail -f /var/log/apache2/${DOMAIN}-error.log"
echo ""
echo "Tambah aplikasi lain: ulangi pola ini di repo app tsb. dengan"
echo "APP_PORT & DOMAIN berbeda (mis. devapps-lain.bontangkota.go.id -> 8004)."
echo ""
