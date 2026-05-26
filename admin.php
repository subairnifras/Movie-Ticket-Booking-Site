<?php
session_start();
include 'db.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Admin Operations
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // 1. Cancel Booking
    if ($action === 'cancel_booking' && isset($_GET['id'])) {
        $booking_id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = "Booking #$booking_id cancelled successfully.";
        } else {
            $error = "Failed to cancel booking #$booking_id.";
        }
        $stmt->close();
    }
    
    // 2. Delete Booking
    if ($action === 'delete_booking' && isset($_GET['id'])) {
        $booking_id = intval($_GET['id']);
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = "Booking #$booking_id deleted from database.";
        } else {
            $error = "Failed to delete booking #$booking_id.";
        }
        $stmt->close();
    }
    
    // 3. Toggle Role (Promote/Demote)
    if ($action === 'toggle_role' && isset($_GET['id']) && isset($_GET['current_role'])) {
        $target_user_id = intval($_GET['id']);
        $current_role = $_GET['current_role'];
        
        if ($target_user_id === $admin_id) {
            $error = "You cannot change your own role.";
        } else {
            $new_role = ($current_role === 'admin') ? 'user' : 'admin';
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $target_user_id);
            if ($stmt->execute()) {
                $message = "User role updated to '$new_role'.";
            } else {
                $error = "Failed to update user role.";
            }
            $stmt->close();
        }
    }
    
    // 4. Delete User
    if ($action === 'delete_user' && isset($_GET['id'])) {
        $target_user_id = intval($_GET['id']);
        if ($target_user_id === $admin_id) {
            $error = "You cannot delete your own account.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            if ($stmt->execute()) {
                $message = "User deleted successfully.";
            } else {
                $error = "Failed to delete user.";
            }
            $stmt->close();
        }
    }
    
    // Redirect to clear GET parameters from URL
    header("Location: admin.php?msg=" . urlencode($message) . "&err=" . urlencode($error));
    exit;
}

if (isset($_GET['msg'])) $message = $_GET['msg'];
if (isset($_GET['err'])) $error = $_GET['err'];

// Fetch Analytics Summary
$total_bookings_res = $conn->query("SELECT COUNT(*) FROM bookings");
$total_bookings = $total_bookings_res ? $total_bookings_res->fetch_row()[0] : 0;

$total_earnings_res = $conn->query("SELECT SUM(total_amount) FROM bookings WHERE status = 'paid'");
$total_earnings = 0;
if ($total_earnings_res) {
    $row = $total_earnings_res->fetch_row();
    $total_earnings = $row[0] ? floatval($row[0]) : 0;
}

$total_users_res = $conn->query("SELECT COUNT(*) FROM users");
$total_users = $total_users_res ? $total_users_res->fetch_row()[0] : 0;

// Fetch Bookings Data
$bookings_query = "SELECT b.*, u.name as user_name, u.email as user_email FROM bookings b LEFT JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC";
$bookings_res = $conn->query($bookings_query);

// Fetch Users Data
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_res = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Movie Booking Site</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <style>
        :root {
            --bg-color: #1a1a24;
            --card-bg: #22222f;
            --primary-color: #e70634;
            --text-color: #ffffff;
            --text-muted: #8a8a9e;
            --success-color: #2ec4b6;
            --warning-color: #ff9f1c;
            --danger-color: #e71d36;
            --border-color: #2f2f3f;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Style */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 4%;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            text-decoration: none;
            gap: 8px;
        }

        .logo i {
            color: var(--primary-color);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 8px 18px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--text-color);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .btn-outline:hover {
            background-color: var(--border-color);
        }

        /* Container */
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            width: 92%;
            flex: 1;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.4s ease;
        }

        .alert-success {
            background-color: rgba(46, 196, 182, 0.15);
            border-left: 5px solid var(--success-color);
            color: #d1f7f4;
        }

        .alert-error {
            background-color: rgba(231, 29, 54, 0.15);
            border-left: 5px solid var(--danger-color);
            color: #ffd6da;
        }

        /* Analytics Grid */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 25px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card-icon {
            font-size: 36px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-1 { background-color: rgba(231, 6, 52, 0.1); color: var(--primary-color); }
        .card-2 { background-color: rgba(46, 196, 182, 0.1); color: var(--success-color); }
        .card-3 { background-color: rgba(255, 159, 28, 0.1); color: var(--warning-color); }

        .card-content h3 {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-content p {
            font-size: 28px;
            font-weight: 700;
        }

        /* Tabs Navigation */
        .tabs-nav {
            display: flex;
            gap: 15px;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 25px;
        }

        .tab-btn {
            padding: 12px 20px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
        }

        .tab-btn.active {
            color: var(--text-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* Panels */
        .panel {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .panel.active {
            display: block;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: rgba(255, 255, 255, 0.02);
            font-weight: 600;
            font-size: 14px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(255, 255, 255, 0.01);
        }

        /* Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success { background-color: rgba(46, 196, 182, 0.15); color: var(--success-color); }
        .badge-warning { background-color: rgba(255, 159, 28, 0.15); color: var(--warning-color); }
        .badge-danger { background-color: rgba(231, 29, 54, 0.15); color: var(--danger-color); }
        .badge-primary { background-color: rgba(231, 6, 52, 0.15); color: var(--primary-color); }
        .badge-secondary { background-color: rgba(138, 138, 158, 0.15); color: var(--text-muted); }

        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.2s;
            margin-right: 5px;
            border: none;
            cursor: pointer;
        }

        .btn-action-cancel {
            background-color: rgba(255, 159, 28, 0.15);
            color: var(--warning-color);
        }

        .btn-action-cancel:hover {
            background-color: var(--warning-color);
            color: #fff;
        }

        .btn-action-delete {
            background-color: rgba(231, 29, 54, 0.15);
            color: var(--danger-color);
        }

        .btn-action-delete:hover {
            background-color: var(--danger-color);
            color: #fff;
        }

        .btn-action-toggle {
            background-color: rgba(46, 196, 182, 0.15);
            color: var(--success-color);
        }

        .btn-action-toggle:hover {
            background-color: var(--success-color);
            color: #fff;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .tabs-nav {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">
            <i class='bx bxs-movie'></i> Movies Admin
        </a>
        <div class="header-actions">
            <span style="font-size: 14px; color: var(--text-muted);">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
            </span>
            <a href="index.php" class="btn btn-outline"><i class='bx bx-home-alt'></i> View Site</a>
            <a href="logout.php" class="btn btn-primary"><i class='bx bx-log-out'></i> Sign Out</a>
        </div>
    </header>

    <div class="admin-container">
        
        <!-- Alerts -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <i class='bx bx-check-circle' style="font-size: 20px;"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class='bx bx-error-circle' style="font-size: 20px;"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Analytics Summary -->
        <div class="analytics-grid">
            <div class="card">
                <div class="card-icon card-1">
                    <i class='bx bx-film'></i>
                </div>
                <div class="card-content">
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon card-2">
                    <i class='bx bx-money'></i>
                </div>
                <div class="card-content">
                    <h3>Total Earnings</h3>
                    <p>LKR. <?php echo number_format($total_earnings, 2); ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon card-3">
                    <i class='bx bx-group'></i>
                </div>
                <div class="card-content">
                    <h3>Registered Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab(event, 'bookings-panel')">Bookings Management</button>
            <button class="tab-btn" onclick="switchTab(event, 'users-panel')">User Accounts</button>
        </div>

        <!-- Bookings Panel -->
        <div id="bookings-panel" class="panel active">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 15px;">Recent Ticket Sales</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Date / Time</th>
                            <th>Seats Reserved</th>
                            <th>Price Paid</th>
                            <th>Status</th>
                            <th>Reserved On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings_res && $bookings_res->num_rows > 0): ?>
                            <?php while ($booking = $bookings_res->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($booking['user_name'] ?? 'Guest'); ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($booking['user_email'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($booking['show_date'])); ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($booking['show_time']); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-family: monospace; letter-spacing: 0.5px;"><?php echo htmlspecialchars($booking['seats']); ?></span>
                                    </td>
                                    <td><strong>LKR. <?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <?php if ($booking['status'] === 'paid'): ?>
                                            <span class="badge badge-success">Paid</span>
                                        <?php elseif ($booking['status'] === 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-size: 12px; color: var(--text-muted);"><?php echo date('Y-m-d H:i', strtotime($booking['created_at'])); ?></span></td>
                                    <td>
                                        <?php if ($booking['status'] !== 'cancelled'): ?>
                                            <a href="admin.php?action=cancel_booking&id=<?php echo $booking['id']; ?>" class="btn-action btn-action-cancel" title="Cancel Booking" onclick="return confirm('Are you sure you want to cancel booking #<?php echo $booking['id']; ?>?')">
                                                <i class='bx bx-x'></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="admin.php?action=delete_booking&id=<?php echo $booking['id']; ?>" class="btn-action btn-action-delete" title="Delete Booking" onclick="return confirm('Are you sure you want to permanently delete booking #<?php echo $booking['id']; ?> from the database?')">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Panel -->
        <div id="users-panel" class="panel">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 15px;">Registered Member Accounts</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Account Role</th>
                            <th>Created On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users_res && $users_res->num_rows > 0): ?>
                            <?php while ($user = $users_res->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $user['id']; ?></strong></td>
                                    <td><span style="font-weight: 500;"><?php echo htmlspecialchars($user['name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge badge-primary">Administrator</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Member</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="color: var(--text-muted);"><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></span></td>
                                    <td>
                                        <?php if ($user['id'] !== $admin_id): ?>
                                            <a href="admin.php?action=toggle_role&id=<?php echo $user['id']; ?>&current_role=<?php echo $user['role']; ?>" class="btn-action btn-action-toggle" title="<?php echo ($user['role'] === 'admin') ? 'Demote to user' : 'Promote to admin'; ?>" onclick="return confirm('Change role for <?php echo htmlspecialchars($user['name']); ?> to <?php echo ($user['role'] === 'admin') ? 'user' : 'admin'; ?>?')">
                                                <i class='bx bx-refresh'></i>
                                            </a>
                                            <a href="admin.php?action=delete_user&id=<?php echo $user['id']; ?>" class="btn-action btn-action-delete" title="Delete User" onclick="return confirm('Are you sure you want to permanently delete user <?php echo htmlspecialchars($user['name']); ?>? This cannot be undone!')">
                                                <i class='bx bx-trash'></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Self (Locked)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function switchTab(evt, tabId) {
            // Hide all panels
            const panels = document.querySelectorAll('.panel');
            panels.forEach(panel => panel.classList.remove('active'));

            // Deactivate all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => btn.classList.remove('active'));

            // Show target panel and activate button
            document.getElementById(tabId).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        // Auto-dismiss alert boxes after 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
