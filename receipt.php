<?php
// receipt.php
// My Orders page. Shows the customer's past orders and the receipt of the order they pick.
// Concepts used here: import, variables, superglobals, operators, if statements, and functions.

// Import: require_once loads functions.php for the helper functions like escape().
// This also loads config.php and starts the session.
require_once 'includes/functions.php';
requireRole('customer');   // guests (and staff) are sent to login.php

// Superglobal ($_GET): receipt.php?order=ID opens the receipt of that order.
// The ?? operator makes it "" if there is no order in the URL.
// The JavaScript checks later if that order really exists.
$requestedId = $_GET['order'] ?? "";
// Control structure (if): a URL can send other types like an array, so make sure the value is a string.
if (!is_string($requestedId)) {
    $requestedId = "";
}
$requestedId = trim($requestedId);

// Superglobal ($_GET) and comparison operator (===): receipt.php?orders=none shows the "No orders yet" page, for testing.
// The ternary operator (? :) gives "1" if it matches and "0" if not.
$forceEmpty = (($_GET['orders'] ?? "") === "none") ? "1" : "0";

// Superglobal ($_SERVER): gets the file name of this page (receipt.php).
// It is used in the order links so they still work even if the file is renamed.
$pageUrl = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt | DejaBrew</title>
    <link rel="icon" type="image/png" href="assets/db-logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/receipt.css">
</head>

<body class="mo-body">
    <!-- Import: include loads the navbar (includes/header.php) so all pages use the same one -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- The orders are saved by the cart in js/orders-data.js,
         so an order placed at checkout shows up here. js/receipt.js builds the order cards
         and the receipts inside #mo-list and #mo-receipts.
         The PHP variables above are passed to the JavaScript using data- attributes.
         escape() makes the values safe to print in the HTML. -->
    <main class="mo-page mo-page--empty" id="mo-page" data-page-url="<?php echo escape($pageUrl); ?>"
        data-initial-order="<?php echo escape($requestedId); ?>" data-force-empty="<?php echo $forceEmpty; ?>">

        <!-- No orders yet: looks the same as the empty cart page. It stays hidden until the JavaScript finds no orders. -->
        <section class="mo-none" id="mo-none" aria-labelledby="mo-none-title" hidden>
            <img class="mo-none__icon" src="assets/dblogo.png" alt="DejaBrew logo" width="112" height="112">

            <h1 class="mo-none__title" id="mo-none-title">No orders yet</h1>
            <p class="mo-none__text">
                Looks like you haven't placed an order yet.<br>
                Go find your favorite drink!
            </p>
            <a class="mo-none__btn" href="index.php#menu">Explore Menu &rarr;</a>
        </section>

        <!-- Background drinks, just for design. The size and position are in css/receipt.css (.mo-drinks) -->
        <img class="mo-drinks" src="receipt_assets/drinksdesign.png" alt="" aria-hidden="true">

        <!-- The layout is hidden at first. receipt.js only shows it if the user has orders. -->
        <div class="mo-layout" id="mo-layout" hidden>

            <!-- Left side: heading and order history (js/receipt.js adds the cards here) -->
            <section class="mo-orders" aria-labelledby="mo-title">
                <h1 class="mo-title" id="mo-title">My Orders</h1>
                <p class="mo-subtitle">View your past orders and receipt</p>

                <ul class="mo-list" id="mo-list"></ul>
            </section>

            <!-- Right side: receipt of the selected order (only one shows at a time) -->
            <section class="mo-receipt-wrap" id="mo-receipts" aria-label="Selected order receipt"></section>

        </div>

        <!-- Message for browsers with JavaScript turned off, since the orders are built with JavaScript -->
        <noscript>
            <p class="mo-none__text">Please turn on JavaScript to see your orders.</p>
        </noscript>
    </main>

    <!-- Same order as cart.php: orders-data.js first, then cart.js (cart count in the navbar), then receipt.js -->
    <script>
        // Who is logged in? (from the PHP session)
        const LOGGED_IN_USER = <?= json_encode($_SESSION['user'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script src="js/orders-data.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/activity-badge.js"></script>
    <script src="js/receipt.js"></script>
</body>

</html>