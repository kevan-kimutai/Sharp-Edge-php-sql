-- ============================================================
-- GENTLEMAN'S EDGE - Complete Database Setup
-- ============================================================

-- Drop database if exists (optional - remove for production)
-- DROP DATABASE IF EXISTS gentlemans_edge;

-- Create database
CREATE DATABASE IF NOT EXISTS gentlemans_edge;
USE gentlemans_edge;

-- ============================================================
-- TABLE: bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    service VARCHAR(100) NOT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: contact_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admin_users
-- ============================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT ADMIN USER
-- ============================================================
-- Default credentials: username = 'admin', password = 'admin123'
-- Password hash generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO admin_users (username, password_hash) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username = username;

-- ============================================================
-- OPTIONAL: Sample Data for Testing
-- ============================================================
INSERT INTO bookings (full_name, email, phone, service, booking_date, time_slot, notes, status) VALUES
('John Smith', 'john@example.com', '+254 712345678', 'Classic Hot Towel Shave - $35', CURDATE(), '10:00 AM', 'First time customer - prefers senior barber', 'confirmed'),
('Michael Johnson', 'michael@example.com', '+254 723456789', 'Royal Beard Sculpt - $28', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '2:30 PM', 'Sensitive skin - use hypoallergenic products', 'pending'),
('David Williams', 'david@example.com', '+254 734567890', 'Luxury Head & Face Shave - $55', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:30 AM', 'Allergic to certain oils - please check', 'completed'),
('Robert Brown', 'robert@example.com', '+254 745678901', 'Classic Hot Towel Shave - $35', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '4:00 PM', '', 'pending'),
('James Wilson', 'james@example.com', '+254 756789012', 'Royal Beard Sculpt - $28', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '1:00 PM', 'Beard is 3 months grown', 'confirmed')
ON DUPLICATE KEY UPDATE full_name = full_name;

INSERT INTO contact_messages (name, email, subject, message, status) VALUES
('Sarah Thompson', 'sarah@example.com', 'Booking Inquiry', 'Do you offer group bookings for 5 people? We would like to book for a bachelor party.', 'unread'),
('Robert Brown', 'robert@example.com', 'Gift Voucher', 'I would like to purchase a gift voucher for my husband. Please provide pricing and options.', 'read'),
('Emily Davis', 'emily@example.com', 'General Question', 'What products do you use for sensitive skin? I have a condition and need to be careful.', 'replied'),
('Chris Evans', 'chris@example.com', 'Wedding Booking', 'Looking to book for wedding day grooming for 4 groomsmen. Please advise availability.', 'unread')
ON DUPLICATE KEY UPDATE name = name;

-- ============================================================
-- VERIFY INSTALLATION
-- ============================================================
SELECT '✅ Database setup complete!' AS status;
SELECT '👤 Admin user count:' AS info, COUNT(*) AS count FROM admin_users;
SELECT '📅 Bookings count:' AS info, COUNT(*) AS count FROM bookings;
SELECT '✉️ Messages count:' AS info, COUNT(*) AS count FROM contact_messages;

-- Show sample data
SELECT '📋 Sample Bookings:' AS info;
SELECT id, full_name, service, booking_date, status FROM bookings LIMIT 3;

SELECT '📋 Sample Messages:' AS info;
SELECT id, name, subject, status FROM contact_messages LIMIT 3;

-- ============================================================
-- QUICK REFERENCE QUERIES
-- ============================================================
-- SELECT * FROM bookings ORDER BY created_at DESC;
-- SELECT * FROM contact_messages WHERE status = 'unread';
-- UPDATE bookings SET status = 'confirmed' WHERE id = 1;
-- UPDATE contact_messages SET status = 'read' WHERE id = 1;