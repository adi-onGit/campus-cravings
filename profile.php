<?php
session_start();
require_once 'php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $res = mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id = $order_id");
    if (!$res) die("DB Error: " . mysqli_error($conn));
    header("Location: profile.php?success=1");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

if ($user_role === 'admin') {
    // Admin sees all orders with customer details
    $order_query = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    ORDER BY o.order_date DESC";
} else {
    // Normal user sees only their orders
    $order_query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_date DESC";
}

$order_result = mysqli_query($conn, $order_query);
$orders = [];
while ($row = mysqli_fetch_assoc($order_result)) {
    $orders[] = $row;
}

$complaint_query = "SELECT * FROM complaints WHERE user_id = '$user_id' ORDER BY created_at DESC";
$complaint_result = mysqli_query($conn, $complaint_query);
$complaints = [];
while ($row = mysqli_fetch_assoc($complaint_result)) {
    $complaints[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CampusCravings</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <a href="home.php" class="logo-desktop" style="text-decoration: none; color: inherit;">
                🍴 Campus<span>Cravings</span>
            </a>
            <div class="desktop-nav">
                <a href="home.php">Home</a>
                <a href="checkout.php">Cart</a>
                <a href="profile.php" class="active">Account</a>
            </div>
            <a href="profile.php" class="account-btn">
                <img src="assets/pfp/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.jpeg'; ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
        </header>

        <div class="profile-container">
            <div class="profile-header-card">
                <div class="profile-cover"></div>
                <div class="profile-info">
                    <div class="profile-avatar">
                        <img src="assets/pfp/<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.jpeg'; ?>" class="profile-img">
                    </div>
                    <div class="user-meta">
                        <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <?php if (!empty($user['phone'])): ?>
                            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-wrapper">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border-light); padding-bottom: 15px; margin-bottom: 25px;">
                    <div class="section-title" style="margin: 0; padding: 0; border: none;"><i class="fas fa-receipt"></i> Order History</div>
                    <button onclick="window.location.reload();" style="background: transparent; border: 1px solid var(--border-light); padding: 5px 12px; border-radius: 8px; font-size: 0.8rem; color: var(--text-muted); font-weight: 600; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.borderColor='var(--swiggy-orange)'; this.style.color='var(--swiggy-orange)';" onmouseout="this.style.borderColor='var(--border-light)'; this.style.color='var(--text-muted)';"><i class="fas fa-sync-alt" style="margin-right: 5px;"></i> Refresh Status</button>
                </div>
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No orders yet</h3>
                        <p>Looks like you haven't crazed any dishes yet!</p>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-details">
                                    <h4>Order #<?php echo $order['id']; ?></h4>
                                    <p><i class="far fa-calendar-alt"></i> <?php echo date('d M, Y • g:i A', strtotime($order['order_date'])); ?></p>
                                    
                                    <?php if ($user_role === 'admin' && isset($order['customer_name'])): ?>
                                        <div style="margin-top: 15px; padding: 12px; background: #fff; border-radius: 8px; border: 1px solid #ffd0a8; border-left: 4px solid var(--swiggy-orange); font-size: 0.85rem;">
                                            <strong style="color: var(--text-main); display: block; margin-bottom: 5px;"><i class="fas fa-user-circle"></i> Customer Details</strong>
                                            <div style="color: var(--text-muted);">
                                                <div style="margin-bottom: 3px;"><i class="fas fa-user" style="width: 14px; text-align: center;"></i> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div style="margin-bottom: 3px;"><i class="fas fa-envelope" style="width: 14px; text-align: center;"></i> <?php echo htmlspecialchars($order['customer_email']); ?></div>
                                                <?php if (!empty($order['customer_phone'])): ?>
                                                    <div><i class="fas fa-phone" style="width: 14px; text-align: center;"></i> <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="order-amount">
                                    <span class="price">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                    <?php 
                                    // Automatic Progression Logic based on Time
                                    date_default_timezone_set('Asia/Kolkata');
                                    $order_time = strtotime($order['order_date']);
                                    $current_time = time();
                                    
                                    // If there is an extreme DB timezone mismatch, we can fallback to absolute difference. 
                                    // Normally, current_time should be > order_time. 
                                    $diff = $current_time - $order_time;
                                    // If diff is intensely negative due to strict timezone offset, correct it (e.g., 5.5 hrs is 19800 sec)
                                    if ($diff < -1000) {
                                        $diff = abs($diff + 19800); // Attempt baseline correction if PHP and DB are 5.5 hours apart
                                        if ($diff < -1000 || $diff > 86400) $diff = abs($current_time - $order_time); 
                                    } elseif ($diff < 0) {
                                        $diff = 0; // Small drift
                                    }
                                    
                                    $minutes_passed = $diff / 60;
                                    
                                    $status = 'Processing';
                                    if ($minutes_passed >= 2) {
                                        $status = 'Delivered';
                                    } elseif ($minutes_passed >= 1) {
                                        $status = 'On the Way';
                                    }
                                    
                                    // Optionally allow admin to override, but if we do this, auto-progression is so much cooler.
                                    // If db status is explicitly set to Delivered, keep it.
                                    if (isset($order['status']) && $order['status'] === 'Delivered') {
                                        $status = 'Delivered';
                                    }

                                    $statusClass = 'status-default';
                                    if ($status == 'Processing') $statusClass = 'status-pending';
                                    else if ($status == 'On the Way') $statusClass = 'status-warning';
                                    else if ($status == 'Delivered') $statusClass = 'status-delivered';
                                    ?>
                                    <span class="status <?php echo $statusClass; ?>" style="display:inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; <?php if($statusClass=='status-pending') echo 'background:#fff8c5;color:#9a6700;'; else if($statusClass=='status-warning') echo 'background:#fff3cd;color:#856404;'; else if($statusClass=='status-delivered') echo 'background:#e6ffec;color:#1a7f37;'; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section-wrapper">
                <div class="section-title"><i class="fas fa-headset"></i> Support & Complaints</div>
                <div class="complaint-form">
                    <form action="php/submit_complaint.php" method="POST">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" required placeholder="What is this regarding?">
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" rows="4" required placeholder="Describe your issue or feedback in detail..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; border: none; cursor: pointer; padding: 18px; font-size: 1.1rem; font-weight: 800; border-radius: 10px; box-shadow: 0 8px 20px rgba(252, 128, 25, 0.3);">Submit Complaint</button>
                    </form>
                </div>
            </div>

            <div class="logout-btn-wrapper">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout Securely
                </a>
            </div>
        </div>

        <nav class="bottom-nav">
            <a href="home.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>HOME</span>
            </a>
            <a href="checkout.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>CART</span>
            </a>
            <a href="profile.php" class="nav-item active">
                <i class="fas fa-user"></i>
                <span>PROFILE</span>
            </a>
        </nav>
    </div>
</body>
</html>
