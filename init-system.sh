#!/usr/bin/env bash
# ==============================================================================
# Enterprise Sales & Distribution System - Setup & Initialization Script
# ==============================================================================

set -e

# Terminal colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}===================================================================${NC}"
echo -e "${BLUE}  Multi-Branch Enterprise Sales & Distribution Management System   ${NC}"
echo -e "${BLUE}===================================================================${NC}"

# Step 1: Environment file check
echo -e "\n${YELLOW}[1/6] Memeriksa file .env...${NC}"
if [ ! -f .env ]; then
    echo -e "File .env belum ditemukan. Menyalin dari .env.example..."
    cp .env.example .env
    echo -e "${GREEN}✓ File .env berhasil dibuat.${NC}"
else
    echo -e "${GREEN}✓ File .env sudah ada.${NC}"
fi

# Step 2: Check Docker Daemon
echo -e "\n${YELLOW}[2/6] Memeriksa status Docker daemon...${NC}"
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker daemon tidak berjalan atau tidak dapat diakses.${NC}"
    echo -e "Silakan jalankan Docker Desktop atau Docker daemon terlebih dahulu."
    exit 1
fi
echo -e "${GREEN}✓ Docker daemon aktif dan siap.${NC}"

# Step 3: Build & Start Containers
echo -e "\n${YELLOW}[3/6] Membangun dan menjalankan kontainer Docker (Docker Compose)...${NC}"
docker compose up -d --build

# Step 4: Wait for PostgreSQL database health
echo -e "\n${YELLOW}[4/6] Menunggu database PostgreSQL dan Redis sehat (health check)...${NC}"
echo -n "Menunggu PostgreSQL siap..."
until docker compose exec -T db pg_isready -U sales_admin -d sales_dist_db > /dev/null 2>&1; do
    echo -n "."
    sleep 2
done
echo -e "\n${GREEN}✓ Database PostgreSQL dan Redis telah siap!${NC}"

# Step 5: Install Composer dependencies & Generate Key (if needed)
echo -e "\n${YELLOW}[5/6] Memeriksa dependensi aplikasi Laravel di dalam kontainer...${NC}"
if [ ! -f vendor/autoload.php ]; then
    echo "Menjalankan composer install di dalam kontainer 'app'..."
    docker compose exec -T app composer install --optimize-autoloader --no-interaction
fi

# Generate App Key if empty
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=base64:sP8bQ7U8oU5mGZ4k2h3n4j5l6k7m8n9p0q1r2s3t4u5=" .env; then
    echo "Memperbarui Laravel Application Key..."
    docker compose exec -T app php artisan key:generate --force || true
fi

# Step 6: Database Migration & Seeding
echo -e "\n${YELLOW}[6/6] Menjalankan migrasi database dan database seeder...${NC}"
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan db:seed --force || true

# Storage link & permissions
docker compose exec -T app php artisan storage:link || true

echo -e "\n${GREEN}===================================================================${NC}"
echo -e "${GREEN}  ✓ SISTEM BERHASIL DIINISIALISASI DAN SIAP DIGUNAKAN!              ${NC}"
echo -e "${GREEN}===================================================================${NC}"
echo -e "Aplikasi Web / API : http://localhost:8080"
echo -e "PostgreSQL Port    : localhost:5432 (User: sales_admin, DB: sales_dist_db)"
echo -e "Redis Port         : localhost:6379"
echo -e "Layanan aktif      : app, webserver, db, redis, queue_worker, scheduler"
echo -e "Perintah log       : docker compose logs -f"
echo -e "===================================================================\n"

