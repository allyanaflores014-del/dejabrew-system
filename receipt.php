<?php
// Import: shared helpers (this also starts the session)
require_once 'includes/functions.php';

// $_GET: ?order=ID opens that order's receipt (the JavaScript checks that it exists)
$requestedId = $_GET['order'] ?? "";
if (!is_string($requestedId)) {
    $requestedId = "";
}
$requestedId = trim($requestedId);

// $_GET: receipt.php?orders=none previews the "No orders yet" page (for testing)
$forceEmpty = (($_GET['orders'] ?? "") === "none") ? "1" : "0";

// $_SERVER: this page's own file name, used for the order links
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/receipt.css">
</head>

<body class="mo-body">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- The orders come from the same place the cart saves them (js/orders-data.js),
         so an order placed at checkout shows up here. js/receipt.js builds the cards
         and receipts inside #mo-list and #mo-receipts. -->
    <main class="mo-page mo-page--empty" id="mo-page"
        data-page-url="<?php echo escape($pageUrl); ?>"
        data-initial-order="<?php echo escape($requestedId); ?>"
        data-force-empty="<?php echo $forceEmpty; ?>">

        <!-- NO ORDERS YET: same style as the empty cart page -->
        <section class="mo-none" id="mo-none" aria-labelledby="mo-none-title" hidden>
            <svg class="mo-none__icon" viewBox="0 0 120 120" width="112" height="112" aria-hidden="true">
                <!-- placeholder cup: replace this <svg> with the same <img> the cart page uses -->
                <ellipse cx="58" cy="70" rx="46" ry="42" fill="#c4b2de" />
                <circle cx="96" cy="56" r="13" fill="none" stroke="#b9a5d8" stroke-width="9" />
                <circle cx="58" cy="56" r="34" fill="#b9a5d8" />
                <circle cx="58" cy="56" r="27" fill="#7b4a4d" />
                <ellipse cx="50" cy="46" rx="14" ry="8" fill="#9a6366" opacity="0.6" />
            </svg>
            <h1 class="mo-none__title" id="mo-none-title">No orders yet</h1>
            <p class="mo-none__text">
                Looks like you haven't placed an order yet.<br>
                Go find your favorite drink!
            </p>
            <a class="mo-none__btn" href="index.php#menu">Explore Menu &rarr;</a>
        </section>

        <!-- Background drinks (decorative). Its size and position are set in css/receipt.css (.mo-drinks) -->
        <img class="mo-drinks" src="receipt_assets/drinksdesign.png" alt="" aria-hidden="true">

        <div class="mo-layout" id="mo-layout" hidden>

            <!-- LEFT: heading + order history -->
            <section class="mo-orders" aria-labelledby="mo-title">
                <h1 class="mo-title" id="mo-title">My Orders</h1>
                <p class="mo-subtitle">View your past orders and receipt</p>

                <ul class="mo-list" id="mo-list"></ul>
            </section>

            <!-- RIGHT: receipt of the selected order (only one is visible at a time) -->
            <section class="mo-receipt-wrap" id="mo-receipts" aria-label="Selected order receipt"></section>

        </div>

        <noscript>
            <p class="mo-none__text">Please turn on JavaScript to see your orders.</p>
        </noscript>
    </main>

    <!-- same order as cart.php: orders-data.js first, then cart.js (cart count in the navbar) -->
    <script src="js/orders-data.js"></script>
    <script src="js/cart.js"></script>
    <script src="js/receipt.js"></script>
</body>

</html>