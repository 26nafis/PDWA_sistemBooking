<?php
// api.php - REST-like API endpoints

require_once 'config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_slots':
        getSlots();
        break;
    case 'get_services':
        getServices();
        break;
    case 'create_booking':
        createBooking();
        break;
    case 'upload_payment':
        uploadPayment();
        break;
    case 'check_booking':
        checkBooking();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

// ─── Get available slots for a service+date ───────────────────────────────────
function getSlots(): void {
    $db = getDB();
    $serviceId = (int)($_GET['service_id'] ?? 0);
    $date = $_GET['date'] ?? date('Y-m-d');

    if (!$serviceId) { echo json_encode(['error' => 'Service ID required']); return; }

    // Validate date
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $date < date('Y-m-d')) {
        echo json_encode(['error' => 'Invalid or past date']);
        return;
    }

    // Get service info
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    if (!$service) { echo json_encode(['error' => 'Service not found']); return; }

    // Generate slots based on service open/close time
    $slots = [];
    $openTime  = new DateTime($date . ' ' . $service['open_time']);
    $closeTime = new DateTime($date . ' ' . $service['close_time']);
    $duration  = $service['slot_duration_minutes'];

    $current = clone $openTime;
    while ($current < $closeTime) {
        $slotEnd = clone $current;
        $slotEnd->modify("+{$duration} minutes");
        if ($slotEnd > $closeTime) break;

        $startStr = $current->format('H:i:s');
        $endStr   = $slotEnd->format('H:i:s');

        // Check DB status
        $stmt2 = $db->prepare("SELECT status FROM time_slots WHERE service_id=? AND slot_date=? AND start_time=?");
        $stmt2->execute([$serviceId, $date, $startStr]);
        $dbSlot = $stmt2->fetch();

        $now = new DateTime();
        $isPast = ($current <= $now);

        $status = 'available';
        if ($isPast) {
            $status = 'past';
        } elseif ($dbSlot) {
            $status = $dbSlot['status'];
        }

        $slots[] = [
            'start'  => $current->format('H:i'),
            'end'    => $slotEnd->format('H:i'),
            'status' => $status,
            'price'  => $service['price_per_slot'],
        ];

        $current = $slotEnd;
    }

    echo json_encode(['slots' => $slots, 'service' => $service, 'date' => $date]);
}

// ─── Get all active services ──────────────────────────────────────────────────
function getServices(): void {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY name");
    echo json_encode(['services' => $stmt->fetchAll()]);
}

// ─── Create booking ───────────────────────────────────────────────────────────
function createBooking(): void {
    $db = getDB();

    $name      = sanitize($_POST['name'] ?? '');
    $phone     = sanitize($_POST['phone'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $serviceId = (int)($_POST['service_id'] ?? 0);
    $date      = $_POST['date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $notes     = sanitize($_POST['notes'] ?? '');

    if (!$name || !$phone || !$serviceId || !$date || !$startTime) {
        echo json_encode(['error' => 'Data tidak lengkap']); return;
    }

    // Validate not past
    if (isPastSlot($date, $startTime)) {
        echo json_encode(['error' => 'Slot waktu sudah lewat']); return;
    }

    // Get service info for price and end time
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();
    if (!$service) { echo json_encode(['error' => 'Layanan tidak ditemukan']); return; }

    $endTime = date('H:i:s', strtotime($startTime) + ($service['slot_duration_minutes'] * 60));

    // Check for double booking (use transaction + lock)
    $db->beginTransaction();
    try {
        // Lock the slot check
        $stmt = $db->prepare(
            "SELECT id FROM time_slots WHERE service_id=? AND slot_date=? AND start_time=? AND status='booked' FOR UPDATE"
        );
        $stmt->execute([$serviceId, $date, $startTime]);
        if ($stmt->fetch()) {
            $db->rollBack();
            echo json_encode(['error' => 'Slot sudah dipesan oleh orang lain. Silakan pilih slot lain.']);
            return;
        }

        // Upsert customer
        $stmt = $db->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $customer = $stmt->fetch();

        if ($customer) {
            $customerId = $customer['id'];
            $db->prepare("UPDATE customers SET name=?, email=? WHERE id=?")->execute([$name, $email, $customerId]);
        } else {
            $db->prepare("INSERT INTO customers (name, phone, email) VALUES (?,?,?)")->execute([$name, $phone, $email]);
            $customerId = (int)$db->lastInsertId();
        }

        // Create booking
        $code = generateBookingCode();
        $price = $service['price_per_slot'];

        $db->prepare("INSERT INTO bookings (booking_code, customer_id, service_id, slot_date, start_time, end_time, total_price, notes) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$code, $customerId, $serviceId, $date, $startTime, $endTime, $price, $notes]);
        $bookingId = (int)$db->lastInsertId();

        // Lock the slot
        $stmt = $db->prepare("INSERT INTO time_slots (service_id, slot_date, start_time, end_time, status) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE status='booked'");
        $stmt->execute([$serviceId, $date, $startTime, $endTime, 'booked']);

        // Create pending payment record
        $db->prepare("INSERT INTO payments (booking_id) VALUES (?)")->execute([$bookingId]);

        $db->commit();

        echo json_encode([
            'success'      => true,
            'booking_code' => $code,
            'booking_id'   => $bookingId,
            'total_price'  => $price,
            'message'      => 'Booking berhasil dibuat!'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['error' => 'Gagal membuat booking: ' . $e->getMessage()]);
    }
}

// ─── Upload payment proof ─────────────────────────────────────────────────────
function uploadPayment(): void {
    $db = getDB();
    $bookingCode = sanitize($_POST['booking_code'] ?? '');
    $method      = sanitize($_POST['payment_method'] ?? 'Transfer Bank');

    if (!$bookingCode) { echo json_encode(['error' => 'Kode booking diperlukan']); return; }
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File bukti pembayaran diperlukan']); return;
    }

    $stmt = $db->prepare("SELECT b.*, p.id as payment_id FROM bookings b LEFT JOIN payments p ON p.booking_id=b.id WHERE b.booking_code=?");
    $stmt->execute([$bookingCode]);
    $booking = $stmt->fetch();

    if (!$booking) { echo json_encode(['error' => 'Kode booking tidak ditemukan']); return; }
    if ($booking['status'] === 'canceled') { echo json_encode(['error' => 'Booking telah dibatalkan']); return; }

    $file = $_FILES['payment_proof'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['error' => 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF']); return;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        echo json_encode(['error' => 'Ukuran file maksimal 5MB']); return;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $bookingCode . '_' . time() . '.' . strtolower($ext);
    $targetPath = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['error' => 'Gagal mengupload file']); return;
    }

    $db->prepare("UPDATE payments SET payment_proof=?, payment_method=?, status='pending' WHERE booking_id=?")
       ->execute([$filename, $method, $booking['id']]);

    echo json_encode(['success' => true, 'message' => 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.']);
}

// ─── Check booking status ─────────────────────────────────────────────────────
function checkBooking(): void {
    $db = getDB();
    $code = sanitize($_GET['code'] ?? '');
    if (!$code) { echo json_encode(['error' => 'Kode booking diperlukan']); return; }

    $stmt = $db->prepare("
        SELECT b.*, c.name as customer_name, c.phone, s.name as service_name,
               p.status as payment_status, p.payment_method, p.verified_at
        FROM bookings b
        JOIN customers c ON c.id = b.customer_id
        JOIN services s ON s.id = b.service_id
        LEFT JOIN payments p ON p.booking_id = b.id
        WHERE b.booking_code = ?
    ");
    $stmt->execute([$code]);
    $booking = $stmt->fetch();

    if (!$booking) { echo json_encode(['error' => 'Booking tidak ditemukan']); return; }

    echo json_encode(['booking' => $booking]);
}
