<?php
// config.php - Database Configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'TekniInformasi24');
define('DB_NAME', 'booking_system');

define('SITE_NAME', 'BookEase');
define('SITE_TAGLINE', 'Reservasi Mudah, Jadwal Teratur');
define('UPLOAD_DIR', __DIR__ . '/uploads/payments/');
define('UPLOAD_URL', 'uploads/payments/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function generateBookingCode(): string {
    return 'BK' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate(string $date): string {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d) return $date;
    $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return $days[$d->format('w')] . ', ' . $d->format('j') . ' ' . $months[(int)$d->format('n')] . ' ' . $d->format('Y');
}

function isPastSlot(string $date, string $time): bool {
    $slotDateTime = strtotime($date . ' ' . $time);
    return $slotDateTime < time();
}

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

session_start();
