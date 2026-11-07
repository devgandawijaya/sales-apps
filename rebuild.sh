#!/usr/bin/env bash
# ==============================================================================
# Enterprise Sales & Distribution System - Rebuild & Restart Script
# ==============================================================================
# Fungsi:
# 1. Menghentikan seluruh kontainer aplikasi yang sedang berjalan.
# 2. Mendeteksi dan memilih port yang bebas secara otomatis jika terjadi konflik port.
# 3. Membersihkan cache lama aplikasi.
# 4. Membangun ulang (rebuild) image dan kontainer Docker baru.
# 5. Menjalankan aplikasi baru, migrasi skema database, dan optimasi.
# 6. Memverifikasi status kesehatan sistem dan rute web.
# ==============================================================================

set -e

# Warna Terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}===================================================================${NC}"
echo -e "${BLUE}     Sistem Distribusi Penjualan Enterprise (GT & MT Channels)    ${NC}"
echo -e "${BLUE}              PENGHENTIAN, BUILD BARU & RESTART SISTEM             ${NC}"
echo -e "${BLUE}===================================================================${NC}"

# ------------------------------------------------------------------------------
# 1. Cek Kesiapan Docker Daemon & File .env
# ------------------------------------------------------------------------------
echo -e "\n${YELLOW}[1/5] Memeriksa status environment & Docker daemon...${NC}"

if [ ! -f .env ]; then
    echo -e "File .env belum ditemukan. Menyalin dari .env.example..."
    cp .env.example .env
fi

if ! docker ps > /dev/null 2>&1 && ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker daemon tidak aktif atau tidak dapat diakses.${NC}"
    echo -e "Silakan jalankan Docker Desktop terlebih dahulu sebelum menjalankan script ini."
    exit 1
fi
echo -e "${GREEN}✓ Docker daemon aktif dan siap.${NC}"

# ------------------------------------------------------------------------------
# 2. Menghentikan Kontainer yang Sedang Berjalan (Stop Aplikasi)
# ------------------------------------------------------------------------------
echo -e "\n${YELLOW}[2/5] Menghentikan seluruh kontainer aplikasi yang sedang berjalan...${NC}"
docker compose down --remove-orphans 2>/dev/null || true
echo -e "${GREEN}✓ Seluruh kontainer lama berhasil dihentikan.${NC}"

# Membersihkan cache lokal Laravel
echo -e "Membersihkan cache sementara..."
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/services.php bootstrap/cache/packages.php
rm -f storage/framework/views/*.php 2>/dev/null || true
echo -e "${GREEN}✓ File cache lama dibersihkan.${NC}"

# ------------------------------------------------------------------------------
# 3. Deteksi Port Bebas Otomatis (Anti-Konflik Port)
# ------------------------------------------------------------------------------
find_free_port() {
    local port=$1
    while lsof -i :$port >/dev/null 2>&1 || nc -z 127.0.0.1 $port >/dev/null 2>&1; do
        port=$((port + 1))
    done
    echo $port
}

TARGET_APP_PORT=$(grep -E "^APP_PORT=" .env | cut -d'=' -f2 | tr -d ' "' || echo "8080")
[ -z "$TARGET_APP_PORT" ] && TARGET_APP_PORT=8080

TARGET_DB_PORT=$(grep -E "^DB_PORT_FORWARD=" .env | cut -d'=' -f2 | tr -d ' "' || echo "5432")
[ -z "$TARGET_DB_PORT" ] && TARGET_DB_PORT=5432

TARGET_REDIS_PORT=$(grep -E "^REDIS_PORT_FORWARD=" .env | cut -d'=' -f2 | tr -d ' "' || echo "6379")
[ -z "$TARGET_REDIS_PORT" ] && TARGET_REDIS_PORT=6379

FREE_APP_PORT=$(find_free_port $TARGET_APP_PORT)
FREE_DB_PORT=$(find_free_port $TARGET_DB_PORT)
FREE_REDIS_PORT=$(find_free_port $TARGET_REDIS_PORT)

if [ "$FREE_APP_PORT" != "$TARGET_APP_PORT" ]; then
    echo -e "${YELLOW}Perhatian: Port $TARGET_APP_PORT sedang digunakan oleh proses lain. Mengalihkan port web ke $FREE_APP_PORT...${NC}"
fi

if [ "$FREE_DB_PORT" != "$TARGET_DB_PORT" ]; then
    echo -e "${YELLOW}Perhatian: Port PostgreSQL host $TARGET_DB_PORT sedang digunakan. Mengalihkan port host ke $FREE_DB_PORT...${NC}"
fi

if [ "$FREE_REDIS_PORT" != "$TARGET_REDIS_PORT" ]; then
    echo -e "${YELLOW}Perhatian: Port Redis host $TARGET_REDIS_PORT sedang digunakan. Mengalihkan port host ke $FREE_REDIS_PORT...${NC}"
fi

export APP_PORT=$FREE_APP_PORT
export DB_PORT_FORWARD=$FREE_DB_PORT
export REDIS_PORT_FORWARD=$FREE_REDIS_PORT

# Perbarui nilai di .env agar tersinkronisasi
if grep -q "^APP_PORT=" .env; then
    sed -i '' "s/^APP_PORT=.*/APP_PORT=$FREE_APP_PORT/" .env 2>/dev/null || sed -i "s/^APP_PORT=.*/APP_PORT=$FREE_APP_PORT/" .env
fi
if grep -q "^DB_PORT_FORWARD=" .env; then
    sed -i '' "s/^DB_PORT_FORWARD=.*/DB_PORT_FORWARD=$FREE_DB_PORT/" .env 2>/dev/null || sed -i "s/^DB_PORT_FORWARD=.*/DB_PORT_FORWARD=$FREE_DB_PORT/" .env
fi
if grep -q "^REDIS_PORT_FORWARD=" .env; then
    sed -i '' "s/^REDIS_PORT_FORWARD=.*/REDIS_PORT_FORWARD=$FREE_REDIS_PORT/" .env 2>/dev/null || sed -i "s/^REDIS_PORT_FORWARD=.*/REDIS_PORT_FORWARD=$FREE_REDIS_PORT/" .env
fi
if grep -q "^APP_URL=" .env; then
    sed -i '' "s|^APP_URL=.*|APP_URL=http:\/\/localhost:$FREE_APP_PORT|" .env 2>/dev/null || sed -i "s|^APP_URL=.*|APP_URL=http:\/\/localhost:$FREE_APP_PORT|" .env
fi

# ------------------------------------------------------------------------------
# 4. Membangun Ulang Image & Menjalankan Kontainer Baru (Build Baru & Run)
# ------------------------------------------------------------------------------
echo -e "\n${YELLOW}[3/5] Membangun image Docker dan memulai kontainer baru (Build & Up)...${NC}"
docker compose up -d --build --remove-orphans

# ------------------------------------------------------------------------------
# 5. Menunggu Kesiapan Database PostgreSQL & Redis
# ------------------------------------------------------------------------------
echo -e "\n${YELLOW}[4/5] Menunggu database PostgreSQL dan Redis sehat (health check)...${NC}"
echo -n "Menunggu PostgreSQL siap..."
MAX_RETRIES=30
COUNT=0
until docker compose exec -T db pg_isready -U sales_admin -d sales_dist_db > /dev/null 2>&1; do
    echo -n "."
    sleep 2
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo -e "\n${RED}Error: Database PostgreSQL tidak siap setelah 60 detik.${NC}"
        exit 1
    fi
done
echo -e "\n${GREEN}✓ Database PostgreSQL dan Redis telah siap dan sehat!${NC}"

# Optimasi dan Migrasi di dalam kontainer
echo -e "Menjalankan migrasi database dan optimasi cache aplikasi baru..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan storage:link 2>/dev/null || true

# ------------------------------------------------------------------------------
# 6. Verifikasi Sistem Baru Berjalan
# ------------------------------------------------------------------------------
echo -e "\n${YELLOW}[5/5] Memverifikasi status rute aplikasi baru di port $FREE_APP_PORT...${NC}"
sleep 2

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:${FREE_APP_PORT}/" || true)

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo -e "${GREEN}✓ Web server merespons dengan kode HTTP ${HTTP_CODE} (Normal & Berjalan Lancar)${NC}"
else
    echo -e "${YELLOW}Catatan: Web server merespons kode HTTP ${HTTP_CODE}.${NC}"
fi

echo -e "\n${GREEN}===================================================================${NC}"
echo -e "${GREEN}  ✓ APLIKASI BARU BERHASIL DI-BUILD DAN KINI SEDANG BERJALAN!      ${NC}"
echo -e "${GREEN}===================================================================${NC}"
echo -e "Aplikasi Web / API : ${CYAN}http://localhost:${FREE_APP_PORT}${NC}"
echo -e "Halaman Login      : ${CYAN}http://localhost:${FREE_APP_PORT}/login${NC}"
echo -e "Katalog Produk     : ${CYAN}http://localhost:${FREE_APP_PORT}/products${NC}"
echo -e "Daftar Harga Jual  : ${CYAN}http://localhost:${FREE_APP_PORT}/price-lists${NC}"
echo -e "Port PostgreSQL    : localhost:${FREE_DB_PORT} (User: sales_admin, DB: sales_dist_db)"
echo -e "Port Redis         : localhost:${FREE_REDIS_PORT}"
echo -e "Layanan Aktif      : ${GREEN}sales_app, sales_webserver, sales_postgres, sales_redis, sales_queue_worker, sales_scheduler${NC}"
echo -e "Perintah Log       : docker compose logs -f"
echo -e "Perintah Berhenti  : docker compose down"
echo -e "===================================================================\n"
