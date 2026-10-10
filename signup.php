<?php
// signup.php
// Sign Up page. A new customer creates an account here, and the inputs are checked first before saving.
// Concepts used here: import, variables, superglobals, operators, if statements, and functions.

// Import: require_once loads functions.php only one time.
// It has the validate functions and registerAccount(), and it also loads config.php and starts the session.
require_once 'includes/functions.php';

// Variables for the form inputs and the messages.
// They are empty at first and only get values after the form is submitted.
$username = "";
$email = "";
$usernameError = "";
$emailError = "";
$passwordError = "";
$confirmError = "";
$success = "";

// Superglobals ($_SERVER and $_POST): this block only runs when the Sign Up button is clicked.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Get the inputs from $_POST. trim() removes extra spaces, and the ?? "" makes it empty if the field is missing.
    $username = trim($_POST['username'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm = $_POST['confirm'] ?? "";

    // Function calls: each validate function returns "" if the value is okay,
    // or an error message that is shown under that field.
    $usernameError = validateUsername($username);
    $emailError = validateEmail($email);
    $passwordError = validatePassword($password);
    $confirmError = validateConfirm($password, $confirm);

    // Stops the same email from being used twice. findAccount() looks through the saved accounts
    // and returns null if the email is not used yet. (=== and !== are comparison operators)
    if ($emailError === "" && findAccount($email) !== null) {
        $emailError = "That email is already registered.";
    }

    // Only save the account if there are no errors in any field.
    // && is a logical operator, so all of the conditions have to be true.
    if ($usernameError === "" && $emailError === "" && $passwordError === "" && $confirmError === "") {
        registerAccount($username, $email, $password);
        // Gets the password strength (Weak, Medium, or Strong) to show in the message.
        $strength = getPasswordStrength($password);
        // The dot (.) operator joins the text and the variables into one message.
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
    <!-- Import: the navbar is in its own file so the login and sign up pages use the same header -->
    <?php require_once 'includes/login-signup-header.php'; ?>
    <main class="stage">
        
        
        <!-- Decorative images on the left side. aria-hidden so screen readers skip them -->
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

            <!-- Control structure (if / else): if the account was created, show the success message
                 and a link to Sign In. If not, show the sign up form. -->
            <?php if ($success !== ""): ?>
                <p class="alert success" role="status"><?php echo escape($success); ?></p>
                <p class="switch"><a href="login.php">Go to Sign In</a></p>
            <?php else: ?>
                <!-- The form sends its data with POST to this same page (signup.php) -->
                <form method="post" action="signup.php" novalidate>
                    <div class="field">
                        <label for="username">Username</label>
                        <div class="input-wrap icon-user">
                            <!-- value puts back what the user typed so it is not erased when there is an error.
                                 escape() makes the text safe to print in the HTML. -->
                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                value="<?php echo escape($username); ?>"
                                autocomplete="username">
                        </div>
                        <!-- Shows the username error (empty if there is none) -->
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
                            <!-- No value here on purpose, the password is never put back in the field for safety -->
                            <input class="has-toggle" type="password" id="password" name="password" placeholder="Enter your password" autocomplete="new-password">
                            <!-- The eye button shows or hides the password (the code is in js/signup.js) -->
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
                        <!-- Shows the error if the two passwords are not the same -->
                        <p class="error" id="confirm-error"><?php echo escape($confirmError); ?></p>
                    </div>
                    <button class="submit" type="submit" name="submit">Sign Up</button>
                </form>
                <p class="switch">Already have an account? <a href="login.php">Sign In</a></p>
            <?php endif; ?>
        </section>
    </main>
    <!-- JavaScript for the show/hide password buttons -->
    <script src="js/signup.js"></script>
</body>

</html>