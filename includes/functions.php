<?php
// functions.php
// Functions used by login.php and signup.php.
// The validate functions return "" if the input is okay, or an error message if not.

// Import: loads the settings and starts the session.
// I used require_once because the system can't work without config.php.
require_once __DIR__ . '/config.php';

// Replaces the special characters in the text so user input can't break the HTML.
function escape($text) {
    $text = str_replace("&", "&amp;", $text);
    $text = str_replace("<", "&lt;", $text);
    $text = str_replace(">", "&gt;", $text);
    $text = str_replace('"', "&quot;", $text);
    return $text;
}

// Username must not be empty, must be 3 to 20 characters, and has no spaces.
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

// Checks the email format by hand: one "@", something before it, and a domain with a dot.
function validateEmail($email) {
    if (strlen($email) === 0) {
        return "Please enter your email.";
    }
    $parts = explode("@", $email);   // example: "juan@mail.com" becomes ["juan", "mail.com"]
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

// Password must not be empty and must have at least 8 characters.
function validatePassword($password) {
    if (strlen($password) === 0) {
        return "Please enter a password.";
    } elseif (strlen($password) < 8) {
        return "Password must be at least 8 characters.";
    } else {
        return "";
    }
}

// Checks that the confirm password is not empty and is the same as the password.
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
// Account functions
// We don't have a database, so the accounts are saved in a file (data/accounts.php).
// This way they are still there after the session ends or the browser is closed.
// ---------------------------------------------------------

// Gets the list of registered accounts from the file. If there is none yet, it gives an empty list.
function getAccounts() {
    if (!file_exists(ACCOUNTS_FILE)) {
        return [];
    }
    // The first line of the file is the PHP exit command (it protects the file), so remove it before reading the JSON.
    $text = file_get_contents(ACCOUNTS_FILE);
    $text = str_replace("<?php exit; ?>\n", "", $text);
    $accounts = json_decode($text, true);

    // If the file is empty or broken, json_decode() does not give an array, so use an empty list.
    return is_array($accounts) ? $accounts : [];
}

// Writes the list of accounts to the file. Returns true if it was saved, false if not.
function saveAccounts($accounts) {
    // Create the "data" folder the first time (nothing happens if it already exists).
    $folder = dirname(ACCOUNTS_FILE);
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    $text = "<?php exit; ?>\n" . json_encode($accounts, JSON_PRETTY_PRINT);
    // LOCK_EX stops two sign ups from writing at the same time.
    return file_put_contents(ACCOUNTS_FILE, $text, LOCK_EX) !== false;
}

// Finds an account using the email. It checks the staff accounts first, then the customers who signed up.
// Returns the account array, or null if there is no match.
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

// Saves a new customer account. The password is hashed so it is not saved as plain text.
// Returns true if the account was saved, false if the file could not be written.
function registerAccount($username, $email, $password) {
    $accounts = getAccounts();
    $accounts[] = [
        'username' => $username,
        'email'    => strtolower($email),
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role'     => 'customer'
    ];
    return saveAccounts($accounts);
}

// Logs the user in by saving their username, email, and role in the session.
// session_regenerate_id() gives a new session id after login for safety.
function loginUser($account) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'username' => $account['username'],
        'email'    => $account['email'],
        'role'     => $account['role'] ?? 'customer'
    ];
}

// Gives the password a score, then returns Weak, Medium, or Strong.
// One point each for: 8+ characters, 12+ characters, has a number,
// has lowercase and uppercase, and has a special character.
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
// Login functions
// ---------------------------------------------------------

// Returns a greeting based on the current hour (morning, afternoon, or evening).
function getGreeting() {
    $hour = (int) date("G");   // hour in 24-hour format, 0 to 23
    if ($hour < 12) {
        return "Good morning";
    } elseif ($hour < 18) {
        return "Good afternoon";
    } else {
        return "Good evening";
    }
}

// Returns true if the user is still locked out because of too many wrong attempts.
function isLockedOut() {
    $attempts = $_SESSION['attempts'] ?? 0;
    $lockedUntil = $_SESSION['locked_until'] ?? 0;

    if ($attempts >= MAX_ATTEMPTS && time() >= $lockedUntil) {
        resetAttempts();   // the waiting time is done, so the attempts start again from 0
        return false;
    }
    return $attempts >= MAX_ATTEMPTS;
}

// Returns how many seconds are left before the user can try again.
function secondsLeft() {
    return max(0, ($_SESSION['locked_until'] ?? 0) - time());
}

// Adds 1 to the failed attempts. When it reaches the limit, the lockout timer starts.
function recordFailedAttempt() {
    $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
    if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
        $_SESSION['locked_until'] = time() + LOCKOUT_SECONDS;
    }
}

// Sets the failed attempts back to 0. Used after a successful login or when the lockout ends.
function resetAttempts() {
    $_SESSION['attempts'] = 0;
    $_SESSION['locked_until'] = 0;
}



// Logs the user out by removing them from the session.
function logoutUser() {
    unset($_SESSION['user']);
}

// Returns true if there is a user logged in.
function isLoggedIn() {
    return isset($_SESSION['user']);
}


// Returns the role of the logged-in user: "staff" or "customer". If nobody is logged in, it returns "guest".
function getRole() {
    return $_SESSION['user']['role'] ?? "guest";
}

// For protected pages: put this at the top of the page.
// If the visitor's role is not the one needed, they are sent back to login.php.
function requireRole($role) {
    if (getRole() !== $role) {
        header("Location: login.php");
        exit;
    }
}

// Returns which page the user goes to after logging in, depending on the role.
function getHomePage($role) {
    switch ($role) {
        case "staff":
            return "staff-menu.php";   // staff goes to the staff page
        default:
            return "index.php";             // customers go to the home page
    }
}