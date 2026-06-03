-- =============================================
-- SISTEM BOOKING - Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS booking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE booking_system;

-- Table: services (jenis layanan)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_slot DECIMAL(10,2) NOT NULL DEFAULT 0,
    slot_duration_minutes INT NOT NULL DEFAULT 60,
    open_time TIME NOT NULL DEFAULT '08:00:00',
    close_time TIME NOT NULL DEFAULT '22:00:00',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: time_slots (slot waktu per hari)
CREATE TABLE time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('available', 'booked', 'blocked') DEFAULT 'available',
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (service_id, slot_date, start_time)
);

-- Table: customers
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: bookings (reservasi utama)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    service_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Table: payments (bukti pembayaran)
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    payment_proof VARCHAR(255),
    payment_method VARCHAR(50),
    amount DECIMAL(10,2),
    verified_at TIMESTAMP NULL,
    verified_by VARCHAR(100),
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Table: admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- SEED DATA
-- =============================================

-- Default admin (password: admin123)
INSERT INTO admins (username, password, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Sample services
INSERT INTO services (name, description, price_per_slot, slot_duration_minutes, open_time, close_time) VALUES
('Lapangan Futsal A', 'Lapangan futsal standar ukuran regulasi, rumput sintetis premium', 100000, 60, '08:00:00', '22:00:00'),
('Lapangan Futsal B', 'Lapangan futsal indoor dengan pencahayaan LED', 120000, 60, '08:00:00', '22:00:00'),
('Studio Musik', 'Studio rekaman lengkap dengan instrumen dan sound system profesional', 150000, 60, '09:00:00', '21:00:00'),
('Barbershop', 'Layanan potong rambut dan grooming profesional', 75000, 30, '08:00:00', '20:00:00');
