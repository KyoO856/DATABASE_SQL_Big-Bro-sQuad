<?php
session_start();

include_once("config/Database.php");
include_once("class/UserLogin.php");
include_once("class/Utils.php");

$connectDB = new Database();
$db = $connectDB->getConnection();

$user = new UserLogin($db);
$bs = new Bootstrap();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Debug: แสดงข้อมูลที่ส่งมา
    echo "<!-- Email: " . htmlspecialchars($_POST['email']) . " -->";
    $user->setEmail($_POST['email']);
    $user->setPassword($_POST['password']);

    if ($user->emailNotExists()) {
        $bs->displayAlert("Email is not exists", "danger");
        header("Location: home.php");
        exit();
    } else {
        // ถ้า email มีอยู่ ให้ตรวจสอบ password
        $result = $user->verifyPassword();

        // Debug
        echo "<!-- Verify result: " . ($result ? "true" : "false") . " -->";

        if ($result) {
            // Login สำเร็จ - redirect ไปหน้า home
            header("Location: home.php");
            exit();
        } else {
            $bs->displayAlert("Password do not match", "danger");
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Big Bro sQuad.com</title>
    <link rel="stylesheet" type="text/css" href="css/CSS log in.css">
</head>

<body>
    <div class="flex-container">
        <img src="img/dbell.png" alt="DUMBBELL">
        <h1>Log in</h1>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="email"
                name="email"
                placeholder="E-mail"
                required
                autofocus><br>
            <input type="password"
                name="password"
                placeholder="Password"
                required><br>
            <button type="submit" name="login">Log in</button>
        </form>
        <p>Don't have an account?
            <a href="sign up.php">sign up</a>
        </p>
    </div>
</body>

</html>