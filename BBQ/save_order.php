<?php
session_start();

// Log everything to a file
$logFile = 'order_debug.log';
function logDebug($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logDebug("=== NEW REQUEST ===");
logDebug("Method: " . $_SERVER['REQUEST_METHOD']);
logDebug("Session userid: " . (isset($_SESSION['userid']) ? $_SESSION['userid'] : 'NOT SET'));

header('Content-Type: application/json');

// Step 1: Check session
if (!isset($_SESSION['userid'])) {
    $response = ['success' => false, 'message' => 'User not logged in', 'step' => 1];
    logDebug("FAIL Step 1: No session");
    echo json_encode($response);
    exit;
}
logDebug("PASS Step 1: Session OK");

// Step 2: Check POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ['success' => false, 'message' => 'Must use POST method', 'step' => 2];
    logDebug("FAIL Step 2: Not POST");
    echo json_encode($response);
    exit;
}
logDebug("PASS Step 2: POST method OK");

// Step 3: Get and parse JSON
$json = file_get_contents('php://input');
logDebug("Raw JSON received: " . $json);

if (empty($json)) {
    $response = ['success' => false, 'message' => 'No data received', 'step' => 3];
    logDebug("FAIL Step 3: Empty JSON");
    echo json_encode($response);
    exit;
}

$data = json_decode($json, true);
logDebug("Parsed data: " . print_r($data, true));

if (json_last_error() !== JSON_ERROR_NONE) {
    $response = ['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg(), 'step' => 3];
    logDebug("FAIL Step 3: JSON parse error");
    echo json_encode($response);
    exit;
}
logDebug("PASS Step 3: JSON parsed OK");

// Step 4: Validate data structure
if (!isset($data['items']) || !isset($data['total'])) {
    $response = [
        'success' => false, 
        'message' => 'Missing items or total', 
        'step' => 4,
        'has_items' => isset($data['items']),
        'has_total' => isset($data['total'])
    ];
    logDebug("FAIL Step 4: Missing data");
    echo json_encode($response);
    exit;
}
logDebug("PASS Step 4: Data structure OK");

// Step 5: Validate data values
$items = $data['items'];
$total = floatval($data['total']);
$userId = intval($_SESSION['userid']);

if (empty($items)) {
    $response = ['success' => false, 'message' => 'Cart is empty', 'step' => 5];
    logDebug("FAIL Step 5: Empty cart");
    echo json_encode($response);
    exit;
}

if ($total <= 0) {
    $response = ['success' => false, 'message' => 'Invalid total amount', 'step' => 5];
    logDebug("FAIL Step 5: Invalid total");
    echo json_encode($response);
    exit;
}
logDebug("PASS Step 5: Data values OK (items: " . count($items) . ", total: $total, user: $userId)");

// Step 6: Include Database
if (!file_exists("config/Database.php")) {
    $response = ['success' => false, 'message' => 'Database.php not found', 'step' => 6];
    logDebug("FAIL Step 6: Database.php not found");
    echo json_encode($response);
    exit;
}

try {
    include_once("config/Database.php");
    logDebug("PASS Step 6: Database.php included");
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error including Database.php: ' . $e->getMessage(), 'step' => 6];
    logDebug("FAIL Step 6: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

// Step 7: Connect to database
try {
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    
    if (!$db) {
        $response = ['success' => false, 'message' => 'Database connection is null', 'step' => 7];
        logDebug("FAIL Step 7: DB connection null");
        echo json_encode($response);
        exit;
    }
    logDebug("PASS Step 7: Database connected");
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Database connection error: ' . $e->getMessage(), 'step' => 7];
    logDebug("FAIL Step 7: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

// Step 8: Check if Order.php exists
if (!file_exists("class/Order.php")) {
    $response = ['success' => false, 'message' => 'Order.php not found', 'step' => 8];
    logDebug("FAIL Step 8: Order.php not found");
    echo json_encode($response);
    exit;
}

try {
    include_once("class/Order.php");
    logDebug("PASS Step 8: Order.php included");
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error including Order.php: ' . $e->getMessage(), 'step' => 8];
    logDebug("FAIL Step 8: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

// Step 9: Create Order instance
try {
    $order = new Order($db);
    logDebug("PASS Step 9: Order object created");
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error creating Order: ' . $e->getMessage(), 'step' => 9];
    logDebug("FAIL Step 9: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

// Step 10: Create the order
try {
    logDebug("Attempting to create order...");
    $orderId = $order->createOrder($userId, $items, $total);
    
    if ($orderId) {
        $response = [
            'success' => true,
            'message' => 'Order saved successfully',
            'order_id' => $orderId,
            'step' => 10
        ];
        logDebug("SUCCESS! Order ID: $orderId");
        echo json_encode($response);
    } else {
        $response = [
            'success' => false,
            'message' => 'createOrder returned false',
            'step' => 10
        ];
        logDebug("FAIL Step 10: createOrder returned false");
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Exception in createOrder: ' . $e->getMessage(),
        'step' => 10,
        'trace' => $e->getTraceAsString()
    ];
    logDebug("FAIL Step 10: Exception - " . $e->getMessage());
    logDebug("Stack trace: " . $e->getTraceAsString());
    echo json_encode($response);
}

logDebug("=== END REQUEST ===\n");
?>