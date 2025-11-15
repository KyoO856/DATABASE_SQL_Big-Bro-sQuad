<?php
session_start();

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['userid'])) {
    header("Location: log in.php");
    exit;
}

include_once("config/Database.php");
include_once("class/UserLogin.php");

$connectDB = new Database();
$db = $connectDB->getConnection();

$user = new UserLogin($db);
$userData = $user->userData($_SESSION['userid']);

if (!$userData) {
    header("Location: log in.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Big Bro sQuad.com</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/CSS home.css">
    <script src="js/home.js" defer></script>
</head>

<body>

    <?php include_once("nav.php"); ?>

    <div class="container">
        

        <div id="productlist" class="product">

        </div>
    </div>

    <div class="modal" style="display: none;">
        <div class="modal-bg"></div>
        <div class="modal-page">
            <h2>Product Detail</h2><br>
            <div class="modaldesc-content">
                <img class="modaldesc-img" src="productData.img" alt="Product">
                <div class="modaldesc-detail">
                    <p style="font-size: 1.5vw;">Product name</p>
                    <p style="font-size: 1.2vw;">500 THB</p><br>
                    <p style="color: gray;">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum impedit, quisquam quos consequuntur adipisci placeat doloremque illo dolores suscipit facilis. Cumque, similique esse. Impedit reprehenderit, quisquam laboriosam facere voluptatibus facilis?</p><br>
                    <div class="btn-control">
                        <button class="btn btn-close">
                            Close
                        </button>
                        <button class="btn btn-buy">
                            Add to cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับตะกร้าสินค้า -->
    <div class="modal" style="display: none;">
        <div class="modal-bg"></div>
        <div class="modal-page">
            <h2>My Cart</h2><br>
            <div class="cartlist">
                <!-- Cart items will be loaded here by JavaScript -->
            </div>
            <div class="btn-control">
                <button class="btn btn-cancel">
                    Cancel
                </button>
                <button class="btn btn-buy btn-Buy">
                    Buy
                </button>
            </div>
        </div>
    </div>

</body>

</html>