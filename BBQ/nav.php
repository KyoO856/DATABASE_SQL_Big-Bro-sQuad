 <nav>
        <div class="nav-container">
            <a href="home.php">
                <img src="img/dbell2.png" class="logonav" alt="Big Bro sQuad Logo">
            </a>

            <div class="nav-profile">
                <a href="account.php" style="color: white; margin-right: 20px; text-decoration: none;">
                    <i class="fas fa-user-circle"></i>
                </a>
                <p class="nav-profile-name"><?php echo htmlspecialchars($userData['name']); ?></p>
                <div class="nav-profile-cart" style="cursor: pointer; margin-left: 20px;">
                    <i class="fas fa-cart-shopping"></i>
                    <div class="cartcount" style="font-size: 0.8vw;">
                        0
                    </div>
                </div>
            </div>
        </div>
    </nav>
