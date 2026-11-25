# Panduan Deployment ke Hostinger VPS Menggunakan Docker

Dokumen ini berisi panduan langkah demi langkah untuk melakukan deploy aplikasi **Web UK3P** ke **Hostinger VPS** menggunakan Docker & Docker Compose.

---

## 1. Arsitektur Docker yang Disediakan

Aplikasi ini menggunakan konfigurasi multi-container:
1. **`app` (`uk3p-app`)**: Container PHP 8.2-FPM yang menjalankan Laravel 10 (termasuk build otomatis asset Vite via Node 20).
2. **`webserver` (`uk3p-webserver`)**: Nginx Alpine yang berfungsi sebagai reverse proxy ke PHP-FPM dan melayani static files serta storage uploads secara langsung.
3. **`db` (`uk3p-db`)**: MySQL 8.0 dengan persistent volume data.
4. **`whatsapp-notifier` (`uk3p-whatsapp-notifier`)**: Microservice Node.js untuk integrasi WhatsApp Cloud API.
5. **`queue` (`uk3p-queue`)**: Worker background job Laravel untuk memproses antrean email, notifikasi, dll.

---

## 2. Persiapan di Hostinger VPS

### A. Login ke VPS via SSH
Buka terminal (PowerShell, Git Bash, atau Terminal macOS/Linux) dan jalankan:
```bash
ssh root@IP_VPS_ANDA
```

### B. Update Sistem & Install Docker
Jika VPS Anda belum terinstall Docker & Docker Compose:
```bash
# Update paket
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y ca-certificates curl gnupg lsb-release git

# Install Docker engine
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Verifikasi instalasi Docker & Docker Compose
docker --version
docker compose version
```

---

## 3. Clone Repository & Setup Konfigurasi

### A. Clone Repo di VPS
```bash
cd /var/www  # atau direktori pilihan Anda
git clone https://github.com/ditoww/web-uk3p.git
cd web-uk3p
```

### B. Siapkan File `.env`
Salin template docker environment yang telah disediakan:
```bash
cp .env.docker.example .env
```

Buka dan sesuaikan isi file `.env`:
```bash
nano .env
```

Pastikan variabel-variabel kunci berikut diisi dengan benar:
- `APP_URL`: Alamat domain Anda (contoh: `https://domainanda.com` atau `http://IP_VPS`)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`: Tentukan password database yang kuat.
- `WHATSAPP_PHONE_ID`, `WHATSAPP_ACCESS_TOKEN`: Isi jika fitur notifikasi WhatsApp digunakan.
- Konfigurasi Firebase (untuk fitur Realtime Chat): salin dari panduan `FIREBASE_ENV_CONFIG.md`.

Simpan perubahan (`Ctrl + O`, `Enter`, lalu `Ctrl + X`).

---

## 4. Menjalankan Docker Compose

### A. Build dan Jalankan Container
Jalankan perintah berikut di dalam direktori `web-uk3p`:
```bash
docker compose up -d --build
```
> **Catatan**: Proses build pertama kali membutuhkan waktu beberapa menit karena akan mendownload base image, menginstall ekstensi PHP, mendownload paket composer, dan melakukan build asset Vite.

### B. Cek Status Container
```bash
docker compose ps
```
Pastikan semua container (`uk3p-app`, `uk3p-webserver`, `uk3p-db`, `uk3p-whatsapp-notifier`, `uk3p-queue`) berada dalam status `Up` atau `healthy`.

---

## 5. Migrasi Database & Seeding

Setelah container database siap, jalankan migration database Laravel:
```bash
# Jalankan migrasi
docker compose exec app php artisan migrate --force

# (Opsional) Jalankan seeder jika ada data awal
docker compose exec app php artisan db:seed --force
```

---

## 6. Setup Domain & SSL (HTTPS)

### Opsi 1: Menggunakan Cloudflare (Paling Mudah & Gratis)
1. Arahkan DNS domain (`A record`) ke IP VPS Hostinger Anda.
2. Aktifkan **Proxy (Orange Cloud)** di Cloudflare.
3. Di tab **SSL/TLS** Cloudflare, pilih mode **Flexible** atau **Full**.
4. Website Anda langsung aman menggunakan HTTPS tanpa perlu konfigurasi certbot di server.

### Opsi 2: Menggunakan Certbot Let's Encrypt Langsung di Hostinger
Jika ingin sertifikat SSL langsung di Nginx VPS:
1. Pastikan domain sudah terarah ke IP VPS.
2. Install Certbot di VPS:
   ```bash
   sudo apt install -y certbot python3-certbot-nginx
   ```
3. Alternatif lain adalah menggunakan **Nginx Proxy Manager** atau Traefik jika Anda menjalankan banyak container di VPS.

---

## 7. Perintah Pemeliharaan Harian (Maintenance)

- **Melihat Log Aplikasi & Server:**
  ```bash
  docker compose logs -f app
  docker compose logs -f webserver
  docker compose logs -f whatsapp-notifier
  ```

- **Masuk ke Shell Container Laravel:**
  ```bash
  docker compose exec app bash
  ```

- **Menghapus Cache Laravel:**
  ```bash
  docker compose exec app php artisan optimize:clear
  docker compose exec app php artisan optimize
  ```

- **Restart Semua Service:**
  ```bash
  docker compose restart
  ```

- **Deploy Update Kode Terbaru dari Git:**
  ```bash
  git pull origin main
  docker compose up -d --build
  docker compose exec app php artisan migrate --force
  docker compose exec app php artisan optimize
  ```

- **Hentikan Seluruh Layanan:**
  ```bash
  docker compose down
  ```

