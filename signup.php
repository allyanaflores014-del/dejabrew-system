<?php
// Import: shared functions (also loads config + starts the session)
require_once 'includes/functions.php';

// Variables used by the form and its messages
$username = "";
$email = "";
$usernameError = "";
$emailError = "";
$passwordError = "";
$confirmError = "";
$success = "";

// $_SERVER + $_POST: runs only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm = $_POST['confirm'] ?? "";

    $usernameError = validateUsername($username);
    $emailError = validateEmail($email);
    $passwordError = validatePassword($password);
    $confirmError = validateConfirm($password, $confirm);

    // Block duplicate emails (searches the accounts saved in the session)
    if ($emailError === "" && findAccount($email) !== null) {
        $emailError = "That email is already registered.";
    }

    // Save the account only when every field is valid
    if ($usernameError === "" && $emailError === "" && $passwordError === "" && $confirmError === "") {
        registerAccount($username, $email, $password);
        $strength = getPasswordStrength($password);
        $success = "Welcome to DejaBrew, " . ucfirst($username) . "! Your account is ready. Password strength: " . $strength . ".";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up | DejaBrew</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <?php require_once 'includes/login-signup-header.php'; ?>
    <main class="stage">
        
        
        <div class="visual" aria-hidden="true">
            <img class="table" src="login_assets/images/table.png" alt="">
            <img
            class="curve"
            src="login_assets/images/curve.png"
            alt=""
            aria-hidden="true">
            <img class="drinks" src="login_assets/images/drinksdesign.png" alt="">
        </div>
        <section class="panel">
            <img class="logo" src="assets/dblogo.png" alt="DejaBrew logo">
            <h1>Ready for D&eacute;j&agrave; Brew?</h1>

            <?php if ($success !== ""): ?>
                <p class="alert success" role="status"><?php echo escape($success); ?></p>
                <p class="switch"><a href="login.php">Go to Sign In</a></p>
            <?php else: ?>
                <form method="post" action="signup.php" novalidate>
                    <div class="field">
                        <label for="username">Username</label>
                        <div class="input-wrap icon-user">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                value="<?php echo escape($username); ?>"
                                autocomplete="username">
                        </div>
                        <p class="error"><?php echo escape($usernameError); ?></p>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <div class="input-wrap icon-mail">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="<?php echo escape($email); ?>"
                                autocomplete="email">
                        </div>
                        <p class="error"><?php echo escape($emailError); ?></p>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap icon-lock">
                            <input class="has-toggle" type="password" id="password" name="password" placeholder="Enter your password" autocomplete="new-password">
                            <button type="button" class="toggle" data-target="password" aria-label="Show password"><img src="login_assets/eye.svg" alt="" width="20" height="20"></button>
                        </div>
                        <p class="error"><?php echo escape($passwordError); ?></p>
                    </div>
                    <div class="field">
                        <label for="confirm">Confirm Password</label>
                        <div class="input-wrap icon-lock">
                            <input class="has-toggle" type="password" id="confirm" name="confirm" placeholder="Confirm your password" autocomplete="new-password">
                            <button type="button" class="toggle" data-target="confirm" aria-label="Show password"><img src="login_assets/eye.svg" alt="" width="20" height="20"></button>
                        </div>
                        <p class="error" id="confirm-error"><?php echo escape($confirmError); ?></p>
                    </div>
                    <button class="submit" type="submit" name="submit">Sign Up</button>
                </form>
                <p class="switch">Already have an account? <a href="login.php">Sign In</a></p>
            <?php endif; ?>
        </section>
    </main>
    <script src="js/signup.js"></script>
</body>

</html>