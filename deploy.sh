#!/bin/bash
set -e

echo "=========================================================="
echo "   DEPLOYMENT OTOMATIS WEB-UK3P KE HOSTINGER VPS"
echo "=========================================================="

# 1. Update paket & pastikan Docker terinstall
echo "[1/5] Memeriksa dan menginstall Docker..."
if ! command -v docker &> /dev/null; then
    sudo apt-get update && sudo apt-get install -y ca-certificates curl gnupg git
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    rm -f get-docker.sh
fi

if ! docker compose version &> /dev/null; then
    sudo apt-get install -y docker-compose-plugin
fi

# 2. Siapkan file .env jika belum ada
echo "[2/5] Menyiapkan environment (.env)..."
if [ ! -f .env ]; then
    cp .env.docker.example .env
    
    # Generate password acak yang aman untuk database
    DB_PASS=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 16)
    DB_ROOT_PASS=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 20)
    
    sed -i "s/GantiPasswordUserDatabase123!/$DB_PASS/g" .env
    sed -i "s/GantiPasswordRootDatabase123!/$DB_ROOT_PASS/g" .env
    
    # Deteksi IP Publik server
    SERVER_IP=$(curl -s https://api.ipify.org || echo "localhost")
    sed -i "s|APP_URL=http://your-domain.com|APP_URL=http://$SERVER_IP|g" .env
    
    echo ">> File .env berhasil dibuat dengan password database acak yang aman."
    echo ">> APP_URL diset ke http://$SERVER_IP"
fi

# 3. Build & Jalankan Docker Container
echo "[3/5] Membangun dan menjalankan container (ini butuh 2-4 menit pertama kali)..."
docker compose up -d --build

# 4. Tunggu container database dan app siap
echo "[4/5] Menunggu database dan aplikasi siap..."
sleep 15

# 5. Jalankan Migrasi Database
echo "[5/5] Menjalankan migrasi database..."
docker compose exec -T app php artisan migrate --force

echo ""
echo "=========================================================="
echo "   🎉 DEPLOYMENT SELESAI & BERHASIL!"
echo "=========================================================="
docker compose ps
echo ""
echo "Website dapat diakses di: http://$(curl -s https://api.ipify.org || echo 'IP_VPS_ANDA')"
echo "File konfigurasi berada di: $(pwd)/.env"
echo "Untuk mengedit konfigurasi: nano .env && docker compose restart"
echo "=========================================================="

