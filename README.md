<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## WhatsApp Notifier (Microservice Node.js)

Bagian ini menjelaskan cara menjalankan layanan Node.js ringan yang digunakan aplikasi untuk mengirim notifikasi WhatsApp ketika ada balasan chat (support ↔ developer ↔ client).

### Lokasi Source
`whatsapp-notifier/` (di dalam root project ini)

Struktur singkat:
```
whatsapp-notifier/
	package.json
	src/
		server.js
```

### Prasyarat
- Node.js ≥ 18
- WhatsApp Cloud API (Meta): `WA_PHONE_ID` & `WA_TOKEN`
- Akses internet keluar ke `graph.facebook.com`

### Variabel Environment (Wajib / Opsional)
| Variable | Wajib | Contoh | Keterangan |
|----------|-------|--------|-----------|
| WA_PHONE_ID | Ya | 123456789012345 | Phone Number ID (bukan Business Account ID) |
| WA_TOKEN | Ya | EAAG... | Long‑lived token WhatsApp Cloud API |
| PORT | Opsional | 4001 | Port service lokal |
| WA_API_URL | Opsional | https://graph.facebook.com | Base URL API |
| WA_API_VERSION | Opsional | v22.0 | Versi Graph API |
| WA_NOTIFICATION_TEMPLATE_NAME | Opsional | ticket_update | Untuk future template (belum dipakai) |

### Menjalankan (Windows PowerShell)
Masuk folder dan install dependency (sekali saja):
```
cd c:\xampp\htdocs\TickDesk\whatsapp-notifier
npm install
```
Set environment sementara (atau set lewat System Environment Variables):
```
$env:WA_PHONE_ID = "123456789012345"
$env:WA_TOKEN    = "EAAGXXXXXXXXXXXXXXXXXXXXXXXX"
$env:PORT        = "4001"
npm start
```
Output berhasil (contoh):
```
[info] WhatsApp notifier listening on :4001
```

### Health Check
```
GET http://localhost:4001/health
Response: { "ok": true }
```

### Endpoint Kirim Pesan
`POST /send`
Body JSON minimal:
```json
{
	"to": "62XXXXXXXXXXX",
	"sender": "Support",
	"message": "Balasan terbaru...",
	"ticketId": 101
}
```
Field opsional: `ticketTitle`, `status`, `chatType` (support_to_dev | dev_to_support | support_to_client)

Contoh PowerShell (gunakan kutip ganda untuk JSON):
```
curl -Method POST -Uri http://localhost:4001/send -Headers @{"Content-Type"="application/json"} -Body '{"to":"62XXXXXXXXXXX","sender":"Dev","message":"Tes notif","ticketId":99}'
```
Response sukses mengandung `requestId` & `ms` (durasi ms).

### Format Pesan yang Dikirim ke WhatsApp
```
*<sender>* membalas tiket <ticketTitle>:\n<message>
```
Jika `ticketTitle` kosong bagian itu dihilangkan.

### Integrasi di Laravel
Service Laravel memanggil microservice via env berikut pada `.env` utama:
```
WHATSAPP_MICROSERVICE_URL=http://localhost:4001
```
Log Laravel spesifik WhatsApp: `storage/logs/whatsapp-YYYY-MM-DD.log`

### Logging Microservice
Console (pino-pretty) menampilkan:
- `incoming /send` (request masuk)
- `WA message sent` (sukses)
- `WA send failed` (gagal)
Disertai `requestId`, `ms`, `ticketId`, `to`.

### Troubleshooting Cepat
| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| 500 missing WA config | Env belum diset | Set `WA_PHONE_ID`, `WA_TOKEN` |
| 400 validation failed | Body kurang field | Perbaiki JSON (wajib: to, sender, message, ticketId) |
| 502 WA send failed | Token salah / nomor belum opt-in | Verifikasi token & status nomor di Meta | 

### Rencana Pengembangan (Opsional)
- Template message (tipe `template` WA)
- Retry & queue (Bull / Redis) + exponential backoff
- Metrics (Prometheus) + dashboard
- API key internal untuk menghindari abuse

---
>>>>>>> 944ced1 (Initial commit)
