<!DOCTYPE html>
<html>

<head>
    <title>Create your BBQ account</title>
    <link rel="stylesheet" type="text/css" href="css/CSS sign up.css">
</head>

<body>
    <a href="log in.php" class="back-link">← Back</a>

    <div class="container">

        <h1>Create a your account</h1>

        <?php

        include_once("config/Database.php");
        include_once("class/UserRegister.php");
        include_once("class/Utils.php");

        $connectDB = new Database();
        $db = $connectDB->getConnection();

        $user = new UserRegister($db);
        $bs = new Bootstrap();

        $hasError = false;

        if (isset($_POST['signup'])) {
            $hasError = false;
            $user->setFirstName($_POST['firstName']);
            $user->setLastName($_POST['lastName']);
            $user->setEmail($_POST['email']);
            $user->setPassword($_POST['password']);
            $user->setConfirmPassword($_POST['confirmPassword']);
            $user->setBirthday($_POST['birthday']);
            $user->setPhone($_POST['phone']);
            $user->setAddress($_POST['address']);

            if (!$user->validatePassword()) {
                $bs->displayAlert("Passwords do not match", "danger");
                $hasError = true;
            }

            if (!$user->checkPasswordLength()) {
                $bs->displayAlert("Password must be at least 6 characters long.", "danger");
                $hasError = true;
            }

            if ($user->checkEmail()) {
                $bs->displayAlert("This email is already exists try another.", "danger");
                $hasError = true;
            }
            if ($user->createUser()) {
                echo '<div class="alert alert-success" role="alert" style="margin-top: 20px;">
            ✅ Sign-up complete! Redirecting to login page... </div>';
                echo '<script>
            setTimeout(function() {
            window.location.href = "log in.php";
            }, 1000);
            </script>';
            } else {
                $bs->displayAlert("Failed to create user. Please try again.", "danger");
            }
        }

        ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <div class="form-group">
                <label for="firstName">First name *</label>
                <input type="text" id="firstName" name="firstName" placeholder="first Name" required autofocus>
            </div>

            <div class="form-group">
                <label for="lastName">Last name *</label>
                <input type="text" id="lastName" name="lastName" placeholder="last Name" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" placeholder="e-mail" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" placeholder="Password (at least 6 characters)" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm password *</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password again" required>
                <div class="error-message" id="passwordError">Password do not match</div>
            </div>

            <div class="form-group">
                <label for="birthday">Birthday *</label>
                <input type="date" id="birthday" name="birthday" lang="en" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone *</label>
                <input type="tel" id="phone" name="phone" placeholder="08x-xxx-xxxx" pattern="[0-9]{10}" required>
                <div class="error-message" id="phoneError">Please enter a 10-digit phone number.</div>
            </div>

            <div class="form-group">
                <label for="address"> Address *</label>
                <textarea id="address" name="address" rows="3" placeholder="" required></textarea>
            </div>
            <button type="submit" name="signup">Submit</button>
        </form>

        <p class="mt-3">Already have an account? Go to <a href="log in.php">Sign In</a></p>


    </div>

    <script src="js/java sign up.js"></script>
</body>

</html>