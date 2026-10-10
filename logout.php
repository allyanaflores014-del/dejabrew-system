<?php
// logout.php
// Signs the user out, then goes back to the login page.

// Import: shared helpers (this also starts the session)
require_once 'includes/functions.php';

// Removes the logged-in user from $_SESSION['user'].
// (The registered accounts stay in the session, so the user can sign in again.)
logoutUser();

// login.php?action=logout shows the "You have been signed out" message
header("Location: login.php?action=logout");
exit;