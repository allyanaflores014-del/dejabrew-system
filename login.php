<?php
// Import: shared functions (also loads config + starts the session)
require_once 'includes/functions.php';

// Variables used by the form and its messages
$email = "";
$emailError = "";
$passwordError = "";
$success = "";

// $_GET: login.php?action=logout signs the user out; otherwise check if already signed in
$action = $_GET['action'] ?? "";

switch ($action) {
  case "logout":
    logoutUser();
    $success = "You have been signed out. See you again!";
    break;
  default:
    if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] !== 'POST') {
      $success = "You're already signed in as " . ucfirst($_SESSION['user']['username']) . ".";
    }
}

// $_SERVER + $_POST: runs only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  $email = trim($_POST['email'] ?? "");
  $password = $_POST['password'] ?? "";

  $emailError = validateEmail($email);
  if (strlen($password) === 0) {
    $passwordError = "Please enter your password.";
  }

  // Only check the account when both fields are valid
  if ($emailError === "" && $passwordError === "") {
    if (isLockedOut()) {
      $passwordError = "Too many failed attempts. Try again in " . secondsLeft() . " seconds.";
    } else {
      $account = findAccount($email);

            if ($account !== null && password_verify($password, $account['password'])) {
        resetAttempts();
        loginUser($account);
        header("Location: " . getHomePage($account['role'] ?? 'customer'));
        exit;
      } else {
        recordFailedAttempt();
        $attemptsLeft = MAX_ATTEMPTS - $_SESSION['attempts'];   // arithmetic operator

        if ($attemptsLeft > 0) {
          $passwordError = "Incorrect email or password. " . $attemptsLeft . " attempt(s) left.";
        } else {
          $passwordError = "Too many failed attempts. Try again in " . secondsLeft() . " seconds.";
        }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In | DejaBrew</title>
  <link rel="icon" type="image/png" href="assets/db-logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
      <h1>Welcome Back!</h1>
      <p class="subtitle">Do you get Deja Brew?</p>

      <?php if ($success !== ""): ?>
        <p class="alert success" role="status"><?php echo escape($success); ?></p>
      <?php endif; ?>

      <form method="post" action="login.php" novalidate>
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
            <input class="has-toggle" type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
            <button type="button" class="toggle" data-target="password" aria-label="Show password"><img src="login_assets/eye.svg" alt="" width="20" height="20"></button>
          </div>
          <p class="error"><?php echo escape($passwordError); ?></p>
        </div>
        <button class="submit" type="submit" name="submit">Sign In</button>
      </form>
      <p class="switch">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </section>
  </main>
  <script src="js/login.js"></script>
</body>

</html>