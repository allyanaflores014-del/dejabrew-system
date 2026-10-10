<?php
require_once 'includes/functions.php';   
requireRole('staff');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DejaBrew | Order Management</title>
    <link rel="icon" type="image/png" href="assets/db-logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/staff-orders.css">
</head>

<body class="staff-body">

<!-- One wrapper holds the top bar, the sidebar, and the main area -->
<div class="staff-shell">

    <?php include __DIR__ . '/includes/staff-sidebar.php'; ?>

    <main class="staff-main">

        <!-- MIDDLE: title, search, filters, order list -->
        <section class="orders-section">

            <div class="orders-heading">
                <div>
                    <h1>Order Management</h1>
                    <p class="orders-subtitle">View and manage incoming orders</p>
                </div>

                <div class="order-search">
                    <label for="order-search" class="visually-hidden">Search orders</label>
                    <input type="search" id="order-search" placeholder="Search by Order #, Customer or Item">
                </div>
            </div>

            <div class="orders-card">

                <!-- Filter buttons -->
                <div class="filter-pills" role="group" aria-label="Filter orders by status">
                    <button type="button" class="filter-pill is-active" data-status="all">All orders</button>
                    <button type="button" class="filter-pill" data-status="pending">Pending</button>
                    <button type="button" class="filter-pill" data-status="preparing">Preparing</button>
                    <button type="button" class="filter-pill" data-status="completed">Completed</button>
                </div>

                <!-- Column titles -->
                <div class="orders-table-head">
                    <span>Order #</span>
                    <span>Customer</span>
                    <span>Items</span>
                    <span>Total</span>
                    <span>Time</span>
                    <span>Status</span>
                </div>

                <!-- TEMPORARY sample row. -->
                <div id="orders-list">
                    <!-- <button type="button" class="order-row">
                        <span class="row-id">#1008</span>
                        <span>Jane Doe</span>
                        <span>2 items</span>
                        <span>₱265</span>
                        <span>12:30 PM</span>
                        <span class="row-end">
                            <span class="status-badge status-pending">Pending</span>
                            <span class="row-chevron">›</span>
                        </span>
                    </button>
                </div> -->

                <p class="no-orders" id="no-orders" hidden>No orders found.</p>
            </div>
        </section>

        <!-- RIGHT: details of the selected order (hidden until an order is clicked) -->
        <aside class="order-detail" id="order-detail" hidden>

            <div class="detail-content" id="detail-content">

    <button type="button" class="detail-close" id="detail-close">← Back to orders</button>

    <div class="detail-top">
                    <h2 id="detail-title">Order #1008</h2>
                    <span class="status-badge status-pending" id="detail-status">Pending</span>
                </div>
                <p class="detail-date" id="detail-date">Oct 9, 2026 · 12:30 PM</p>

                <div class="detail-customer">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="10" r="3"></circle>
                        <path d="M6.2 18.5a7 7 0 0 1 11.6 0"></path>
                    </svg>
                    <div>
                        <p class="customer-name" id="detail-customer-name">Jane Doe</p>
                        <p class="customer-email" id="detail-customer-email">JaneDoe@gmail.com</p>
                    </div>
                </div>

                <h3 class="detail-heading">Items</h3>
                <div class="detail-items" id="detail-items">
                    <!-- SAMPLE ITEMS (JavaScript replaces these in Step 6) -->
                    <div class="detail-item">
                        <img src="assets/CaramelMacchiato.png" alt="">
                        <div>
                            <p class="item-title">Caramel Macchiato</p>
                            <p class="item-meta">Medium 16oz · 50% sugar</p>
                        </div>
                        <div class="item-end">
                            ₱145
                            <span class="item-qty">x1</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <img src="assets/Classic.png" alt="">
                        <div>
                            <p class="item-title">Classic Iced Coffee</p>
                            <p class="item-meta">Medium 16oz · 50% sugar</p>
                        </div>
                        <div class="item-end">
                            ₱120
                            <span class="item-qty">x1</span>
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <span id="detail-subtotal-label">Subtotal (2 items)</span>
                    <span id="detail-subtotal">₱265</span>
                </div>
                <div class="detail-row" id="detail-discount-row" hidden>
                    <span>Discount</span>
                    <span id="detail-discount">-₱0</span>
                </div>
                <div class="detail-total">
                    <span>Total</span>
                    <span id="detail-total">₱265</span>
                </div>

                <h3 class="detail-heading">Payment Method</h3>
                <div class="detail-box">
                    <p class="box-title" id="detail-payment">Pay upon pickup</p>
                    <p class="box-sub">Customer will pay when they collect the order</p>
                </div>

                <h3 class="detail-heading">Order Notes</h3>
                <div class="detail-box">
                    <p id="detail-notes">No special instructions.</p>
                </div>

                <div class="detail-actions">
                    <button type="button" class="btn-status" id="status-btn">Start Preparing</button>
                    <button type="button" class="btn-cancel" id="cancel-btn">Cancel Order</button>
                </div>

            </div>
        </aside>

    </main>
</div>

<script src="js/orders-data.js"></script>
<script src="js/staff-orders.js"></script>
</body>
</html>