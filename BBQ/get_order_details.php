<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID not provided']);
    exit;
}

include_once("config/Database.php");
include_once("class/Order.php");

try {
    $orderId = intval($_GET['order_id']);
    
    // Connect to database
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    
    // Get order details
    $order = new Order($db);
    $orderDetails = $order->getOrderDetails($orderId);
    
    if ($orderDetails) {
        // Verify that the order belongs to the logged-in user
        if ($orderDetails['user_id'] != $_SESSION['userid']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'order' => $orderDetails
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Get order details error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching order details'
    ]);
}
?>