<?php
/**
 * Gentleman's Edge Barbershop
 * Booking Form Handler - PHP Backend
 * Handles booking form submissions via POST method
 */

header('Content-Type: application/json; charset=utf-8');

// Include database configuration
require_once 'db_config.php';

// ============================================================
// CHECK REQUEST METHOD - MUST BE POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method. Only POST is allowed.'
    ]);
    exit;
}

// ============================================================
// GET DATABASE CONNECTION
// ============================================================
$conn = getDBConnection();

// ============================================================
// RETRIEVE AND SANITIZE FORM DATA
// ============================================================
$fullName = isset($_POST['fullName']) ? sanitizeInput($_POST['fullName'], $conn) : '';
$email = isset($_POST['emailAddr']) ? sanitizeInput($_POST['emailAddr'], $conn) : '';
$phone = isset($_POST['phoneNum']) ? sanitizeInput($_POST['phoneNum'], $conn) : '';
$service = isset($_POST['serviceChoice']) ? sanitizeInput($_POST['serviceChoice'], $conn) : '';
$bookingDate = isset($_POST['sessionDate']) ? sanitizeInput($_POST['sessionDate'], $conn) : '';
$timeSlot = isset($_POST['timeSlot']) ? sanitizeInput($_POST['timeSlot'], $conn) : '';
$notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes'], $conn) : '';

// ============================================================
// VALIDATE REQUIRED FIELDS
// ============================================================
$errors = [];

// Full Name Validation
if (empty($fullName)) {
    $errors[] = 'Full name is required.';
} elseif (strlen($fullName) < 3) {
    $errors[] = 'Full name must be at least 3 characters.';
} elseif (strlen($fullName) > 100) {
    $errors[] = 'Full name must not exceed 100 characters.';
}

// Email Validation
if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!validateEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email must not exceed 100 characters.';
}

// Phone Validation (optional but must be valid if provided)
if (!empty($phone)) {
    if (!validatePhone($phone)) {
        $errors[] = 'Phone number must be between 8 and 20 characters.';
    }
}

// Service Validation
if (empty($service)) {
    $errors[] = 'Please select a service.';
}

// Booking Date Validation
if (empty($bookingDate)) {
    $errors[] = 'Please select a booking date.';
} elseif (!validateDate($bookingDate)) {
    $errors[] = 'Booking date must be today or in the future.';
}

// Time Slot Validation
if (empty($timeSlot)) {
    $errors[] = 'Please select a time slot.';
}

// Notes Validation (optional)
if (strlen($notes) > 500) {
    $errors[] = 'Notes must not exceed 500 characters.';
}

// ============================================================
// IF VALIDATION FAILS, RETURN ERRORS
// ============================================================
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'errors' => $errors,
        'message' => 'Please correct the following errors:'
    ]);
    closeDBConnection($conn);
    exit;
}

// ============================================================
// INSERT BOOKING INTO DATABASE USING PREPARED STATEMENTS
// ============================================================
try {
    // Prepare SQL statement
    $sql = "INSERT INTO bookings (full_name, email, phone, service, booking_date, time_slot, notes, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    // Create prepared statement
    $stmt = $conn->prepare($sql);
    
    // Check if preparation was successful
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters to the prepared statement
    $stmt->bind_param(
        'sssssss',
        $fullName,
        $email,
        $phone,
        $service,
        $bookingDate,
        $timeSlot,
        $notes
    );
    
    // Execute the prepared statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get the booking ID
    $bookingId = $conn->insert_id;
    
    // Close the prepared statement
    $stmt->close();
    
    // ============================================================
    // SUCCESS: RETURN CONFIRMATION
    // ============================================================
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'booking_id' => $bookingId,
        'message' => 'Booking confirmed successfully!',
        'details' => [
            'name' => $fullName,
            'email' => $email,
            'service' => $service,
            'date' => $bookingDate,
            'time' => $timeSlot
        ],
        'confirmation' => "Thank you {$fullName}! Your {$service} booking is confirmed for {$bookingDate} at {$timeSlot}. Confirmation sent to {$email}."
    ]);
    
} catch (Exception $e) {
    // ============================================================
    // DATABASE ERROR
    // ============================================================
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'message' => 'Unable to process booking. Please try again later.'
    ]);
} finally {
    // Always close the database connection
    closeDBConnection($conn);
}
?>
