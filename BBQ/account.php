<?php
session_start();

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['userid'])) {
    header("Location: log in.php");
    exit;
}

include_once("config/Database.php");
include_once("class/UserLogin.php");
include_once("class/Utils.php");
include_once("class/Order.php");

$connectDB = new Database();
$db = $connectDB->getConnection();

$user = new UserLogin($db);
$bs = new Bootstrap();
$userData = $user->userData($_SESSION['userid']);

// Get user's recent orders (limit to 5 most recent)
$order = new Order($db);
$recentOrders = $order->getUserOrders($_SESSION['userid']);
$recentOrders = array_slice($recentOrders, 0, 5); // Get only 5 most recent

if (!$userData) {
    header("Location: log in.php");
    exit;
}

// จัดการการอัปเดตข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $userId = $_SESSION['userid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];

    // ตรวจสอบว่ามีการเปลี่ยน email หรือไม่
    if ($email !== $userData['email']) {
        // ตรวจสอบว่า email ใหม่ซ้ำกับคนอื่นไหม
        $checkQuery = "SELECT id FROM user WHERE email = :email AND id != :userid LIMIT 1";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":email", $email);
        $checkStmt->bindParam(":userid", $userId);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $bs->displayAlert("This email is already in use by another account", "danger");
            $userData = $user->userData($_SESSION['userid']); // โหลดข้อมูลเดิม
        } else {
            // อัปเดตข้อมูล
            $updateQuery = "UPDATE user SET name = :name, email = :email, phone = :phone, address = :address, birthday = :birthday WHERE id = :userid";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(":name", $name);
            $updateStmt->bindParam(":email", $email);
            $updateStmt->bindParam(":phone", $phone);
            $updateStmt->bindParam(":address", $address);
            $updateStmt->bindParam(":birthday", $birthday);
            $updateStmt->bindParam(":userid", $userId);

            if ($updateStmt->execute()) {
                $bs->displayAlert("Profile updated successfully!", "success");
                $userData = $user->userData($_SESSION['userid']); // โหลดข้อมูลใหม่
            } else {
                $bs->displayAlert("Failed to update profile. Please try again.", "danger");
            }
        }
    } else {
        // อัปเดตข้อมูลโดยไม่เปลี่ยน email
        $updateQuery = "UPDATE user SET name = :name, phone = :phone, address = :address, birthday = :birthday WHERE id = :userid";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":name", $name);
        $updateStmt->bindParam(":phone", $phone);
        $updateStmt->bindParam(":address", $address);
        $updateStmt->bindParam(":birthday", $birthday);
        $updateStmt->bindParam(":userid", $userId);

        if ($updateStmt->execute()) {
            $bs->displayAlert("Profile updated successfully!", "success");
            $userData = $user->userData($_SESSION['userid']); // โหลดข้อมูลใหม่
        } else {
            $bs->displayAlert("Failed to update profile. Please try again.", "danger");
        }
    }
}

// จัดการการเปลี่ยนรหัสผ่าน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePassword'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // ดึงรหัสผ่านปัจจุบันจากฐานข้อมูล
    $query = "SELECT password FROM user WHERE id = :userid";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":userid", $_SESSION['userid']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบรหัสผ่านปัจจุบัน
    if (!password_verify($currentPassword, $row['password'])) {
        $bs->displayAlert("Current password is incorrect", "danger");
    } elseif ($newPassword !== $confirmNewPassword) {
        $bs->displayAlert("New passwords do not match", "danger");
    } elseif (strlen($newPassword) < 6) {
        $bs->displayAlert("New password must be at least 6 characters long", "danger");
    } else {
        // อัปเดตรหัสผ่าน
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE user SET password = :password WHERE id = :userid";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(":password", $hashedPassword);
        $updateStmt->bindParam(":userid", $_SESSION['userid']);

        if ($updateStmt->execute()) {
            $bs->displayAlert("Password changed successfully!", "success");
        } else {
            $bs->displayAlert("Failed to change password. Please try again.", "danger");
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Account - Big Bro sQuad</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/CSS home.css">
    <link rel="stylesheet" type="text/css" href="css/CSS ACC.css">
    <script src="js/home.js" defer></script>
    <script src="js/ACC.js" defer></script>
</head>

<body>
    <?php include_once("nav.php"); ?>

    <div class="account-container">
        <div class="account-header">
            <h1><i class="fas fa-user-circle"></i> My Account</h1>
            <p>Manage your personal information</p>
        </div>

        <div class="account-content">
            <!-- Profile Information Section -->
            <div class="account-section">
                <h2><i class="fas fa-id-card"></i> Profile Information</h2>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="birthday"><i class="fas fa-birthday-cake"></i> Birthday</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($userData['birthday']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone']); ?>" pattern="[0-9]{10}" required>
                    </div>

                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                        <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($userData['address']); ?></textarea>
                    </div>

                    <button type="submit" name="update" class="btn-update">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password Section -->
            <div class="account-section">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="form-group">
                        <label for="currentPassword"><i class="fas fa-key"></i> Current Password</label>
                        <input type="password" id="currentPassword" name="currentPassword" required>
                    </div>

                    <div class="form-group">
                        <label for="newPassword"><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" id="newPassword" name="newPassword" minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmNewPassword"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
                    </div>

                    <button type="submit" name="changePassword" class="btn-password">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- Order History Section -->
            <div class="account-section order-history-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2><i class="fas fa-history"></i> Recent Orders</h2>
                    <a href="order_history.php" class="view-all-link">
                        View All Orders <i class="fas fa-arrow-right"></i>   <!--/////////////////////////////////////////////-->
                    </a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-cart"></i>
                        <p>No orders yet. Start shopping!</p>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($recentOrders as $orderItem): ?>
                            <div class="order-item-card">
                                <div class="order-item-header">
                                    <div class="order-info">
                                        <span class="order-number">
                                            <i class="fas fa-receipt"></i> Order #<?php echo $orderItem['id']; ?>
                                        </span>
                                        <span class="order-date">
                                            <i class="far fa-calendar"></i> 
                                            <?php echo date('M d, Y', strtotime($orderItem['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="order-amount">
                                        <span class="amount"><?php echo number_format($orderItem['total_amount'], 0); ?> ฿</span>
                                        <span class="status-badge">
                                            <i class="fas fa-check-circle"></i> <?php echo ucfirst($orderItem['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="back-home">
            <a href="home.php"><i class="fas fa-arrow-left"></i> Back to Home</a><br>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </div>

    </div>

   <script src="js/ACC.js"></script>
</body>

</html>