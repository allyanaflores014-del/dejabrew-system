<?php
// Shared helper functions for login.php and signup.php.
// Each validate function returns "" when the value is OK,
// or an error message when it is not.

// Import: settings + session start (require = file is essential)
require_once __DIR__ . '/config.php';

// Makes user text safe to print back into the page (uses str_replace).
function escape($text) {
    $text = str_replace("&", "&amp;", $text);
    $text = str_replace("<", "&lt;", $text);
    $text = str_replace(">", "&gt;", $text);
    $text = str_replace('"', "&quot;", $text);
    return $text;
}

function validateUsername($username) {
    if (strlen($username) === 0) {
        return "Please enter a username.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        return "Username must be 3 to 20 characters long.";
    } elseif (strpos($username, " ") !== false) {
        return "Username cannot contain spaces.";
    } else {
        return "";
    }
}

function validateEmail($email) {
    if (strlen($email) === 0) {
        return "Please enter your email.";
    }
    $parts = explode("@", $email);   // "juan@mail.com" -> ["juan", "mail.com"]
    if (count($parts) !== 2 || strlen($parts[0]) === 0) {
        return "Email must look like name@example.com.";
    } elseif (strpos($email, " ") !== false) {
        return "Email cannot contain spaces.";
    } elseif (strpos($parts[1], ".") === false
        || substr($parts[1], 0, 1) === "."
        || substr($parts[1], -1) === ".") {
        return "Email needs a valid domain, like example.com.";
    } else {
        return "";
    }
}

function validatePassword($password) {
    if (strlen($password) === 0) {
        return "Please enter a password.";
    } elseif (strlen($password) < 8) {
        return "Password must be at least 8 characters.";
    } else {
        return "";
    }
}

function validateConfirm($password, $confirm) {
    if (strlen($confirm) === 0) {
        return "Please confirm your password.";
    } elseif ($password !== $confirm) {
        return "Passwords do not match.";
    } else {
        return "";
    }
}

// ---------------------------------------------------------
// ACCOUNT FUNCTIONS (accounts are kept in $_SESSION for now)
// ---------------------------------------------------------

// Returns the list of registered accounts (empty list if none yet).
function getAccounts() {
    if (!isset($_SESSION['accounts'])) {
        $_SESSION['accounts'] = [];
    }
    return $_SESSION['accounts'];
}

// Looks for an account by email (staff list first, then signed-up customers).
// Returns the account array, or null if not found.
function findAccount($email) {
    $email = strtolower($email);

    foreach (STAFF_ACCOUNTS as $staff) {
        if ($staff['email'] === $email) {
            return [
                'username' => $staff['username'],
                'email'    => $staff['email'],
                'password' => password_hash($staff['password'], PASSWORD_DEFAULT),
                'role'     => 'staff'
            ];
        }
    }

    foreach (getAccounts() as $account) {
        if ($account['email'] === $email) {
            return $account;
        }
    }
    return null;
}

// Saves a new customer account. The password is hashed, never stored as plain text.
function registerAccount($username, $email, $password) {
    $accounts = getAccounts();
    $accounts[] = [
        'username' => $username,
        'email'    => strtolower($email),
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role'     => 'customer'
    ];
    $_SESSION['accounts'] = $accounts;
}

// Marks the user as logged in by saving them (and their role) in the session.
function loginUser($account) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'username' => $account['username'],
        'email'    => $account['email'],
        'role'     => $account['role'] ?? 'customer'
    ];
}

// Rates a password as Weak, Medium, or Strong using a simple score.
function getPasswordStrength($password) {
    $score = 0;
    if (strlen($password) >= 8) {
        $score++;
    }
    if (strlen($password) >= 12) {
        $score++;
    }
    if (preg_match('/[0-9]/', $password)) {
        $score++;
    }
    if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) {
        $score++;
    }
    if (preg_match('/[^A-Za-z0-9]/', $password)) {
        $score++;
    }

    switch ($score) {
        case 5:
        case 4:
            return "Strong";
        case 3:
        case 2:
            return "Medium";
        default:
            return "Weak";
    }
}

// ---------------------------------------------------------
// LOGIN FUNCTIONS
// ---------------------------------------------------------

// Returns a greeting that depends on the current time of day.
function getGreeting() {
    $hour = (int) date("G");   // 0-23
    if ($hour < 12) {
        return "Good morning";
    } elseif ($hour < 18) {
        return "Good afternoon";
    } else {
        return "Good evening";
    }
}

// True while the user is locked out after too many wrong attempts.
function isLockedOut() {
    $attempts = $_SESSION['attempts'] ?? 0;
    $lockedUntil = $_SESSION['locked_until'] ?? 0;

    if ($attempts >= MAX_ATTEMPTS && time() >= $lockedUntil) {
        resetAttempts();   // lockout is over, start fresh
        return false;
    }
    return $attempts >= MAX_ATTEMPTS;
}

// How many seconds are left before the lockout ends.
function secondsLeft() {
    return max(0, ($_SESSION['locked_until'] ?? 0) - time());
}

// Adds one failed attempt and starts the lockout when the limit is reached.
function recordFailedAttempt() {
    $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
    if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
        $_SESSION['locked_until'] = time() + LOCKOUT_SECONDS;
    }
}

// Clears the failed attempt counter (after a successful login or finished lockout).
function resetAttempts() {
    $_SESSION['attempts'] = 0;
    $_SESSION['locked_until'] = 0;
}



// Removes the logged-in user from the session.
function logoutUser() {
    unset($_SESSION['user']);
}

// True if someone is currently logged in.
function isLoggedIn() {
    return isset($_SESSION['user']);
}


// Returns the logged-in user's role: "staff", "customer", or "guest" if not logged in.
function getRole() {
    return $_SESSION['user']['role'] ?? "guest";
}

// Put this at the top of a protected page. Sends the visitor back to login
// if their role does not match.
function requireRole($role) {
    if (getRole() !== $role) {
        header("Location: login.php");
        exit;
    }
}

// Returns the page each role should land on after logging in.
function getHomePage($role) {
    switch ($role) {
        case "staff":
            return "staff-dashboard.php";   // <-- change to your real staff page
        default:
            return "index.php";             // <-- change to your real customer page
    }
}