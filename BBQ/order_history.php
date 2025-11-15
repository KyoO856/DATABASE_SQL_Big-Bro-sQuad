<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: log in.php");
    exit;
}

include_once("config/Database.php");
include_once("class/Order.php");
include_once("class/UserLogin.php");

$connectDB = new Database();
$db = $connectDB->getConnection();

$user = new UserLogin($db);
$userData = $user->userData($_SESSION['userid']);

if (!$userData) {
    header("Location: log in.php");
    exit;
}

// Get user orders
$order = new Order($db);
$orders = $order->getUserOrders($_SESSION['userid']);
$stats = $order->getUserOrderStats($_SESSION['userid']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History - BBQ</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/CSS home.css">
    <link rel="stylesheet" type="text/css" href="css/CSS order_history.css">
</head>
<body>
    <?php include_once("nav.php"); ?>

    <div class="history-container">
        <a href="home.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="page-header">
            <h1><i class="fas fa-history"></i> Order History</h1>
            <p>Welcome back, <?php echo htmlspecialchars($userData['name']); ?>!</p>
        </div>

        <?php if ($stats && $stats['total_orders'] > 0): ?>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                    <h3>Total Orders</h3>
                    <p><?php echo $stats['total_orders']; ?></p>
                </div>
                <div class="stat-card money">
                    <div class="icon"><i class="fas fa-coins"></i></div>
                    <h3>Total Spent</h3>
                    <p><?php echo number_format($stats['total_spent'], 0); ?> ฿</p>
                </div>
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-calendar-check"></i></div>
                    <h3>Last Order</h3>
                    <p style="font-size: 1.3rem;"><?php echo date('d M Y', strtotime($stats['last_order_date'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="orders-section">
            <h2><i class="fas fa-list"></i> Your Orders</h2>
            
            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>No orders yet</h3>
                    <p>Start shopping to see your order history!</p>
                    <a href="home.php"><i class="fas fa-shopping-bag"></i> Start Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $orderItem): ?>
                    <div class="order-card" onclick="viewOrderDetails(<?php echo $orderItem['id']; ?>)">
                        <div class="order-header">
                            <div>
                                <div class="order-id">
                                    <i class="fas fa-receipt"></i>
                                    Order #<?php echo $orderItem['id']; ?>
                                </div>
                                <div class="order-date">
                                    <i class="far fa-calendar"></i> 
                                    <?php echo date('F d, Y - H:i', strtotime($orderItem['created_at'])); ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="order-total"><?php echo number_format($orderItem['total_amount'], 0); ?> ฿</div>
                                <span class="order-status">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo ucfirst($orderItem['status']); ?>
                                </span>
                            </div>
                        </div>
                        <button class="view-details-btn" onclick="event.stopPropagation(); viewOrderDetails(<?php echo $orderItem['id']; ?>)">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="orderDetailsContent"></div>
        </div>
    </div>

    <script>
        function viewOrderDetails(orderId) {
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order);
                    } else {
                        alert('Failed to load order details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading order details');
                });
        }

        function displayOrderDetails(order) {
            let itemsHtml = order.items.map(item => `
                <div class="order-item">
                    <div class="order-item-left">
                        <div class="order-item-name">${item.product_name}</div>
                        <div class="order-item-details">
                            ${parseFloat(item.price).toLocaleString()} ฿ × ${item.quantity}
                        </div>
                    </div>
                    <div class="order-item-total">
                        ${parseFloat(item.subtotal).toLocaleString()} ฿
                    </div>
                </div>
            `).join('');

            const orderDate = new Date(order.created_at);
            const formattedDate = orderDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const html = `
                <h2><i class="fas fa-receipt"></i> Order #${order.id}</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    <i class="far fa-clock"></i> Placed on ${formattedDate}
                </p>
                <div class="order-info">
                    <div style="margin-bottom: 10px;">
                        <strong><i class="fas fa-user"></i> Customer:</strong> ${order.user_name}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong><i class="fas fa-envelope"></i> Email:</strong> ${order.user_email}
                    </div>
                    <div>
                        <strong><i class="fas fa-info-circle"></i> Status:</strong> 
                        <span style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 15px;">
                            ${order.status.toUpperCase()}
                        </span>
                    </div>
                </div>
                <h3 style="margin-bottom: 15px; color: #333;">
                    <i class="fas fa-shopping-basket"></i> Order Items
                </h3>
                ${itemsHtml}
                <div class="order-summary">
                    <div class="order-summary-total">
                        <i class="fas fa-receipt"></i> Total: ${parseFloat(order.total_amount).toLocaleString()} ฿
                    </div>
                </div>
            `;

            document.getElementById('orderDetailsContent').innerHTML = html;
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>