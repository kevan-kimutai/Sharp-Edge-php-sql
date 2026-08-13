


<?php
// ============================================================
// GENTLEMAN'S EDGE - Complete PHP Backend with Database
// ============================================================

session_start();

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'gentlemans_edge');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    try {
        return new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
    }
}

// ============================================================
// ROUTING
// ============================================================
$route = $_GET['route'] ?? 'home';
$page = $_GET['page'] ?? 'home';

// ============================================================
// API ENDPOINTS
// ============================================================
if ($route === 'api') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
    
    $pdo = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = $_GET['endpoint'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    // --- BOOKING API ---
    if ($endpoint === 'booking') {
        if ($method === 'POST') {
            // Validate required fields
            $required = ['fullName', 'email', 'service', 'date', 'timeSlot'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    echo json_encode(['success' => false, 'error' => "$field is required"]);
                    exit;
                }
            }
            
            // Validate email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'error' => 'Invalid email address']);
                exit;
            }
            
            // Validate date (must be today or future)
            if ($data['date'] < date('Y-m-d')) {
                echo json_encode(['success' => false, 'error' => 'Date must be today or future']);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (full_name, email, phone, service, booking_date, time_slot, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['fullName'],
                    $data['email'],
                    $data['phone'] ?? '',
                    $data['service'],
                    $data['date'],
                    $data['timeSlot'],
                    $data['notes'] ?? ''
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking confirmed successfully! We\'ll send confirmation to ' . $data['email'],
                    'booking_id' => $pdo->lastInsertId()
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            }
            exit;
        }
        
        if ($method === 'GET') {
            try {
                if (isset($_GET['id'])) {
                    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    echo json_encode($stmt->fetch() ?: ['error' => 'Booking not found']);
                } else {
                    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
                    echo json_encode($stmt->fetchAll());
                }
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            exit;
        }
        
        if ($method === 'PUT') {
            if (empty($data['id']) || empty($data['status'])) {
                echo json_encode(['success' => false, 'error' => 'Missing id or status']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$data['status'], $data['id']]);
                echo json_encode(['success' => true, 'message' => 'Booking status updated']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
        
        if ($method === 'DELETE') {
            if (empty($_GET['id'])) {
                echo json_encode(['success' => false, 'error' => 'Missing id']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                echo json_encode(['success' => true, 'message' => 'Booking deleted']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
    }
    
    // --- CONTACT API ---
    if ($endpoint === 'contact') {
        if ($method === 'POST') {
            if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
                echo json_encode(['success' => false, 'error' => 'Name, email and message are required']);
                exit;
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'error' => 'Invalid email address']);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages (name, email, subject, message) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    $data['subject'] ?? 'General Inquiry',
                    $data['message']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Message sent successfully! We\'ll respond within 24 hours.'
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            }
            exit;
        }
        
        if ($method === 'GET') {
            try {
                $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
                echo json_encode($stmt->fetchAll());
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            exit;
        }
        
        if ($method === 'PUT') {
            if (empty($data['id']) || empty($data['status'])) {
                echo json_encode(['success' => false, 'error' => 'Missing id or status']);
                exit;
            }
            try {
                $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
                $stmt->execute([$data['status'], $data['id']]);
                echo json_encode(['success' => true, 'message' => 'Message status updated']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
    }
    
    echo json_encode(['error' => 'Invalid API endpoint']);
    exit;
}

// ============================================================
// ADMIN LOGIN
// ============================================================
if ($route === 'admin-login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($_POST['password'], $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            header('Location: ?route=admin');
            exit;
        }
        $error = 'Invalid username or password';
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login - Gentleman's Edge</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: 'Inter', sans-serif; background: #fef8f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
            .login-box { background: white; padding: 3rem; border-radius: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.1); border: 1px solid #f0e5d8; width: 100%; max-width: 400px; }
            .login-box h1 { font-family: 'Playfair Display', serif; text-align: center; margin-bottom: 0.5rem; color: #2c241a; }
            .login-box h1 span { color: #d9a13b; }
            .login-box p { text-align: center; color: #5a4a3a; margin-bottom: 2rem; }
            .form-group { margin-bottom: 1.5rem; }
            .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #2c241a; }
            .form-group input { width: 100%; padding: 0.8rem 1rem; border: 2px solid #e8ddd0; border-radius: 1rem; background: #fefaf5; font-size: 1rem; transition: 0.3s; }
            .form-group input:focus { outline: none; border-color: #d9a13b; box-shadow: 0 0 0 4px rgba(217, 161, 59, 0.1); }
            .btn-login { width: 100%; padding: 0.9rem; background: #1e1a15; color: #f0e5d8; border: none; border-radius: 1.5rem; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: 0.3s; }
            .btn-login:hover { background: #3a2f24; transform: translateY(-2px); }
            .error { background: #fde8e8; color: #a13333; padding: 0.8rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 4px solid #b33; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1><i class="fas fa-cut"></i> Gentleman's <span>Edge</span></h1>
            <p>Admin Login</p>
            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// ADMIN LOGOUT
// ============================================================
if ($route === 'admin-logout') {
    session_destroy();
    header('Location: ?route=admin-login');
    exit;
}

// ============================================================
// ADMIN DASHBOARD
// ============================================================
if ($route === 'admin') {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: ?route=admin-login');
        exit;
    }
    
    $pdo = getDB();
    
    // Get statistics
    $bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $unreadCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
    $totalMessages = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    $pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    
    // Get recent data
    $recentBookings = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 15")->fetchAll();
    $recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 15")->fetchAll();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Dashboard - Gentleman's Edge</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: 'Inter', sans-serif; background: #fef8f0; padding: 2rem; }
            .admin-container { max-width: 1400px; margin: 0 auto; }
            .admin-header { background: #1e1a15; color: #f0e5d8; padding: 1rem 2rem; border-radius: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
            .admin-header h1 { font-family: 'Playfair Display', serif; }
            .admin-header h1 span { color: #d9a13b; }
            .admin-header a { color: #f0e5d8; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; transition: 0.3s; }
            .admin-header a:hover { background: #d9a13b; color: #1e1a15; }
            
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
            .stat-card { background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #f0e5d8; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
            .stat-card h3 { font-size: 2.5rem; color: #d9a13b; }
            .stat-card p { color: #5a4a3a; font-weight: 600; }
            .stat-card i { color: #d9a13b; margin-right: 0.5rem; }
            
            .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
            .admin-section { background: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #f0e5d8; overflow-x: auto; }
            .admin-section h2 { margin-bottom: 1rem; color: #2c241a; }
            .admin-section h2 i { color: #d9a13b; margin-right: 0.5rem; }
            
            table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
            th { text-align: left; padding: 0.75rem; border-bottom: 2px solid #f0e5d8; color: #5a4a3a; }
            td { padding: 0.75rem; border-bottom: 1px solid #f0e5d8; }
            
            .badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.8rem; font-weight: 600; display: inline-block; }
            .pending { background: #fff3cd; color: #856404; }
            .confirmed { background: #cce5ff; color: #004085; }
            .completed { background: #d4edda; color: #155724; }
            .cancelled { background: #f8d7da; color: #721c24; }
            .unread { background: #f8d7da; color: #721c24; }
            .read { background: #d4edda; color: #155724; }
            .replied { background: #cce5ff; color: #004085; }
            
            .empty-msg { color: #8a7a6a; text-align: center; padding: 2rem; }
            .admin-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
            .btn-sm { padding: 0.25rem 0.75rem; border: none; border-radius: 0.25rem; cursor: pointer; font-size: 0.8rem; }
            .btn-edit { background: #d9a13b; color: white; }
            .btn-delete { background: #dc3545; color: white; }
            
            @media (max-width: 768px) {
                .admin-grid { grid-template-columns: 1fr; }
                .stats-grid { grid-template-columns: 1fr 1fr; }
            }
        </style>
    </head>
    <body>
        <div class="admin-container">
            <div class="admin-header">
                <h1><i class="fas fa-cut"></i> Gentleman's <span>Edge</span> Admin</h1>
                <div>
                    <span style="margin-right: 1rem;">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                    <a href="?route=admin-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?= $bookingCount ?></h3>
                    <p><i class="fas fa-calendar-check"></i> Total Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?= $pendingBookings ?></h3>
                    <p><i class="fas fa-clock"></i> Pending Bookings</p>
                </div>
                <div class="stat-card">
                    <h3><?= $unreadCount ?></h3>
                    <p><i class="fas fa-envelope"></i> Unread Messages</p>
                </div>
                <div class="stat-card">
                    <h3><?= $totalMessages ?></h3>
                    <p><i class="fas fa-comments"></i> Total Messages</p>
                </div>
            </div>
            
            <div class="admin-grid">
                <div class="admin-section">
                    <h2><i class="fas fa-calendar-check"></i> Recent Bookings</h2>
                    <?php if (empty($recentBookings)): ?>
                        <p class="empty-msg">No bookings yet</p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['full_name']) ?></td>
                                <td><?= htmlspecialchars(substr($b['service'], 0, 20)) ?>...</td>
                                <td><?= $b['booking_date'] ?></td>
                                <td><?= $b['time_slot'] ?></td>
                                <td><span class="badge <?= $b['status'] ?>"><?= $b['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
                
                <div class="admin-section">
                    <h2><i class="fas fa-envelope"></i> Recent Messages</h2>
                    <?php if (empty($recentMessages)): ?>
                        <p class="empty-msg">No messages yet</p>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMessages as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['name']) ?></td>
                                <td><?= htmlspecialchars($m['subject'] ?: 'General') ?></td>
                                <td><?= date('M d', strtotime($m['created_at'])) ?></td>
                                <td><span class="badge <?= $m['status'] ?>"><?= $m['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================
// FRONTEND - DISPLAY WEBSITE
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gentleman's Edge | Barber & Shaving Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== COMPLETE CSS STYLES ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #fef8f0; color: #2c241a; line-height: 1.6; }
        h1, h2, h3, h4, .logo { font-family: 'Playfair Display', serif; }
        a { text-decoration: none; color: inherit; }

        .navbar { background: #1e1a15; color: #f0e5d8; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }
        .logo { font-size: 1.8rem; font-weight: 700; }
        .logo span { color: #d9a13b; }
        .logo i { color: #d9a13b; margin-right: 0.5rem; }
        .nav-links { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-links a { color: #f0e5d8; font-weight: 500; padding: 0.5rem 0; transition: 0.3s; border-bottom: 2px solid transparent; }
        .nav-links a:hover, .nav-links a.active { color: #e6b45e; border-bottom-color: #e6b45e; }
        .nav-links a i { margin-right: 0.5rem; }
        .btn-nav { background: #d9a13b; color: #1e1a15; padding: 0.6rem 1.5rem; border-radius: 30px; font-weight: 600; transition: 0.3s; }
        .btn-nav:hover { background: #c2862a; transform: scale(1.05); }

        .btn { display: inline-block; padding: 0.9rem 2.2rem; border-radius: 40px; font-weight: 600; transition: 0.3s; border: none; cursor: pointer; font-family: 'Inter', sans-serif; }
        .btn-primary { background: #d9a13b; color: #1e1a15; }
        .btn-primary:hover { background: #c2862a; transform: translateY(-2px); }
        .btn-submit { background: #1e1a15; color: #f0e5d8; width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 1.5rem; }
        .btn-submit:hover { background: #3a2f24; transform: translateY(-2px); }

        .hero { background: linear-gradient(135deg, #2c241a 0%, #4a3a2e 100%); color: white; padding: 4rem 2rem; margin: 2rem 2rem 0; border-radius: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 2rem; }
        .hero-content { flex: 1; min-width: 280px; }
        .hero-content h1 { font-size: 3.5rem; line-height: 1.2; margin-bottom: 1rem; }
        .hero-content p { font-size: 1.1rem; color: #e2cfb3; max-width: 500px; margin-bottom: 1.5rem; }
        .hero-icon { font-size: 6rem; color: #d9a13b; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

        .services { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .services h2 { text-align: center; font-size: 2.5rem; margin-bottom: 2rem; }
        .services h2 i { color: #d9a13b; margin-right: 0.8rem; }
        .service-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }
        .service-card { background: white; border-radius: 1.5rem; padding: 2rem 1.5rem; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f0e5d8; transition: 0.3s; }
        .service-card:hover { transform: translateY(-8px); }
        .service-card i { font-size: 3rem; color: #d9a13b; margin-bottom: 1rem; }
        .service-card h3 { font-size: 1.4rem; margin-bottom: 0.5rem; }
        .service-card p { color: #5a4a3a; margin-bottom: 1rem; }
        .price { font-size: 1.5rem; font-weight: 700; color: #d9a13b; }
        .price span { font-size: 0.9rem; font-weight: 400; color: #8a7a6a; }

        .booking-section { padding: 3rem 2rem; max-width: 800px; margin: 0 auto; }
        .booking-card { background: white; border-radius: 2rem; padding: 2.5rem; box-shadow: 0 15px 40px rgba(0,0,0,0.08); border: 1px solid #f0e5d8; }
        .booking-card h2 { font-size: 2rem; margin-bottom: 0.5rem; }
        .booking-card h2 i { color: #d9a13b; margin-right: 0.8rem; }
        .booking-card > p { color: #5a4a3a; margin-bottom: 1.5rem; }

        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.4rem; }
        .form-group label i { color: #d9a13b; margin-right: 0.5rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.8rem 1rem; border: 2px solid #e8ddd0; border-radius: 1rem; background: #fefaf5; font-family: 'Inter', sans-serif; font-size: 1rem; transition: 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #d9a13b; box-shadow: 0 0 0 4px rgba(217, 161, 59, 0.1); }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        #bookingStatusMsg, #contactStatusMsg { margin-top: 1rem; padding: 0.8rem; border-radius: 1rem; font-weight: 500; }
        .msg-success { background: #e8f5e8; color: #1a6e1a; border-left: 4px solid #2b7a2b; }
        .msg-error { background: #fde8e8; color: #a13333; border-left: 4px solid #b33; }

        .testimonials { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .testimonials h2 { text-align: center; font-size: 2.5rem; margin-bottom: 2rem; }
        .testimonials h2 i { color: #d9a13b; margin-right: 0.8rem; }
        .testimonial-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }
        .testimonial-card { background: white; padding: 2rem; border-radius: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f0e5d8; }
        .testimonial-card p { font-style: italic; font-size: 1.05rem; margin-bottom: 1rem; }
        .testimonial-card .client { font-weight: 600; color: #d9a13b; }

        .gallery-section { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .gallery-section h2 { text-align: center; font-size: 2.5rem; margin-bottom: 0.5rem; }
        .gallery-section h2 i { color: #d9a13b; margin-right: 0.8rem; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }

        .flip-card { background-color: transparent; height: 340px; perspective: 1000px; cursor: pointer; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; transition: transform 0.6s; transform-style: preserve-3d; }
        .flip-card:hover .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 1.5rem; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .flip-card-front { background: white; border: 1px solid #f0e5d8; }
        .flip-card-front img { width: 100%; height: 250px; object-fit: cover; display: block; }
        .card-caption { padding: 1rem; text-align: center; background: white; }
        .card-caption h3 { font-size: 1.1rem; }
        .flip-card-back { background: #1e1a15; color: #f0e5d8; transform: rotateY(180deg); padding: 1.5rem; display: flex; align-items: center; justify-content: center; }
        .card-back-content { text-align: center; }
        .card-back-content i { font-size: 3rem; color: #d9a13b; margin-bottom: 1rem; display: block; }
        .card-back-content h3 { font-size: 1.4rem; margin-bottom: 0.5rem; color: #e6b45e; }
        .card-back-content p { color: #cfc3b4; }

        .contact-section { padding: 3rem 2rem; max-width: 1200px; margin: 0 auto; }
        .contact-section h2 { text-align: center; font-size: 2.5rem; margin-bottom: 0.5rem; }
        .contact-section h2 i { color: #d9a13b; margin-right: 0.8rem; }
        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .contact-info-card, .contact-form-card { background: white; padding: 2rem; border-radius: 1.5rem; border: 1px solid #f0e5d8; }
        .contact-info-card h3 i, .contact-form-card h3 i { color: #d9a13b; margin-right: 0.8rem; }
        .contact-info-card p { margin-bottom: 0.8rem; }
        .contact-info-card p i { color: #d9a13b; width: 1.5rem; margin-right: 0.3rem; }
        .map-placeholder { background: #f0e5d8; padding: 1rem; border-radius: 1rem; text-align: center; margin-top: 1.5rem; }

        .footer { background: #1e1a15; color: #cfc3b4; margin-top: 3rem; }
        .footer-content { max-width: 1200px; margin: 0 auto; padding: 3rem 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; }
        .footer-section h3, .footer-section h4 { color: #f0e5d8; margin-bottom: 1rem; }