<?php
/**
 * Gentleman's Edge Barbershop
 * Contact Form Handler - PHP Backend
 * Handles contact form submissions via POST method
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
$name = isset($_POST['contactName']) ? sanitizeInput($_POST['contactName'], $conn) : '';
$email = isset($_POST['contactEmail']) ? sanitizeInput($_POST['contactEmail'], $conn) : '';
$subject = isset($_POST['contactSubject']) ? sanitizeInput($_POST['contactSubject'], $conn) : '';
$message = isset($_POST['contactMsg']) ? sanitizeInput($_POST['contactMsg'], $conn) : '';

// ============================================================
// VALIDATE REQUIRED FIELDS
// ============================================================
$errors = [];

// Name Validation
if (empty($name)) {
    $errors[] = 'Name is required.';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters.';
} elseif (strlen($name) > 100) {
    $errors[] = 'Name must not exceed 100 characters.';
}

// Email Validation
if (empty($email)) {
    $errors[] = 'Email address is required.';
} elseif (!validateEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email must not exceed 100 characters.';
}

// Subject Validation (optional)
if (strlen($subject) > 200) {
    $errors[] = 'Subject must not exceed 200 characters.';
}

// Message Validation
if (empty($message)) {
    $errors[] = 'Message is required.';
} elseif (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
} elseif (strlen($message) > 1000) {
    $errors[] = 'Message must not exceed 1000 characters.';
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
// INSERT CONTACT MESSAGE INTO DATABASE USING PREPARED STATEMENTS
// ============================================================
try {
    // Prepare SQL statement
    $sql = "INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
            VALUES (?, ?, ?, ?, 'unread', NOW())";
    
    // Create prepared statement
    $stmt = $conn->prepare($sql);
    
    // Check if preparation was successful
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters to the prepared statement
    $stmt->bind_param(
        'ssss',
        $name,
        $email,
        $subject,
        $message
    );
    
    // Execute the prepared statement
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get the message ID
    $messageId = $conn->insert_id;
    
    // Close the prepared statement
    $stmt->close();
    
    // ============================================================
    // SUCCESS: RETURN CONFIRMATION
    // ============================================================
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message_id' => $messageId,
        'message' => 'Message sent successfully!',
        'details' => [
            'name' => $name,
            'email' => $email,
            'subject' => !empty($subject) ? $subject : 'General Inquiry',
            'timestamp' => date('Y-m-d H:i:s')
        ],
        'confirmation' => "Thank you {$name}! Your message has been received. Our team at Gentleman's Edge will respond within 24 hours."
    ]);
    
} catch (Exception $e) {
    // ============================================================
    // DATABASE ERROR
    // ============================================================
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'message' => 'Unable to send message. Please try again later.'
    ]);
} finally {
    // Always close the database connection
    closeDBConnection($conn);
}
?>
