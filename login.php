<?php
// login.php
// Sign In page. Customers and staff log in here.
// Concepts used here: import, variables, superglobals, operators, control structures (if and switch), and functions.

// Import: require_once loads functions.php only one time.
// It has our functions (validateEmail, findAccount, loginUser, etc.),
// and it also loads config.php and starts the session so we don't repeat that code here.
require_once 'includes/functions.php';

// Variables for the form input and the messages shown on the page.
// They are empty at first and only get values when the form is submitted.
$email = "";
$emailError = "";
$passwordError = "";
$success = "";

// Superglobal ($_GET): reads the "action" in the URL, like login.php?action=logout.
// The ?? operator makes it "" if there is no action in the URL.
$action = $_GET['action'] ?? "";
// "next" = where to go after login. Only "cart" is allowed, so users can't be sent to other sites.
$next = ($_GET['next'] ?? "") === "cart" ? "cart" : "";

// Control structure (switch): decides what to do depending on the value of $action.
switch ($action) {
  case "logout":
    // Function call: logoutUser() removes the login data, then we show a goodbye message.
    logoutUser();
    $success = "You have been signed out. See you again!";
    break;
  default:
    // No special action, so check if the user is already logged in using isLoggedIn().
    // && is a logical operator, so both must be true: the user is logged in AND the form was not submitted.
    // $_SESSION['user'] has the info of the logged-in user.
    if (isLoggedIn() && $_SERVER['REQUEST_METHOD'] !== 'POST') {
      $success = "You're already signed in as " . ucfirst($_SESSION['user']['username']) . ".";
    }
}

if ($next === "cart" && !isLoggedIn() && $success === "") {
  $success = "Please sign in to place your order.";
}

// Superglobals ($_SERVER and $_POST): this block only runs when the Sign In button is clicked.
// $_SERVER['REQUEST_METHOD'] tells us if the page was sent with POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  // $_POST has the form inputs. trim() removes extra spaces, and the ?? "" makes it empty if the field is missing.
  $email = trim($_POST['email'] ?? "");
  $password = $_POST['password'] ?? "";

  // Check the inputs. validateEmail() returns "" if the email is okay, or an error message if not.
  $emailError = validateEmail($email);
  if (strlen($password) === 0) {
    $passwordError = "Please enter your password.";
  }

  // Only check the account if both fields have no errors.
  if ($emailError === "" && $passwordError === "") {
    // Attempt limiter: if the user failed too many times, they can't log in for a short time.
    if (isLockedOut()) {
      $passwordError = "Too many failed attempts. Try again in " . secondsLeft() . " seconds.";
    } else {
      // Look for the account with this email. It gives null if there is none.
      $account = findAccount($email);

      // password_verify() compares the typed password with the hashed password that was saved.
      if ($account !== null && password_verify($password, $account['password'])) {
        // Correct login: reset the failed attempts, save the user in the session,
        // then go to the right home page (staff and customer have different pages).
        resetAttempts();
        loginUser($account);
        $destination = getHomePage($account['role'] ?? 'customer');
        if ($next === "cart" && ($account['role'] ?? 'customer') === 'customer') {
          $destination = "cart.php";
        }
        header("Location: " . $destination);
        exit;
      } else {
        // Wrong email or password, so count it as a failed attempt.
        recordFailedAttempt();
        $attemptsLeft = MAX_ATTEMPTS - $_SESSION['attempts'];   // arithmetic operator (subtraction)

        // Show how many tries are left, or say that the user is now locked out.
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
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="css/login.css">
</head>

<body>

  <!-- Import: the navbar is in its own file so the login and sign up pages use the same header -->
  <?php require_once 'includes/login-signup-header.php'; ?>

  <main class="stage">

    <!-- Decorative images on the left side. aria-hidden so screen readers skip them -->
    <div class="visual" aria-hidden="true">
      <img class="table" src="login_assets/images/table.png" alt="">
      <img class="curve" src="login_assets/images/curve.png" alt="" aria-hidden="true">
      <img class="drinks" src="login_assets/images/drinksdesign.png" alt="">
    </div>
    <section class="panel">
      <img class="logo" src="assets/dblogo.png" alt="DejaBrew logo">
      <h1>Welcome Back!</h1>
      <p class="subtitle">Do you get Deja Brew?</p>

      <!-- Control structure (if): the success message only shows if $success is not empty.
           escape() makes the text safe to print in the HTML. -->
      <?php if ($success !== ""): ?>
        <p class="alert success" role="status"><?php echo escape($success); ?></p>
      <?php endif; ?>

      <!-- The form sends its data with POST to this same page (login.php) -->
      <form method="post" action="login.php<?php echo $next === 'cart' ? '?next=cart' : ''; ?>" novalidate>
        <div class="field">
          <label for="email">Email</label>
          <div class="input-wrap icon-mail">
            <!-- value puts back what the user typed so it is not erased when there is an error -->
            <input type="email" id="email" name="email" placeholder="Enter your email"
              value="<?php echo escape($email); ?>" autocomplete="email">
          </div>
          <!-- Shows the email error (empty if there is none) -->
          <p class="error"><?php echo escape($emailError); ?></p>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap icon-lock">
            <input class="has-toggle" type="password" id="password" name="password" placeholder="Enter your password"
              autocomplete="current-password">
            <!-- The eye button shows or hides the password (the code is in js/login.js) -->
            <button type="button" class="toggle" data-target="password" aria-label="Show password"><img
                src="login_assets/eye.svg" alt="" width="20" height="20"></button>
          </div>
          <!-- Shows the password error or the attempt limit message -->
          <p class="error"><?php echo escape($passwordError); ?></p>
        </div>
        <button class="submit" type="submit" name="submit">Sign In</button>
      </form>
      <p class="switch">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </section>
  </main>
  <!-- JavaScript for the show/hide password button -->
  <script src="js/login.js"></script>
</body>

</html>