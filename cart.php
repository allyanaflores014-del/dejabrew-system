<?php include __DIR__ . '/includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DejaBrew | Cart</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cart.css">
</head>

<body>


<main class="cart-page">

    <!-- STATE 1: EMPTY CART -->
    <section id="empty-cart" class="empty-cart">
        <div class="empty-illustration">☕</div>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything yet.<br>Go find your favorite drink!</p>
        <a href="index.php#menu" class="btn btn-primary">Explore Menu →</a>
    </section>

    <!-- STATE 2: CART WITH ITEMS -->
    <section id="cart-with-items" class="cart-layout">

        <!-- LEFT SIDE: cart items -->
        <div class="cart-left">
            <a href="index.php#menu" class="back-link">← Back to Menu</a>
            <h1>Your Cart</h1>
            <p class="cart-subtitle">Review your items before proceeding to checkout.</p>

            <div id="cart-items">
                <!-- JavaScript fills this in (see js/cart.js) -->
            </div>
        </div>

        <!-- RIGHT SIDE: order summary -->
        <aside class="order-summary">
            <h2>Order Summary</h2>

            <label for="pickup-time">Pickup Time:</label>
            <input type="time" id="pickup-time">

            <label for="payment-method">Payment Method:</label>
            <select id="payment-method">
                <option value="cash" selected>Cash (pay upon pickup)</option>
            </select>

            <label for="barista-note">Note to Barista:</label>
            <textarea id="barista-note" rows="3" placeholder="e.g. Less ice"></textarea>

            <!-- VOUCHER SECTION -->
            <label for="voucher-code">Promo / Voucher Code:</label>
            <div class="voucher-row">
                <input type="text" id="voucher-code" placeholder="e.g. DEJA5">
                <button type="button" class="btn btn-primary" id="apply-voucher-btn">Apply</button>
            </div>
            <p id="voucher-message" class="voucher-message"></p>

            <!-- TOTALS -->
            <div class="summary-row">
                <span>Subtotal <span id="item-count"></span></span>
                <span id="subtotal">₱0</span>
            </div>
            <div class="summary-row">
                <span>Discount</span>
                <span id="discount">-₱0</span>
            </div>
            <div class="summary-row total-row">
                <span>Total</span>
                <span id="total">₱0</span>
            </div>

            <!-- CHECKOUT (only ONE button) -->
            <p id="checkout-error" class="checkout-error"></p>
            <button class="btn btn-primary" id="checkout-btn">Proceed to Checkout →</button>
        </aside>

    </section>

</main>

<script src="js/cart.js"></script>
</body>
</html>