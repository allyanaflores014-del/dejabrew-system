<?php
// Shared settings for the auth pages. Loaded by functions.php.

// Start the session once (it stores accounts, login state, and attempt counter).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

// Limits used by the login attempt limiter.
define('MAX_ATTEMPTS', 3);       // wrong tries allowed
define('LOCKOUT_SECONDS', 30);   // how long the lockout lasts

// Preset staff accounts (staff do not sign up). Change these to your own.
define('STAFF_ACCOUNTS', [
    ['username' => 'barista', 'email' => 'staff@dejabrew.com', 'password' => 'staff12345']
]);