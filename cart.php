<?php
$pageCss = 'cart.css';   
include 'includes/header.php';
?>

<main class="cart-page">

    <!-- STATE 1: EMPTY CART -->
    <section id="empty-cart" class="empty-cart">
        <div class="empty-illustration">☕</div>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything yet.<br>Go find your favorite drink!</p>
        <a href="menu.php" class="btn btn-primary">Explore Menu →</a>
    </section>

    <!-- STATE 2: CART WITH ITEMS -->
    <section id="cart-with-items" class="cart-layout">

        <!-- LEFT SIDE: cart items -->
        <div class="cart-left">
            <a href="menu.php" class="back-link">← Back to Menu</a>
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

<!-- TEMPORARY: test buttons. Delete this whole block when the teammate's menu is connected. -->
<div class="test-buttons" style="padding: 10px 40px; background: #fff3cd;">
    <strong>TEST:</strong>
    <button class="add-to-cart-btn" data-id="1" data-name="Iced Caramel Macchiato" data-price="170" data-image="assets/icecaramel.png">Add Macchiato</button>
    <button class="add-to-cart-btn" data-id="2" data-name="Iced Americano" data-price="120" data-image="assets/icecaramel.png">Add Americano</button>
</div>

<script src="js/cart.js"></script>
</body>
</html>