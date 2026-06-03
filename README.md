# BookEase — Sistem Reservasi
## Struktur Proyek

```
booking-system/
├── config.php          # Konfigurasi database & fungsi global
├── index.php           # Halaman booking pelanggan
├── api.php             # API endpoint (JSON)
├── database.sql        # Schema & seed data MySQL
├── uploads/
│   └── payments/       # Bukti pembayaran (auto-created)
└── admin/
    ├── index.php       # Dashboard administrator
    ├── login.php       # Halaman login admin
    └── logout.php      # Logout
```

## Setup

### 1. Database
```sql
-- Import ke MySQL:
mysql -u root -p < database.sql
-- atau lewat phpMyAdmin: Import > pilih database.sql
```

### 2. Konfigurasi
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');      // password MySQL kamu
define('DB_NAME', 'booking_system');
```

### 3. Jalankan
- Taruh folder di `htdocs/` (XAMPP) atau `www/` (WAMP)
- Akses: `http://localhost/booking-system/`

## Akun Default Admin
- **Username:** `admin`
- **Password:** `admin123`

> Ganti password setelah login pertama!

## Fitur

### Pelanggan
- ✅ Lihat ketersediaan slot real-time
- ✅ Pilih layanan, tanggal, waktu
- ✅ Input data diri & konfirmasi booking
- ✅ Upload bukti transfer
- ✅ Cek status booking via kode

### Admin
- ✅ Dashboard overview dengan statistik
- ✅ Lihat & filter semua booking
- ✅ Update status booking (pending/confirmed/canceled)
- ✅ Verifikasi/tolak bukti pembayaran
- ✅ Auto-confirm booking saat pembayaran diverifikasi
- ✅ Auto-free slot saat booking dibatalkan

## Keamanan
- PDO Prepared Statements (anti SQL injection)
- File MIME type validation (upload)
- XSS sanitization
- Session-based admin auth
- Transaction lock untuk cegah double booking

## Tech Stack
- **Frontend:** Tailwind CSS CDN
- **Backend:** PHP 8+ murni (no framework)
- **Database:** MySQL 5.7+ / MariaDB
