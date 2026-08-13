<?php
/**
 * Gentleman's Edge Barbershop
 * Admin Dashboard - View Bookings and Messages
 */

require_once 'db_config.php';

$conn = getDBConnection();

// ============================================================
// GET ALL BOOKINGS
// ============================================================
$bookings = [];
$bookingsSql = "SELECT id, full_name, email, phone, service, booking_date, time_slot, notes, status, created_at 
                FROM bookings 
                ORDER BY booking_date DESC";
$bookingsResult = $conn->query($bookingsSql);

if ($bookingsResult && $bookingsResult->num_rows > 0) {
    while ($row = $bookingsResult->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// ============================================================
// GET ALL CONTACT MESSAGES
// ============================================================
$messages = [];
$messagesSql = "SELECT id, name, email, subject, message, status, created_at 
                FROM contact_messages 
                ORDER BY created_at DESC";
$messagesResult = $conn->query($messagesSql);

if ($messagesResult && $messagesResult->num_rows > 0) {
    while ($row = $messagesResult->fetch_assoc()) {
        $messages[] = $row;
    }
}

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gentleman's Edge | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .admin-header {
            background: #1e1a15;
            color: white;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .admin-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .admin-header p {
            color: #d9a13b;
            font-size: 1.1rem;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #d9a13b;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #666;
            font-size: 1rem;
        }

        .section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .section h2 {
            color: #1e1a15;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section h2 i {
            color: #d9a13b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        table thead {
            background: #f0e5d8;
        }

        table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #1e1a15;
            border-bottom: 2px solid #d9a13b;
        }

        table td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        table tbody tr:hover {
            background: #fefaf5;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #cfe2ff;
            color: #084298;
        }

        .status-unread {
            background: #fff3cd;
            color: #856404;
        }

        .status-read {
            background: #d4edda;
            color: #155724;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            color: #d9a13b;
            margin-bottom: 1rem;
            display: block;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.9rem;
            }

            table th, table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1><i class="fas fa-cut"></i> Gentleman's Edge Admin Dashboard</h1>
        <p>Manage Bookings & Customer Messages</p>
    </div>

    <div class="container">

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3><?php echo count($bookings); ?></h3>
                <p><i class="fas fa-calendar"></i> Total Bookings</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($messages); ?></h3>
                <p><i class="fas fa-envelope"></i> Contact Messages</p>
            </div>
        </div>

        <!-- Bookings Section -->
        <div class="section">
            <h2><i class="fas fa-calendar-check"></i> Recent Bookings</h2>
            
            <?php if (count($bookings) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service</th>
                            <th>Booking Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($booking['id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td><?php echo htmlspecialchars($booking['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($booking['service']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($booking['booking_date']))); ?></td>
                                <td><?php echo htmlspecialchars($booking['time_slot']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No bookings yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contact Messages Section -->
        <div class="section">
            <h2><i class="fas fa-envelope"></i> Contact Messages</h2>
            
            <?php if (count($messages) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($msg['id']); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars($msg['subject'] ?: 'General Inquiry'); ?></td>
                                <td><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($msg['created_at']))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No messages yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
