<?php
// config.php
// Settings for the login and sign-up system. This is loaded by includes/functions.php,
// so any page that imports functions.php also gets these settings.

// Start the session only if there is none yet. session_status() checks this so
// we don't call session_start() twice and get a warning.
// The session keeps the accounts, the login state, and the failed attempts.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone is set to Manila so the date and time of the orders is correct.
date_default_timezone_set('Asia/Manila');

// Constants for the login attempt limiter. They never change while the program runs.
define('MAX_ATTEMPTS', 3);       // wrong passwords allowed before the lockout
define('LOCKOUT_SECONDS', 30);   // seconds the user has to wait when locked out

// Staff accounts are saved here because staff can't sign up on the website.
// Each one is an array with username, email, and password.
define('STAFF_ACCOUNTS', [
    ['username' => 'barista', 'email' => 'staff@dejabrew.com', 'password' => 'staff12345']
]);

// The registered customer accounts are saved in this file so they are NOT lost when the session ends.
// The folder "data" is created automatically on the first sign up.
// The first line of the file is a PHP exit command, so nobody can read it by opening its link in the browser.
define('ACCOUNTS_FILE', __DIR__ . '/../data/accounts.php');