<?php
/**
 * Gentleman's Edge Barbershop
 * Database Configuration and Connection Handler
 */

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'gentlemans_edge');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

// ============================================================
// ESTABLISH MYSQLI CONNECTION
// ============================================================
function getDBConnection() {
    // Create connection using MySQLi
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    // Check connection
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'error' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    // Set charset to UTF8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

// ============================================================
// CLOSE CONNECTION FUNCTION
// ============================================================
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

// ============================================================
// HELPER: SANITIZE INPUT
// ============================================================
function sanitizeInput($data, $conn) {
    return htmlspecialchars($conn->real_escape_string(trim($data)));
}

// ============================================================
// HELPER: VALIDATE EMAIL
// ============================================================
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================================
// HELPER: VALIDATE PHONE (simple check)
// ============================================================
function validatePhone($phone) {
    return strlen($phone) >= 8 && strlen($phone) <= 20;
}

// ============================================================
// HELPER: VALIDATE DATE (must be today or future)
// ============================================================
function validateDate($date) {
    $today = date('Y-m-d');
    return $date >= $today;
}
?>
